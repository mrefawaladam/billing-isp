# Rencana Integrasi Fitur Paket Internet (Subscription Packages)

## 📋 Overview

Fitur ini akan mengubah sistem input manual "Biaya Bulanan" menjadi sistem pemilihan paket internet yang terstandarisasi. User dapat memilih paket dari tabel yang sudah tersedia, sehingga lebih efisien dan konsisten.

---

## 🎯 Tujuan

1. **Standarisasi Paket**: Mengelola paket internet secara terpusat dengan informasi lengkap (nama, kode, kecepatan, type layanan, harga)
2. **Memudahkan Input**: Mengganti input manual biaya bulanan dengan dropdown/select package
3. **Konsistensi Data**: Memastikan semua customer menggunakan paket yang sudah terdaftar
4. **Fleksibilitas**: Tetap memungkinkan override manual jika diperlukan (untuk custom package)

---

## 📊 Struktur Database

### 1. Migration: `create_packages_table`

```php
Schema::create('packages', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->string('name'); // Nama Paket (contoh: "Bisnis 10", "Bisma MAN 2 Ponorogo")
    $table->string('package_code', 50)->unique(); // Kode Paket (contoh: "2403145894")
    $table->string('speed', 50)->nullable(); // Kecepatan (contoh: "100Mbps", "50Mbps")
    $table->enum('service_type', ['Dedicated Internet', 'Internet Broadband', 'Wireless', 'Fiber'])->default('Internet Broadband');
    $table->decimal('price', 12, 2); // Harga Paket
    $table->text('description')->nullable(); // Deskripsi paket
    $table->boolean('active')->default(true); // Status aktif/nonaktif
    $table->integer('sort_order')->default(0); // Urutan tampil
    $table->timestamps();
    $table->softDeletes(); // Soft delete untuk history
});
```

### 2. Migration: `add_package_id_to_customers_table`

```php
Schema::table('customers', function (Blueprint $table) {
    $table->uuid('package_id')->nullable()->after('monthly_fee');
    $table->foreign('package_id')->references('id')->on('packages')->onDelete('set null');
    $table->index('package_id');
    
    // Optional: Tambah kolom untuk tracking apakah menggunakan custom price
    $table->boolean('use_custom_price')->default(false)->after('package_id');
});
```

---

## 🏗️ Komponen yang Perlu Dibuat

### 1. Model: `Package.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Package extends Model
{
    use SoftDeletes;

    protected $keyType = 'string';
    public $incrementing = false;

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    protected $fillable = [
        'id',
        'name',
        'package_code',
        'speed',
        'service_type',
        'price',
        'description',
        'active',
        'sort_order',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'active' => 'boolean',
        'sort_order' => 'integer',
    ];

    // Relationship
    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }
}
```

### 2. Update Model: `Customer.php`

```php
// Tambahkan ke $fillable
'package_id',
'use_custom_price',

// Tambahkan relationship
public function package(): BelongsTo
{
    return $this->belongsTo(Package::class);
}

