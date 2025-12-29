<form id="bulk-by-region-form" action="{{ route('whatsapp.bulk-by-region.send') }}" method="POST">
    @csrf
    
    <div class="alert alert-info">
        <i class="ti ti-info-circle me-2"></i>
        <strong>Kirim Tagihan Berdasarkan Daerah</strong><br>
        Pilih filter daerah dan bulan tagihan. Sistem akan mengirim notifikasi tagihan ke semua pelanggan di daerah yang dipilih.<br>
        <small class="d-block mt-2">
            <i class="ti ti-shield-lock me-1"></i>
            <strong>Anti-Ban:</strong> Sistem akan otomatis menambahkan delay {{ config('services.fonnte.delay_between_messages', 3) }} detik antara setiap pengiriman untuk menghindari rate limit.
        </small>
    </div>

    <div class="mb-3">
        <label for="filter_type" class="form-label">Filter Berdasarkan <span class="text-danger">*</span></label>
        <select class="form-select" id="filter_type" name="filter_type" required>
            <option value="">Pilih Filter</option>
            <option value="kabupaten">Kabupaten</option>
            <option value="kecamatan">Kecamatan</option>
            <option value="kelurahan">Kelurahan/Desa</option>
        </select>
        <div class="invalid-feedback d-none" id="filter_type-error"></div>
    </div>

    <div class="mb-3" id="kabupaten-wrapper">
        <label for="kabupaten" class="form-label">Kabupaten <span class="text-danger">*</span></label>
        <select class="form-select" id="kabupaten" name="kabupaten">
            <option value="">Pilih Kabupaten</option>
            @foreach($kabupatens as $kab)
                <option value="{{ $kab }}">{{ $kab }}</option>
            @endforeach
        </select>
        <div class="invalid-feedback d-none" id="kabupaten-error"></div>
    </div>

    <div class="mb-3" id="kecamatan-wrapper" style="display: none;">
        <label for="kecamatan" class="form-label">Kecamatan</label>
        <select class="form-select" id="kecamatan" name="kecamatan">
            <option value="">Pilih Kecamatan</option>
            @foreach($kecamatans as $kec)
                <option value="{{ $kec }}">{{ $kec }}</option>
            @endforeach
        </select>
        <div class="invalid-feedback d-none" id="kecamatan-error"></div>
    </div>

    <div class="mb-3" id="kelurahan-wrapper" style="display: none;">
        <label for="kelurahan" class="form-label">Kelurahan/Desa</label>
        <select class="form-select" id="kelurahan" name="kelurahan">
            <option value="">Pilih Kelurahan/Desa</option>
            @foreach($kelurahans as $kel)
                <option value="{{ $kel }}">{{ $kel }}</option>
            @endforeach
        </select>
        <div class="invalid-feedback d-none" id="kelurahan-error"></div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="invoice_year" class="form-label">Tahun Tagihan <span class="text-danger">*</span></label>
            <select class="form-select" id="invoice_year" name="invoice_year" required>
                @for($year = date('Y'); $year >= 2020; $year--)
                    <option value="{{ $year }}" {{ $year == date('Y') ? 'selected' : '' }}>{{ $year }}</option>
                @endfor
            </select>
            <div class="invalid-feedback d-none" id="invoice_year-error"></div>
        </div>
        <div class="col-md-6 mb-3">
            <label for="invoice_month" class="form-label">Bulan Tagihan <span class="text-danger">*</span></label>
            <select class="form-select" id="invoice_month" name="invoice_month" required>
                <option value="">Pilih Bulan</option>
                @php
                    $months = [
                        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                    ];
                @endphp
                @foreach($months as $num => $name)
                    <option value="{{ $num }}" {{ $num == date('n') ? 'selected' : '' }}>{{ $name }}</option>
                @endforeach
            </select>
            <div class="invalid-feedback d-none" id="invoice_month-error"></div>
        </div>
    </div>

    <div class="mb-3">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" id="include_unpaid_previous" name="include_unpaid_previous" value="1">
            <label class="form-check-label" for="include_unpaid_previous">
                <strong>Termasuk tagihan bulan sebelumnya yang belum lunas</strong>
            </label>
        </div>
        <small class="text-muted">
            <i class="ti ti-info-circle me-1"></i>
            Jika dicentang, sistem akan mengirim semua tagihan yang belum dibayar termasuk bulan-bulan sebelumnya
        </small>
    </div>

    <div class="alert alert-warning">
        <i class="ti ti-alert-triangle me-2"></i>
        <strong>Perhatian:</strong> Pastikan semua data sudah benar sebelum mengirim. Proses ini akan mengirim pesan ke semua pelanggan yang sesuai dengan filter.
    </div>

    <div class="alert alert-info" id="progress-alert" style="display: none;">
        <div class="d-flex align-items-center mb-2">
            <div class="spinner-border spinner-border-sm text-primary me-2" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <strong>Sedang mengirim pesan...</strong>
        </div>
        <div class="progress mb-2" style="height: 25px;">
            <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" 
                 id="progress-bar" style="width: 0%">
                <span id="progress-text">0%</span>
            </div>
        </div>
        <small id="progress-detail" class="text-muted">Memproses...</small>
    </div>
