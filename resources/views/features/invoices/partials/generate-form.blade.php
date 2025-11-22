<form id="generate-invoice-form" action="{{ route('invoices.generate') }}" method="POST">
    @csrf
    <div class="mb-3">
        <label for="year" class="form-label">Tahun <span class="text-danger">*</span></label>
        <select class="form-select @error('year') is-invalid @enderror" id="year" name="year" required>
            <option value="">Pilih Tahun</option>
            @for($y = date('Y'); $y >= 2020; $y--)
                <option value="{{ $y }}" {{ old('year', date('Y')) == $y ? 'selected' : '' }}>{{ $y }}</option>
            @endfor
        </select>
        <div class="invalid-feedback d-none" id="year-error"></div>
        @error('year')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label for="month" class="form-label">Bulan <span class="text-danger">*</span></label>
        <select class="form-select @error('month') is-invalid @enderror" id="month" name="month" required>
            <option value="">Pilih Bulan</option>
            <option value="1" {{ old('month', date('n')) == 1 ? 'selected' : '' }}>Januari</option>
            <option value="2" {{ old('month', date('n')) == 2 ? 'selected' : '' }}>Februari</option>
            <option value="3" {{ old('month', date('n')) == 3 ? 'selected' : '' }}>Maret</option>
            <option value="4" {{ old('month', date('n')) == 4 ? 'selected' : '' }}>April</option>
            <option value="5" {{ old('month', date('n')) == 5 ? 'selected' : '' }}>Mei</option>
            <option value="6" {{ old('month', date('n')) == 6 ? 'selected' : '' }}>Juni</option>
            <option value="7" {{ old('month', date('n')) == 7 ? 'selected' : '' }}>Juli</option>
            <option value="8" {{ old('month', date('n')) == 8 ? 'selected' : '' }}>Agustus</option>
            <option value="9" {{ old('month', date('n')) == 9 ? 'selected' : '' }}>September</option>
            <option value="10" {{ old('month', date('n')) == 10 ? 'selected' : '' }}>Oktober</option>
            <option value="11" {{ old('month', date('n')) == 11 ? 'selected' : '' }}>November</option>
            <option value="12" {{ old('month', date('n')) == 12 ? 'selected' : '' }}>Desember</option>
        </select>
        <div class="invalid-feedback d-none" id="month-error"></div>
        @error('month')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>

    <div class="alert alert-info">
        <i class="ti ti-info-circle me-2"></i>
        Sistem akan otomatis membuat tagihan untuk semua pelanggan aktif pada periode yang dipilih.
        Tagihan yang sudah ada akan dilewati.
    </div>
</form>

