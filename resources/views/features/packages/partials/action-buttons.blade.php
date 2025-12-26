<div class="d-flex gap-2 justify-content-center">
    <button type="button" class="btn btn-sm btn-info btn-show-package" data-package-id="{{ $package->id }}" title="Lihat">
        <i class="ti ti-eye"></i>
    </button>
    <button type="button" class="btn btn-sm btn-warning btn-edit-package" data-package-id="{{ $package->id }}" title="Edit">
        <i class="ti ti-edit"></i>
    </button>
    <button type="button" class="btn btn-sm btn-danger btn-delete-package" data-package-id="{{ $package->id }}" data-package-name="{{ $package->name }}" title="Hapus">
        <i class="ti ti-trash"></i>
    </button>
</div>

