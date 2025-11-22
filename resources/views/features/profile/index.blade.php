@extends('layouts.app')

@section('title', 'Profil Saya')

@push('styles')
<style>
    .profile-avatar {
        width: 120px;
        height: 120px;
        font-size: 48px;
        font-weight: bold;
    }
</style>
@endpush

@section('content')
<x-layout.page-header
    title="Profil Saya"
    :breadcrumb-title="'Profil Saya'"
/>

<!-- Toast Notification -->
<x-ui.toast-notification />

<div class="container-fluid">
    <div class="row">
        <!-- Profile Info Card -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center text-white mx-auto mb-3 profile-avatar">
                        {{ strtoupper(substr($user->name, 0, 2)) }}
                    </div>
                    <h4 class="mb-1">{{ $user->name }}</h4>
                    <p class="text-muted mb-2">{{ $user->email }}</p>
                    @if($user->roles->count() > 0)
                        <div class="mb-3">
                            @foreach($user->roles as $role)
                                <span class="badge bg-success">{{ ucfirst($role->name) }}</span>
                            @endforeach
                        </div>
                    @endif
                    <p class="text-muted small mb-0">
                        <i class="ti ti-calendar me-1"></i>
                        Bergabung: {{ $user->created_at->format('d F Y') }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Profile Edit Form -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="ti ti-edit me-2"></i>Edit Profil</h5>
                </div>
                <div class="card-body">
                    <form id="profile-form" action="{{ route('profile.update') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="name" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                <input
                                    type="text"
                                    class="form-control @error('name') is-invalid @enderror"
                                    id="name"
                                    name="name"
                                    value="{{ old('name', $user->name) }}"
                                    required
                                >
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-12 mb-3">
                                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                <input
                                    type="email"
                                    class="form-control @error('email') is-invalid @enderror"
                                    id="email"
                                    name="email"
                                    value="{{ old('email', $user->email) }}"
                                    required
                                >
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <hr class="my-4">
                                <h6 class="mb-3">Ubah Password</h6>
                                <p class="text-muted small">Kosongkan jika tidak ingin mengubah password</p>
                            </div>

                            <div class="col-md-12 mb-3">
                                <label for="current_password" class="form-label">Password Saat Ini</label>
                                <input
                                    type="password"
                                    class="form-control @error('current_password') is-invalid @enderror"
                                    id="current_password"
                                    name="current_password"
                                >
                                @error('current_password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">Password Baru</label>
                                <input
                                    type="password"
                                    class="form-control @error('password') is-invalid @enderror"
                                    id="password"
                                    name="password"
                                >
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="password_confirmation" class="form-label">Konfirmasi Password Baru</label>
                                <input
                                    type="password"
                                    class="form-control"
                                    id="password_confirmation"
                                    name="password_confirmation"
                                >
                            </div>

                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="ti ti-check me-1"></i> Simpan Perubahan
                                </button>
                                <a href="{{ route('dashboard') }}" class="btn btn-secondary">
                                    <i class="ti ti-x me-1"></i> Batal
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
@if(!isset($jqueryLoaded))
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    @php $jqueryLoaded = true; @endphp
@endif
<script>
$(document).ready(function() {
    // Handle form submission
    $('#profile-form').on('submit', function(e) {
        e.preventDefault();
        
        const formData = $(this).serialize();
        const form = $(this);
        
        $.ajax({
            url: form.attr('action'),
            method: 'PUT',
            data: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(response) {
                if (response.success) {
                    Toast.success(response.message);
                    // Reload page after 1 second to show updated data
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    // Show validation errors
                    Object.keys(errors).forEach(function(key) {
                        const input = form.find('[name="' + key + '"]');
                        input.addClass('is-invalid');
                        const errorDiv = input.siblings('.invalid-feedback');
                        if (errorDiv.length) {
                            errorDiv.text(errors[key][0]);
                        } else {
                            input.after('<div class="invalid-feedback">' + errors[key][0] + '</div>');
                        }
                    });
                } else {
                    Toast.error('Gagal memperbarui profil.');
                }
            }
        });
    });

    // Remove validation errors on input
    $('#profile-form input').on('input', function() {
        $(this).removeClass('is-invalid');
        $(this).siblings('.invalid-feedback').remove();
    });
});
</script>
@endpush
@endsection

