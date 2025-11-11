@extends('layouts.app')

@section('title', 'User Management')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/libs/datatables.net-bs5/css/dataTables.bootstrap5.min.css') }}" />
@endpush

@section('content')
<x-layout.page-header
    title="User Management"
    :breadcrumb-title="'User Management'"
/>

<!-- Toast Notification -->
<x-ui.toast-notification />

<!-- DataTable -->
<div class="datatables">
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <h4 class="card-title">Users</h4>
                    <p class="card-subtitle mb-3">
                        Manage your users and their roles. You can create, edit, and delete users from this page.
                    </p>
                </div>
                <button type="button" class="btn btn-primary" id="btn-create-user">
                    <i class="ti ti-plus me-1"></i> Add New User
                </button>
            </div>
            <div class="table-responsive">
                <table id="users-table" class="table table-striped table-bordered text-nowrap align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Roles</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- User Modal -->
<x-ui.modal
    id="userModal"
    title="Create New User"
    size="lg"
    content-id="userModalBody"
>
    <x-slot name="footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="btn-submit-form">Save</button>
    </x-slot>
</x-ui.modal>

@push('scripts')
@if(!isset($jqueryLoaded))
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    @php $jqueryLoaded = true; @endphp
@endif
<script src="{{ asset('assets/libs/datatables.net/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('assets/libs/datatables.net-bs5/js/dataTables.bootstrap5.min.js') }}"></script>
<script>
$(document).ready(function() {
    // Check if DataTable is already initialized
    if ($.fn.DataTable.isDataTable('#users-table')) {
        $('#users-table').DataTable().destroy();
    }

    // Initialize DataTable
    const usersTable = $('#users-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('users.index') }}",
        columns: [
            { data: 'id', name: 'id' },
            { data: 'name', name: 'name' },
            { data: 'email', name: 'email' },
            { data: 'roles', name: 'roles', orderable: false, searchable: false },
            { data: 'created_at', name: 'created_at' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        pageLength: 10,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        responsive: true,
        autoWidth: false,
        order: [[0, 'desc']],
        language: {
            processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>',
            lengthMenu: "Show _MENU_ entries",
            zeroRecords: "No matching records found",
            info: "Showing _START_ to _END_ of _TOTAL_ entries",
            infoEmpty: "Showing 0 to 0 of 0 entries",
            infoFiltered: "(filtered from _MAX_ total entries)",
            search: "Search:",
            paginate: {
                first: "First",
                last: "Last",
                next: "Next",
                previous: "Previous"
            }
        }
    });

    // Load create form
    $('#btn-create-user').on('click', function() {
        Modal.load('userModal', "{{ route('users.create') }}", 'Create New User');
        // Show submit button for create modal
        $('#btn-submit-form').show();
    });

    // Handle show button click (delegated event)
    $(document).on('click', '.btn-show-user', function(e) {
        e.preventDefault();
        const userId = $(this).data('user-id');
        Modal.load('userModal', `/users/${userId}`, 'User Details');
        // Hide submit button for show modal
        $('#btn-submit-form').hide();
    });

    // Handle edit button click (delegated event)
    $(document).on('click', '.btn-edit-user', function(e) {
        e.preventDefault();
        const userId = $(this).data('user-id');
        Modal.load('userModal', `/users/${userId}/edit`, 'Edit User');
        // Show submit button for edit modal
        $('#btn-submit-form').show();
    });

    // Handle form submission
    $('#btn-submit-form').on('click', function() {
        Form.submit('#user-form', {
            success: function(response) {
                if (response.success) {
                    Modal.hide('userModal');
                    Toast.success(response.message);
                    usersTable.ajax.reload(null, false); // Reload table without resetting pagination
                }
            }
        });
    });

    // Handle delete button click (delegated event)
    $(document).on('click', '.btn-delete-user', function(e) {
        e.preventDefault();
        const userId = $(this).data('user-id');
        const userName = $(this).data('user-name');

        if (!confirm(`Are you sure you want to delete user "${userName}"?`)) {
            return;
        }

        $.ajax({
            url: `/users/${userId}`,
            method: 'POST',
            data: {
                _method: 'DELETE',
                _token: $('meta[name="csrf-token"]').attr('content') || $('input[name="_token"]').val()
            },
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(response) {
                if (response.success) {
                    Toast.success(response.message);
                    usersTable.ajax.reload(null, false);
                }
            },
            error: function() {
                Toast.error('Failed to delete user.');
            }
        });
    });

    // Reset form when modal is hidden
    $('#userModal').on('hidden.bs.modal', function() {
        Modal.clear('userModal');
        // Show submit button by default
        $('#btn-submit-form').show();
    });
});
</script>
@endpush
@endsection
