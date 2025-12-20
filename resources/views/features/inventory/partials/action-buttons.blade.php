<div class="d-flex gap-2 justify-content-center">
    <button type="button" class="btn btn-sm btn-info btn-show-inventory" data-item-id="{{ $item->id }}" title="Lihat">
        <i class="ti ti-eye"></i>
    </button>
    <button type="button" class="btn btn-sm btn-warning btn-edit-inventory" data-item-id="{{ $item->id }}" title="Edit">
        <i class="ti ti-edit"></i>
    </button>
    <button type="button" class="btn btn-sm btn-success btn-use-inventory" data-item-id="{{ $item->id }}" title="Gunakan" {{ $item->stock_quantity <= 0 ? 'disabled' : '' }}>
        <i class="ti ti-package"></i>
    </button>
    <button type="button" class="btn btn-sm btn-primary btn-restock-inventory" data-item-id="{{ $item->id }}" data-item-name="{{ $item->name }}" title="Tambah Stok">
        <i class="ti ti-plus"></i>
    </button>
    <button type="button" class="btn btn-sm btn-danger btn-delete-inventory" data-item-id="{{ $item->id }}" data-item-name="{{ $item->name }}" title="Hapus">
        <i class="ti ti-trash"></i>
    </button>
</div>

