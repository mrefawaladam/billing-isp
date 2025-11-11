<div class="d-flex gap-2 justify-content-center">
    <button type="button" class="btn btn-sm btn-info btn-show-user" data-user-id="{{ $user->id }}" title="View">
        <i class="ti ti-eye"></i>
    </button>
    <button type="button" class="btn btn-sm btn-warning btn-edit-user" data-user-id="{{ $user->id }}" title="Edit">
        <i class="ti ti-edit"></i>
    </button>
    <button type="button" class="btn btn-sm btn-danger btn-delete-user" data-user-id="{{ $user->id }}" data-user-name="{{ $user->name }}" title="Delete">
        <i class="ti ti-trash"></i>
    </button>
</div>