// Tambahkan accessor untuk mendapatkan harga
public function getEffectiveMonthlyFeeAttribute(): float
{
    if ($this->use_custom_price) {
        return $this->monthly_fee;
    }
    return $this->package ? $this->package->price : $this->monthly_fee;
}
```

### 3. Service: `PackageService.php`

Fungsi:
- CRUD operations untuk packages
- Validation rules
- Active packages query

### 4. Controller: `PackageController.php`

Actions:
- `index()` - List packages dengan DataTables
- `create()` - Form create package
- `store()` - Simpan package baru
- `show()` - Detail package
- `edit()` - Form edit package
- `update()` - Update package
- `destroy()` - Delete package (soft delete)
- `getActivePackages()` - API endpoint untuk dropdown (JSON)

### 5. Views

#### a. `resources/views/features/packages/index.blade.php`
- DataTable dengan kolom: No, Nama Paket, Kode Paket, Kecepatan, Type Layanan, Harga, Aksi
- Filter by service_type dan active status
- Search functionality
- Action buttons: Edit, Delete

#### b. `resources/views/features/packages/partials/form.blade.php`
- Form fields: name, package_code, speed, service_type, price, description, active, sort_order
- Validation feedback
- Modal-based form

#### c. Update `resources/views/features/customers/partials/form.blade.php`
- Ganti input "Biaya Bulanan" dengan:
  - Select dropdown untuk Package (dengan search/autocomplete)
  - Toggle untuk "Custom Price" (jika perlu override)
  - Input manual "Biaya Bulanan" yang muncul jika custom price diaktifkan
- JavaScript untuk auto-fill monthly_fee saat package dipilih
- JavaScript untuk toggle custom price input

---

## 🔄 Alur Kerja

### Saat Membuat/Edit Customer:

1. **Pilih Paket** (Default):
   - User memilih package dari dropdown
   - Sistem auto-fill `monthly_fee` dengan `package.price`
   - `package_id` disimpan
   - `use_custom_price = false`

2. **Custom Price** (Optional):
   - User toggle "Use Custom Price"
   - Input manual "Biaya Bulanan" muncul
   - User bisa input harga custom
   - `use_custom_price = true`
   - `package_id` tetap tersimpan (untuk tracking)

3. **Perhitungan Total**:
   - Jika paket dipilih: `monthly_fee = package.price`
   - Jika custom: `monthly_fee = input manual`
   - Total fee tetap dihitung dengan formula existing (PPN, discount)

---

## 📝 Routes

```php
// Package Management Routes
Route::resource('packages', \App\Http\Controllers\PackageController::class);
Route::get('packages/api/active', [\App\Http\Controllers\PackageController::class, 'getActivePackages'])->name('packages.api.active');
```

---

## 🎨 UI/UX Improvements

### 1. Package Selector di Form Customer

**Option A: Select Dropdown dengan Search**
```html
<select class="form-select" id="package_id" name="package_id">
    <option value="">-- Pilih Paket --</option>
    @foreach($packages as $package)
        <option value="{{ $package->id }}" data-price="{{ $package->price }}">
            {{ $package->name }} - {{ $package->speed }} - Rp {{ number_format($package->price, 0, ',', '.') }}
        </option>
    @endforeach