</form>

<script>
$(document).ready(function() {
    // Show/hide fields based on filter type
    $('#filter_type').on('change', function() {
        const filterType = $(this).val();
        
        // Reset all selects
        $('#kabupaten, #kecamatan, #kelurahan').val('').prop('required', false);
        $('#kabupaten-wrapper, #kecamatan-wrapper, #kelurahan-wrapper').hide();
        
        if (filterType === 'kabupaten') {
            $('#kabupaten-wrapper').show();
            $('#kabupaten').prop('required', true);
        } else if (filterType === 'kecamatan') {
            $('#kabupaten-wrapper').show();
            $('#kecamatan-wrapper').show();
            $('#kabupaten').prop('required', true);
            $('#kecamatan').prop('required', true);
        } else if (filterType === 'kelurahan') {
            $('#kabupaten-wrapper').show();
            $('#kecamatan-wrapper').show();
            $('#kelurahan-wrapper').show();
            $('#kabupaten').prop('required', true);
            $('#kecamatan').prop('required', true);
            $('#kelurahan').prop('required', true);
        }
    });

    // Filter kecamatan based on kabupaten
    $('#kabupaten').on('change', function() {
        const kabupaten = $(this).val();
        const kecamatanSelect = $('#kecamatan');
        const kelurahanSelect = $('#kelurahan');
        
        if (kabupaten) {
            kecamatanSelect.html('<option value="">Memuat...</option>');
            kecamatanSelect.prop('disabled', true);
            
            $.get("{{ route('whatsapp.bulk-by-region') }}", {
                filter: 'kecamatan',
                kabupaten: kabupaten
            }, function(response) {
                kecamatanSelect.html('<option value="">Pilih Kecamatan</option>');
                if (response.kecamatans && response.kecamatans.length > 0) {
                    response.kecamatans.forEach(function(kec) {
                        kecamatanSelect.append($('<option></option>').val(kec).text(kec));
                    });
                }
                kecamatanSelect.prop('disabled', false);
            });
        } else {
            kecamatanSelect.html('<option value="">Pilih Kecamatan</option>');
        }
        
        // Reset kelurahan
        kelurahanSelect.html('<option value="">Pilih Kelurahan/Desa</option>');
    });
    
    // Filter kelurahan based on kecamatan
    $('#kecamatan').on('change', function() {
        const kabupaten = $('#kabupaten').val();
        const kecamatan = $(this).val();
        const kelurahanSelect = $('#kelurahan');
        
        if (kecamatan && kabupaten) {
            kelurahanSelect.html('<option value="">Memuat...</option>');
            kelurahanSelect.prop('disabled', true);
            
            $.get("{{ route('whatsapp.bulk-by-region') }}", {
                filter: 'kelurahan',
                kabupaten: kabupaten,
                kecamatan: kecamatan
            }, function(response) {
                kelurahanSelect.html('<option value="">Pilih Kelurahan/Desa</option>');
                if (response.kelurahans && response.kelurahans.length > 0) {
                    response.kelurahans.forEach(function(kel) {
                        kelurahanSelect.append($('<option></option>').val(kel).text(kel));
                    });
                }
                kelurahanSelect.prop('disabled', false);
            });
        } else {
            kelurahanSelect.html('<option value="">Pilih Kelurahan/Desa</option>');
        }
    });
});
</script>

