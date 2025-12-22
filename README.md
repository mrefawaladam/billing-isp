# ISP Billing Management System

Sistem manajemen billing untuk Internet Service Provider (ISP) yang dilengkapi dengan berbagai fitur lengkap untuk mengelola pelanggan, tagihan, pembayaran, inventori, dan notifikasi WhatsApp.

**Base URL:** https://biling.duckdns.org/

## 📋 Daftar Isi

- [Fitur Utama](#fitur-utama)
- [Instalasi](#instalasi)
- [Konfigurasi](#konfigurasi)
- [Dokumentasi Fitur](#dokumentasi-fitur)
  - [1. Dashboard](#1-dashboard)
  - [2. Manajemen Pengguna](#2-manajemen-pengguna)
  - [3. Manajemen Pelanggan](#3-manajemen-pelanggan)
  - [4. Manajemen Perangkat](#4-manajemen-perangkat)
  - [5. Manajemen Tagihan](#5-manajemen-tagihan)
  - [6. Manajemen Inventori](#6-manajemen-inventori)
  - [7. Notifikasi WhatsApp](#7-notifikasi-whatsapp)
  - [8. Laporan Pembayaran](#8-laporan-pembayaran)
  - [9. Peta Pelanggan](#9-peta-pelanggan)
  - [10. Dashboard Field Officer](#10-dashboard-field-officer)
  - [11. Profil](#11-profil)
- [API Documentation](#api-documentation)
- [Struktur Folder](#struktur-folder)
- [Dependencies](#dependencies)

## ✨ Fitur Utama

- ✅ **Dashboard** - Overview statistik dan grafik pendapatan
- ✅ **Manajemen Pelanggan** - CRUD pelanggan dengan foto rumah dan lokasi
- ✅ **Manajemen Perangkat** - Tracking perangkat per pelanggan
- ✅ **Manajemen Tagihan** - Generate, print, dan tracking tagihan
- ✅ **Manajemen Inventori** - Stock management perangkat dan alert stok menipis
- ✅ **Notifikasi WhatsApp** - Integrasi dengan Fonnte untuk kirim notifikasi
- ✅ **Laporan Pembayaran** - Export laporan pembayaran ke CSV/Excel
- ✅ **Peta Pelanggan** - Visualisasi lokasi pelanggan di peta
- ✅ **Dashboard Field Officer** - Dashboard khusus untuk field officer
- ✅ **Roles & Permissions** - Sistem role dan permission menggunakan Spatie
- ✅ **Authentication** - Login, register, forgot password dengan throttle protection

## 🚀 Instalasi

### 1. Clone Repository
```bash
git clone <repository-url>
cd billing-isp
```

### 2. Install Dependencies
```bash
composer install
npm install
```

### 3. Environment Setup
```bash
cp .env.example .env
php artisan key:generate
```

### 4. Konfigurasi Database
Edit file `.env` dan sesuaikan konfigurasi database:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=billing_isp
DB_USERNAME=root
DB_PASSWORD=
```

### 5. Database Setup
```bash
php artisan migrate
php artisan db:seed
```

### 6. Konfigurasi WhatsApp (Fonnte)
Edit file `.env` dan tambahkan konfigurasi Fonnte:
```env
FONNTE_API_KEY=your_api_key_here
FONNTE_API_URL=https://api.fonnte.com
FONNTE_RATE_LIMIT_MAX_MESSAGES=100
FONNTE_RATE_LIMIT_PERIOD=60
FONNTE_DELAY_BETWEEN_MESSAGES=2
```

### 7. Storage Link
```bash
php artisan storage:link
```

### 8. Run Development Server
```bash
php artisan serve
npm run dev
```

Akses aplikasi di: `http://localhost:8000`

## ⚙️ Konfigurasi

### Default Users
Setelah menjalankan seeder, default users yang tersedia:
- **Admin**: admin@example.com / password
- **Manager**: manager@example.com / password
- **User**: user@example.com / password

### Roles & Permissions
- **Admin**: Full access ke semua fitur
- **Manager**: Limited access
- **User**: Basic access
- **Field Officer**: Akses khusus untuk field officer dashboard

## 📚 Dokumentasi Fitur

### 1. Dashboard

**URL:** https://biling.duckdns.org/dashboard

**Deskripsi:**
Dashboard utama menampilkan overview statistik bisnis ISP, termasuk:
- Total pelanggan aktif
- Total tagihan bulan ini
- Total tagihan belum dibayar
- Pelanggan terlambat bayar
- Total pendapatan bulan ini
- Grafik pendapatan 12 bulan terakhir
- Distribusi status tagihan
- Riwayat pembayaran terbaru

**Fitur:**
- ✅ Statistik real-time
- ✅ Grafik interaktif (Chart.js)
- ✅ Export laporan ke Excel/CSV
- ✅ Filter berdasarkan periode

**Cara Menggunakan:**
1. Login ke sistem
2. Akses menu Dashboard di sidebar
3. Lihat statistik dan grafik
4. Klik tombol "Export" untuk download laporan

**Export Laporan:**
- **URL Export:** https://biling.duckdns.org/dashboard/export?format=excel
- **Format:** Excel (.xls) atau CSV (.csv)
- **Parameter:** `format` (excel/csv)

---

### 2. Manajemen Pengguna

**URL:** https://biling.duckdns.org/users

**Deskripsi:**
Fitur untuk mengelola pengguna sistem, termasuk admin, manager, dan staff.

**Fitur:**
- ✅ CRUD pengguna lengkap
- ✅ Assign roles dan permissions
- ✅ Filter berdasarkan role
- ✅ DataTables dengan search dan pagination
- ✅ Modal-based operations

**Routes:**
- `GET /users` - List semua pengguna
- `GET /users/create` - Form tambah pengguna
- `POST /users` - Simpan pengguna baru
- `GET /users/{id}` - Detail pengguna
- `GET /users/{id}/edit` - Form edit pengguna
- `PUT /users/{id}` - Update pengguna
- `DELETE /users/{id}` - Hapus pengguna
- `POST /users/{id}/assign-permission` - Assign permission
- `DELETE /users/{id}/permissions/{permission}` - Remove permission

**Cara Menggunakan:**
1. Klik menu "Users" di sidebar
2. Klik tombol "Tambah Pengguna" untuk membuat user baru
3. Isi form: nama, email, password, dan pilih role
4. Klik "Simpan"
5. Untuk edit/hapus, gunakan tombol action di tabel
6. Untuk assign permission, klik tombol "Permissions" di detail user

---

### 3. Manajemen Pelanggan

**URL:** https://biling.duckdns.org/customers

**Deskripsi:**
Fitur untuk mengelola data pelanggan ISP, termasuk informasi kontak, alamat, lokasi GPS, dan foto rumah.

**Fitur:**
- ✅ CRUD pelanggan lengkap
- ✅ Upload foto rumah
- ✅ Input koordinat GPS (latitude/longitude)
- ✅ Assign field officer
- ✅ Filter berdasarkan type (rumahan/kantor), status aktif, dan field officer
- ✅ Lihat daftar perangkat per pelanggan
- ✅ History penggunaan inventori per pelanggan

**Routes:**
- `GET /customers` - List semua pelanggan
- `GET /customers/create` - Form tambah pelanggan
- `POST /customers` - Simpan pelanggan baru
- `GET /customers/{id}` - Detail pelanggan
- `GET /customers/{id}/edit` - Form edit pelanggan
- `PUT /customers/{id}` - Update pelanggan
- `DELETE /customers/{id}` - Hapus pelanggan
- `GET /customers/{id}/devices` - Daftar perangkat pelanggan
- `GET /customers/{id}/inventory-history` - History penggunaan inventori

**Field yang Tersedia:**
- Nama pelanggan
- Kode pelanggan (auto-generate)
- Nomor telepon
- Email
- Alamat lengkap
- Koordinat GPS (latitude/longitude)
- Type (rumahan/kantor)
- Status aktif
- Field officer yang ditugaskan
- Foto rumah

**Cara Menggunakan:**
1. Klik menu "Customers" di sidebar
2. Klik "Tambah Pelanggan" untuk membuat pelanggan baru
3. Isi semua informasi pelanggan
4. Upload foto rumah (opsional)
5. Input koordinat GPS untuk lokasi di peta
6. Pilih field officer yang akan menangani pelanggan
7. Klik "Simpan"
8. Untuk melihat perangkat pelanggan, klik tombol "Devices" di action buttons

---

### 4. Manajemen Perangkat

**URL:** https://biling.duckdns.org/customers/{customer_id}/devices

**Deskripsi:**
Fitur untuk mengelola perangkat yang digunakan oleh pelanggan (router, ONT, dll).

**Fitur:**
- ✅ Tambah perangkat ke pelanggan
- ✅ Edit informasi perangkat
- ✅ Hapus perangkat
- ✅ Tracking MAC address dan serial number
- ✅ Status perangkat (aktif/nonaktif)

**Routes:**
- `GET /customers/{customer}/devices` - List perangkat pelanggan
- `GET /customers/{customer}/devices/{device}` - Detail perangkat
- `POST /customers/{customer}/devices` - Tambah perangkat
- `PUT /customers/{customer}/devices/{device}` - Update perangkat
- `DELETE /customers/{customer}/devices/{device}` - Hapus perangkat

**Field yang Tersedia:**
- Nama perangkat
- Tipe perangkat (router, ONT, switch, dll)
- MAC Address
- Serial Number
- IP Address
- Status (aktif/nonaktif)
- Catatan

**Cara Menggunakan:**
1. Buka detail pelanggan atau klik menu "Devices" di action buttons
2. Klik "Tambah Perangkat" untuk menambahkan perangkat baru
3. Isi informasi perangkat (nama, tipe, MAC address, serial number, IP)
4. Pilih status perangkat
5. Klik "Simpan"
6. Untuk edit/hapus, gunakan tombol action di tabel perangkat

---

### 5. Manajemen Tagihan

**URL:** https://biling.duckdns.org/invoices

**Deskripsi:**
Fitur untuk mengelola tagihan bulanan pelanggan, termasuk generate otomatis, print, dan tracking status pembayaran.

**Fitur:**
- ✅ CRUD tagihan lengkap
- ✅ Generate tagihan otomatis per periode
- ✅ Print tagihan (PDF-ready)
- ✅ Filter berdasarkan status, tahun, bulan, dan pelanggan
- ✅ Status tagihan: PENDING, PAID, OVERDUE
- ✅ Kirim notifikasi WhatsApp langsung dari halaman tagihan
- ✅ Tracking pembayaran

**Routes:**
- `GET /invoices` - List semua tagihan
- `GET /invoices/create` - Form tambah tagihan manual
- `POST /invoices` - Simpan tagihan baru
- `GET /invoices/{id}` - Detail tagihan
- `GET /invoices/{id}/edit` - Form edit tagihan
- `PUT /invoices/{id}` - Update tagihan
- `DELETE /invoices/{id}` - Hapus tagihan
- `POST /invoices/generate` - Generate tagihan otomatis
- `GET /invoices/{id}/print` - Print tagihan

**Field yang Tersedia:**
- Nomor tagihan (auto-generate)
- Pelanggan
- Periode (bulan dan tahun)
- Tanggal jatuh tempo
- Total amount
- Status (PENDING/PAID/OVERDUE)
- Catatan

**Cara Menggunakan:**

**Generate Tagihan Otomatis:**
1. Klik menu "Invoices" di sidebar
2. Klik tombol "Generate Tagihan"
3. Pilih periode (bulan dan tahun)
4. Pilih pelanggan (atau biarkan kosong untuk semua pelanggan aktif)
5. Klik "Generate"
6. Sistem akan membuat tagihan untuk semua pelanggan aktif di periode tersebut

**Tambah Tagihan Manual:**
1. Klik "Tambah Tagihan" di halaman invoices
2. Pilih pelanggan
3. Isi periode, tanggal jatuh tempo, dan total amount
4. Klik "Simpan"

**Print Tagihan:**
1. Buka detail tagihan
2. Klik tombol "Print" di action buttons
3. Tagihan akan terbuka di tab baru untuk di-print

**Kirim Notifikasi WhatsApp:**
1. Di halaman list invoices, klik tombol WhatsApp pada tagihan yang ingin dikirim
2. Konfirmasi pengiriman
3. Sistem akan mengirim notifikasi tagihan ke nomor WhatsApp pelanggan

---

### 6. Manajemen Inventori

**URL:** https://biling.duckdns.org/inventory

**Deskripsi:**
Fitur untuk mengelola stok perangkat dan bahan (router, ONT, kabel, dll), termasuk tracking penggunaan per customer dan alert stok menipis.

**Fitur:**
- ✅ CRUD item inventori
- ✅ Restock (tambah stok)
- ✅ Use item (penggunaan per customer/perangkat)
- ✅ Return item (pengembalian)
- ✅ Alert stok menipis (low stock alert)
- ✅ History pemakaian inventori
- ✅ Filter berdasarkan status dan lokasi
- ✅ Tracking penggunaan per customer

**Routes:**
- `GET /inventory` - List semua item inventori
- `GET /inventory/create` - Form tambah item
- `POST /inventory` - Simpan item baru
- `GET /inventory/{id}` - Detail item dengan history penggunaan
- `GET /inventory/{id}/edit` - Form edit item
- `PUT /inventory/{id}` - Update item
- `DELETE /inventory/{id}` - Hapus item
- `POST /inventory/{id}/restock` - Tambah stok
- `GET /inventory/{id}/use` - Form penggunaan item
- `POST /inventory/{id}/use` - Record penggunaan item
- `GET /customers/{id}/inventory-history` - History penggunaan inventori per customer

**Field yang Tersedia:**
- Nama item
- Deskripsi
- Stok quantity
- Unit (pcs, meter, roll, dll)
- Minimum stock alert (untuk notifikasi stok menipis)
- Lokasi penyimpanan
- Status (aktif/nonaktif)

**Cara Menggunakan:**

**Tambah Item Inventori:**
1. Klik menu "Inventory" di sidebar
2. Klik "Tambah Item"
3. Isi informasi item (nama, deskripsi, stok awal, unit, min stock alert, lokasi)
4. Klik "Simpan"

**Restock (Tambah Stok):**
1. Di halaman list inventory, klik tombol "Restock" pada item yang ingin ditambah stoknya
2. Masukkan jumlah stok yang ditambahkan
3. Klik "Simpan"
4. Stok akan otomatis bertambah

**Use Item (Penggunaan):**
1. Klik tombol "Use" pada item yang ingin digunakan
2. Pilih tipe penggunaan:
   - **Installation**: Untuk instalasi baru
   - **Replacement**: Untuk penggantian perangkat
   - **Maintenance**: Untuk maintenance/perbaikan
   - **Other**: Lainnya
3. Pilih customer dan device (jika terkait)
4. Masukkan jumlah yang digunakan
5. Tambahkan catatan (opsional)
6. Klik "Simpan"
7. Stok akan otomatis berkurang

**Lihat History Penggunaan:**
1. Buka detail item inventori
2. Scroll ke bagian "History Penggunaan"
3. Lihat semua history penggunaan item tersebut

**Alert Stok Menipis:**
- Sistem akan menampilkan badge "Low Stock" pada item yang stoknya di bawah minimum stock alert
- Filter "Low Stock" tersedia di halaman list untuk melihat semua item dengan stok menipis

---

### 7. Notifikasi WhatsApp

**URL:** https://biling.duckdns.org/whatsapp

**Deskripsi:**
Fitur untuk mengirim notifikasi WhatsApp ke pelanggan melalui integrasi dengan Fonnte API. Mendukung pengiriman manual dan otomatis untuk tagihan.

**Fitur:**
- ✅ Kirim pesan manual ke pelanggan
- ✅ Kirim notifikasi tagihan otomatis
- ✅ History semua notifikasi yang dikirim
- ✅ Resend untuk notifikasi yang gagal
- ✅ Status tracking (pending, sent, failed)
- ✅ Rate limiting untuk anti-ban
- ✅ Template message support

**Routes:**
- `GET /whatsapp` - List history notifikasi
- `GET /whatsapp/create` - Form kirim pesan manual
- `POST /whatsapp` - Kirim pesan manual
- `GET /whatsapp/{id}` - Detail notifikasi
- `POST /whatsapp/{id}/resend` - Resend notifikasi yang gagal
- `POST /whatsapp/invoices/{invoice_id}/send` - Kirim notifikasi tagihan

**Cara Menggunakan:**

**Kirim Pesan Manual:**
1. Klik menu "WhatsApp Notifications" di sidebar
2. Klik tombol "Kirim Pesan"
3. Pilih customer atau invoice (untuk auto-fill nomor telepon)
4. Atau masukkan nomor telepon secara manual
5. Tulis pesan yang ingin dikirim
6. Klik "Kirim"
7. Sistem akan mengirim pesan melalui Fonnte API

**Kirim Notifikasi Tagihan:**
1. Di halaman invoices, klik tombol WhatsApp pada tagihan yang ingin dikirim
2. Konfirmasi pengiriman
3. Sistem akan mengirim notifikasi tagihan dengan format template ke pelanggan

**Lihat History:**
1. Buka halaman WhatsApp Notifications
2. Lihat semua history pengiriman dengan status (pending, sent, failed)
3. Klik "Detail" untuk melihat informasi lengkap notifikasi

**Resend Notifikasi Gagal:**
1. Buka detail notifikasi yang statusnya "failed"
2. Klik tombol "Kirim Ulang"
3. Sistem akan mencoba mengirim ulang notifikasi

**Konfigurasi Rate Limiting:**
Edit file `.env` untuk mengatur rate limiting:
```env
FONNTE_RATE_LIMIT_MAX_MESSAGES=100  # Max pesan per periode
FONNTE_RATE_LIMIT_PERIOD=60         # Periode dalam menit
FONNTE_DELAY_BETWEEN_MESSAGES=2     # Delay antar pesan (detik)
```

---

### 8. Laporan Pembayaran

**URL:** https://biling.duckdns.org/payments/report

**Deskripsi:**
Fitur untuk melihat dan export laporan pembayaran pelanggan dalam format CSV atau Excel.

**Fitur:**
- ✅ Filter berdasarkan periode (tanggal mulai - tanggal akhir)
- ✅ Filter berdasarkan customer
- ✅ Filter berdasarkan status pembayaran
- ✅ Export ke CSV
- ✅ Export ke Excel
- ✅ Summary statistik pembayaran

**Routes:**
- `GET /payments/report` - Halaman laporan pembayaran
- `GET /payments/report/export` - Export laporan (CSV/Excel)

**Cara Menggunakan:**
1. Klik menu "Payment Report" di sidebar
2. Pilih periode laporan (tanggal mulai dan tanggal akhir)
3. Pilih customer (opsional, biarkan kosong untuk semua)
4. Pilih status pembayaran (opsional)
5. Klik "Filter" untuk melihat laporan
6. Klik "Export CSV" atau "Export Excel" untuk download laporan

**Parameter Export:**
- `start_date`: Tanggal mulai (format: Y-m-d)
- `end_date`: Tanggal akhir (format: Y-m-d)
- `customer_id`: ID customer (opsional)
- `status`: Status pembayaran (opsional)
- `format`: Format export (csv/excel)

**Contoh URL Export:**
```
https://biling.duckdns.org/payments/report/export?start_date=2024-01-01&end_date=2024-12-31&format=csv
```

---

### 9. Peta Pelanggan

**URL:** https://biling.duckdns.org/map

**Deskripsi:**
Fitur untuk melihat visualisasi lokasi pelanggan di peta interaktif menggunakan Google Maps atau Leaflet.

**Fitur:**
- ✅ Peta interaktif dengan marker pelanggan
- ✅ Info popup per pelanggan
- ✅ Filter berdasarkan type pelanggan
- ✅ Filter berdasarkan status aktif
- ✅ Klik marker untuk detail pelanggan

**Routes:**
- `GET /map` - Halaman peta pelanggan
- `GET /map/customers` - API untuk mendapatkan data pelanggan (JSON)

**Cara Menggunakan:**
1. Klik menu "Map" di sidebar
2. Peta akan menampilkan semua pelanggan yang memiliki koordinat GPS
3. Klik marker untuk melihat informasi pelanggan
4. Gunakan filter untuk menyaring pelanggan berdasarkan type atau status
5. Klik nama pelanggan di popup untuk membuka detail pelanggan

**Persyaratan:**
- Pelanggan harus memiliki koordinat GPS (latitude/longitude) yang sudah diinput
- Koordinat GPS dapat diinput saat membuat atau mengedit pelanggan

---

### 10. Dashboard Field Officer

**URL:** https://biling.duckdns.org/field-officer/dashboard

**Deskripsi:**
Dashboard khusus untuk field officer dengan akses terbatas untuk melihat pelanggan yang ditugaskan dan melakukan input pembayaran.

**Fitur:**
- ✅ Dashboard dengan statistik pelanggan yang ditugaskan
- ✅ List pelanggan yang ditugaskan
- ✅ Detail pelanggan dan perangkatnya
- ✅ Peta pelanggan yang ditugaskan
- ✅ Input pembayaran untuk tagihan pelanggan

**Routes:**
- `GET /field-officer/dashboard` - Dashboard field officer
- `GET /field-officer/customers` - List pelanggan yang ditugaskan
- `GET /field-officer/customers/{id}` - Detail pelanggan
- `GET /field-officer/map` - Peta pelanggan yang ditugaskan
- `GET /field-officer/invoices/{id}/payment` - Form input pembayaran
- `POST /field-officer/invoices/{id}/payment` - Proses pembayaran

**Cara Menggunakan:**

**Akses Dashboard:**
1. Login sebagai field officer (user dengan role "Field Officer")
2. Dashboard akan menampilkan statistik pelanggan yang ditugaskan
3. Lihat list pelanggan di menu "My Customers"
4. Lihat peta pelanggan di menu "Map"

**Input Pembayaran:**
1. Buka detail pelanggan
2. Lihat daftar tagihan pelanggan
3. Klik tombol "Input Pembayaran" pada tagihan yang belum dibayar
4. Isi form pembayaran (metode pembayaran, jumlah, tanggal, catatan)
5. Upload bukti pembayaran (opsional)
6. Klik "Simpan"
7. Status tagihan akan otomatis berubah menjadi "PAID"

**Persyaratan:**
- User harus memiliki role "Field Officer"
- Field officer hanya bisa melihat pelanggan yang ditugaskan kepadanya
- Field officer hanya bisa input pembayaran untuk tagihan pelanggan yang ditugaskan

---

### 11. Profil

**URL:** https://biling.duckdns.org/profile

**Deskripsi:**
Fitur untuk mengelola profil pengguna yang sedang login, termasuk update informasi dan password.

**Fitur:**
- ✅ Update informasi profil (nama, email)
- ✅ Update password
- ✅ Validasi form
- ✅ Preview foto profil

**Routes:**
- `GET /profile` - Halaman profil
- `PUT /profile` - Update profil

**Cara Menggunakan:**
1. Klik menu "Profile" di header (dropdown user)
2. Update informasi yang ingin diubah
3. Untuk update password, isi field password baru dan konfirmasi password
4. Klik "Simpan"
5. Profil akan terupdate

---

## 🔌 API Documentation

### Base URL
```
https://biling.duckdns.org/api
```

### Authentication Endpoints

#### Login
```http
POST /api/auth/login
Content-Type: application/json

{
    "email": "user@example.com",
    "password": "password"
}
```

**Response:**
```json
{
    "success": true,
    "message": "Login berhasil",
    "data": {
        "user": {
            "id": 1,
            "name": "John Doe",
            "email": "user@example.com"
        },
        "token": "1|xxxxxxxxxxxx"
    }
}
```

#### Get Current User
```http
GET /api/auth/me
Authorization: Bearer {token}
```

#### Logout
```http
POST /api/auth/logout
Authorization: Bearer {token}
```

### Field Officer API

#### Get Customers (Field Officer)
```http
GET /api/field-officer/customers
Authorization: Bearer {token}
```

#### Get Customer Detail
```http
GET /api/field-officer/customers/{id}
Authorization: Bearer {token}
```

#### Process Payment
```http
POST /api/field-officer/invoices/{id}/payment
Authorization: Bearer {token}
Content-Type: application/json

{
    "payment_method": "cash",
    "amount": 150000,
    "payment_date": "2024-01-15",
    "notes": "Pembayaran tunai"
}
```

### Map API

#### Get Customers for Map
```http
GET /api/map/customers
Authorization: Bearer {token}
```

**Query Parameters:**
- `type`: Filter by customer type (rumahan/kantor)
- `active`: Filter by active status (1/0)

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "name": "John Doe",
            "latitude": "-6.2088",
            "longitude": "106.8456",
            "type": "rumahan",
            "active": true
        }
    ]
}
```

---

## 📁 Struktur Folder

```
app/
├── Console/Commands/          # Artisan commands
├── Exceptions/               # Exception handlers
├── Http/
│   ├── Controllers/          # Controllers
│   │   ├── Api/              # API controllers
│   │   └── Auth/              # Auth controllers
│   ├── Middleware/           # Middleware
│   └── Responses/            # Response helpers
├── Models/                   # Eloquent models
├── Services/                 # Business logic services
└── Support/                  # Helper functions

database/
├── migrations/               # Database migrations
└── seeders/                  # Database seeders

resources/
├── css/                      # CSS files
├── js/                       # JavaScript files
└── views/
    ├── auth/                 # Auth views
    ├── components/           # Blade components
    ├── features/             # Feature views
    │   ├── customers/
    │   ├── devices/
    │   ├── invoices/
    │   ├── inventory/
    │   ├── whatsapp/
    │   └── ...
    ├── layouts/              # Layout templates
    └── pages/                # Page views

routes/
├── api.php                   # API routes
└── web.php                   # Web routes

public/
├── assets/                   # Public assets
└── logo.png                  # Logo files
```

---

## 📦 Dependencies

### Backend
- **Laravel Framework**: ^12.0
- **Laravel Sanctum**: ^4.2 (API Authentication)
- **Spatie Laravel Permission**: ^6.23 (Roles & Permissions)
- **Yajra DataTables**: ^12.6 (DataTables integration)
- **Guzzle HTTP**: ^7.0 (HTTP client untuk Fonnte API)

### Frontend
- **Bootstrap 5**: UI framework
- **Chart.js**: Grafik dan chart
- **DataTables**: Tabel interaktif
- **SweetAlert2**: Alert dan konfirmasi
- **Leaflet/Google Maps**: Peta interaktif

---

## 🔐 Security

- ✅ Password hashing menggunakan bcrypt
- ✅ CSRF protection untuk semua form
- ✅ Rate limiting untuk login (max 5 attempts per minute)
- ✅ Session timeout middleware
- ✅ API authentication menggunakan Laravel Sanctum
- ✅ Role-based access control (RBAC)
- ✅ Input validation dan sanitization

---

## 🛠️ Maintenance

### Clear Cache
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Optimize
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Queue Workers
Jika menggunakan queue untuk WhatsApp notifications:
```bash
php artisan queue:work
```

### Scheduled Tasks
Pastikan cron job sudah di-setup untuk scheduled tasks:
```bash
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

---

## 📝 Changelog

### Version 1.0.0
- ✅ Initial release
- ✅ Dashboard dengan statistik dan grafik
- ✅ Manajemen pelanggan dengan GPS tracking
- ✅ Manajemen perangkat
- ✅ Manajemen tagihan dengan print
- ✅ Manajemen inventori dengan low stock alert
- ✅ Integrasi WhatsApp via Fonnte
- ✅ Laporan pembayaran dengan export
- ✅ Peta pelanggan interaktif
- ✅ Dashboard field officer
- ✅ Roles & permissions

---

## 🤝 Support

Untuk pertanyaan atau bantuan, silakan hubungi tim development atau buka issue di repository.

---

## 📄 License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

---

## 🙏 Credits

- [Laravel](https://laravel.com)
- [MatDash Bootstrap Admin](https://themewagon.com)
- [Spatie Laravel Permission](https://github.com/spatie/laravel-permission)
- [Yajra DataTables](https://github.com/yajra/laravel-datatables)
- [Fonnte](https://fonnte.com) - WhatsApp API Provider