</select>
```

**Option B: Modal dengan Table (Recommended - seperti screenshot)**
- Button "Pilih Paket" membuka modal
- Tabel packages dengan kolom: Nama Paket, Kode, Kecepatan, Type, Harga
- User klik row untuk select
- Selected package ditampilkan di form
- Auto-fill monthly_fee

### 2. Package Display di Customer Detail

Tampilkan informasi paket yang dipilih customer:
- Nama Paket: [package name]
- Kecepatan: [speed]
- Type: [service_type]
- Harga Standar: Rp [package price]
- Harga Custom: Rp [monthly_fee] (jika use_custom_price)

---

## 📦 Seeder: `PackageSeeder.php`

Buat seeder untuk data awal berdasarkan screenshot:

```php
$packages = [
    // Dedicated Internet
    ['name' => 'Bisma MAN 2 Ponorogo', 'package_code' => '2504106992', 'speed' => '1000Mbps', 'service_type' => 'Dedicated Internet', 'price' => 21500000],
    ['name' => 'Bisma MAN 2 Ponorogo', 'package_code' => '2504116998', 'speed' => '1000Mbps', 'service_type' => 'Dedicated Internet', 'price' => 8500000],
    ['name' => 'Bisma MTsN 1 Po', 'package_code' => '2502066895', 'speed' => '200Mbps', 'service_type' => 'Dedicated Internet', 'price' => 6000000],
    ['name' => 'Bisma SMKN 1 Slahung', 'package_code' => '2502076898', 'speed' => '200Mbps', 'service_type' => 'Dedicated Internet', 'price' => 5000000],
    
    // Internet Broadband
    ['name' => 'Bisnis 10', 'package_code' => '2403145894', 'speed' => '50Mbps', 'service_type' => 'Internet Broadband', 'price' => 850000],
    ['name' => 'Bisnis 7', 'package_code' => '2212054979', 'speed' => '30Mbps', 'service_type' => 'Internet Broadband', 'price' => 1700000],
    ['name' => 'Bisnis 8', 'package_code' => '2308165323', 'speed' => '30Mbps', 'service_type' => 'Internet Broadband', 'price' => 800000],
    ['name' => 'Bisnis 9', 'package_code' => '2310165500', 'speed' => '100Mbps', 'service_type' => 'Internet Broadband', 'price' => 3885000],
    ['name' => 'Data', 'package_code' => '2512187408', 'speed' => '2Mbps', 'service_type' => 'Internet Broadband', 'price' => 125000],
];
```

---

## 🔐 Role & Permissions

### Permissions:
- `package.view` - View packages list
- `package.create` - Create new package
- `package.edit` - Edit package
- `package.delete` - Delete package

### Role Access:
- **Admin**: Full access
- **Manager**: Full access
- **Moderator**: View only
- **Staff**: View only (untuk memilih saat create customer)
- **User**: No access

---

## 🚀 Implementation Steps

### Phase 1: Database & Models (30 menit)
1. ✅ Create migration `create_packages_table`
2. ✅ Create migration `add_package_id_to_customers_table`
3. ✅ Run migrations
4. ✅ Create `Package` model
5. ✅ Update `Customer` model (add relationship & fillable)

### Phase 2: Service & Controller (45 menit)
6. ✅ Create `PackageService` dengan CRUD logic
7. ✅ Create `PackageController` dengan semua actions
8. ✅ Update `CustomerService` untuk handle package_id

### Phase 3: Views (60 menit)
9. ✅ Create package management views (index, form, show)
10. ✅ Update customer form dengan package selector
11. ✅ Update customer detail view untuk tampilkan package info
12. ✅ Add JavaScript untuk auto-fill & toggle custom price

### Phase 4: Routes & Integration (30 menit)
13. ✅ Add routes untuk package management
14. ✅ Update sidebar menu (tambah "Package Management")
15. ✅ Update role permissions
16. ✅ Test integration

### Phase 5: Seeder & Documentation (30 menit)
17. ✅ Create `PackageSeeder` dengan sample data
18. ✅ Update `DatabaseSeeder`
19. ✅ Run seeder
20. ✅ Update documentation

---

## 📋 Checklist Implementation

### Database
- [ ] Migration `create_packages_table`
- [ ] Migration `add_package_id_to_customers_table`
- [ ] Run migrations

### Models
- [ ] Model `Package` dengan relationships
- [ ] Update Model `Customer` (package_id, use_custom_price, relationship)

### Services
- [ ] `PackageService` dengan CRUD methods
- [ ] Update `CustomerService` untuk handle package

### Controllers
- [ ] `PackageController` dengan semua actions
- [ ] Update `CustomerController` untuk pass packages ke form

### Views - Package Management
- [ ] `features/packages/index.blade.php` (list dengan DataTable)
- [ ] `features/packages/partials/form.blade.php` (create/edit form)
- [ ] `features/packages/partials/show.blade.php` (detail)
- [ ] `features/packages/partials/action-buttons.blade.php`

### Views - Customer Integration
- [ ] Update `features/customers/partials/form.blade.php` (package selector)
- [ ] Update `features/customers/partials/show.blade.php` (tampilkan package info)
- [ ] JavaScript untuk package selection & auto-fill

### Routes & Navigation
- [ ] Routes untuk package resource
- [ ] API route untuk active packages (JSON)
- [ ] Update sidebar menu

### Permissions & Roles
- [ ] Add package permissions di `RolePermissionSeeder`
- [ ] Assign permissions ke roles

### Seeder
- [ ] `PackageSeeder` dengan sample data
- [ ] Update `DatabaseSeeder`

### Testing
- [ ] Test CRUD package
- [ ] Test select package saat create customer
- [ ] Test custom price toggle
- [ ] Test package display di customer detail
- [ ] Test update customer dengan package berbeda

---

## 🔄 Migration Strategy (Existing Data)

Untuk customer yang sudah ada dengan `monthly_fee` manual:

1. **Option 1: Biarkan seperti sekarang**
   - Customer existing tetap `package_id = null`, `use_custom_price = true`
   - Saat edit, user bisa pilih package atau tetap custom

2. **Option 2: Auto-match dengan package (jika mungkin)**
   - Script untuk match `monthly_fee` dengan `package.price`
   - Jika match, set `package_id` dan `use_custom_price = false`
   - Jika tidak match, set `use_custom_price = true`

**Recommendation: Option 1** (lebih aman, tidak risk data loss)

---

## 📝 Notes

1. **Backward Compatibility**: Pastikan customer yang sudah ada tetap bisa di-edit tanpa error
2. **Validation**: Package code harus unique
3. **Soft Delete**: Gunakan soft delete untuk package agar history tetap terjaga
4. **Price History**: Jika package price berubah, customer existing tidak terpengaruh (karena sudah disimpan di monthly_fee)
5. **Reporting**: Bisa ditambahkan report "Top Packages" berdasarkan jumlah customer per package

---

## 🎯 Future Enhancements

1. **Package Versioning**: Track perubahan harga package over time
2. **Package Promo**: Support untuk paket dengan harga promo/periode tertentu
3. **Package Bundling**: Paket dengan multiple services (Internet + TV + Phone)
4. **Package Templates**: Template untuk generate package baru dengan cepat
5. **Package Analytics**: Dashboard untuk melihat popularitas package

---

**Created**: 2024  
**Last Updated**: 2024  
**Status**: Ready for Implementation

