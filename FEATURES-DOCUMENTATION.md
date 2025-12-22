# Dokumentasi Lengkap Fitur ISP Billing Management System

**Versi:** 1.0.0  
**Tanggal:** 2024  
**Base URL:** https://biling.duckdns.org/

---

## 📋 Daftar Isi

1. [Authentication & Login](#1-authentication--login)
2. [Dashboard](#2-dashboard)
3. [User Management](#3-user-management)
4. [Customer Management](#4-customer-management)
5. [Device Management](#5-device-management)
6. [Invoice Management](#6-invoice-management)
7. [Inventory Management](#7-inventory-management)
8. [WhatsApp Notification](#8-whatsapp-notification)
9. [Payment Report](#9-payment-report)
10. [Map & Location](#10-map--location)
11. [Field Officer Dashboard](#11-field-officer-dashboard)
12. [Profile Management](#12-profile-management)
13. [Role & Permission Matrix](#13-role--permission-matrix)

---

## 1. Authentication & Login

### 1.1 Halaman Login

**URL:** `https://biling.duckdns.org/login`

**Screenshot:**
```
[SCREENSHOT: login-page.png]
Gambar 1.1: Halaman Login - Tampilan responsive dengan logo dan form login
```

**Deskripsi:**
Halaman login adalah pintu masuk utama ke sistem. Halaman ini dirancang dengan tampilan modern dan responsive yang mendukung berbagai ukuran layar (desktop, tablet, mobile).

**Fitur:**
- Form login dengan email dan password
- Validasi input real-time
- Throttle protection (max 5 attempts per minute)
- Link "Lupa Password"
- Link "Daftar Akun Baru"
- Responsive design untuk mobile dan desktop
- Logo aplikasi di sidebar (desktop) dan header (mobile)

**Field yang Tersedia:**
- **Email:** Input email user (required)
- **Password:** Input password (required, hidden)
- **Remember Me:** Checkbox untuk menyimpan session

**Role Access:**
- ✅ Semua user (guest) - Bisa login

**Cara Menggunakan:**
1. Buka URL `https://biling.duckdns.org/login`
2. Masukkan email dan password
3. Centang "Remember Me" jika ingin session tetap aktif
4. Klik tombol "Login"
5. Sistem akan redirect ke dashboard setelah login berhasil

**Error Handling:**
- Jika email/password salah: Menampilkan pesan error
- Jika terlalu banyak attempt: Account akan di-lock sementara
- Jika session expired: Redirect ke halaman login

---

### 1.2 Halaman Register

**URL:** `https://biling.duckdns.org/register`

**Screenshot:**
```
[SCREENSHOT: register-page.png]
Gambar 1.2: Halaman Register - Form pendaftaran user baru
```

**Deskripsi:**
Halaman untuk mendaftarkan akun baru ke sistem. User baru akan mendapatkan role default sesuai konfigurasi sistem.

**Fitur:**
- Form registrasi dengan validasi
- Password strength indicator
- Konfirmasi password
- Link kembali ke login

**Field yang Tersedia:**
- **Nama:** Nama lengkap user (required)
- **Email:** Email user (required, unique)
- **Password:** Password (required, min 8 karakter)
- **Konfirmasi Password:** Konfirmasi password (required, harus sama)

**Role Access:**
- ✅ Guest - Bisa register

---

### 1.3 Forgot Password

**URL:** `https://biling.duckdns.org/forgot-password`

**Screenshot:**
```
[SCREENSHOT: forgot-password-page.png]
Gambar 1.3: Halaman Lupa Password - Form request reset password
```

**Deskripsi:**
Halaman untuk meminta reset password jika user lupa password mereka.

**Fitur:**
- Input email untuk reset password
- Link reset password dikirim ke email
- Validasi email

**Cara Menggunakan:**
1. Masukkan email yang terdaftar
2. Klik "Kirim Link Reset Password"
3. Cek email untuk link reset password
4. Klik link di email untuk reset password

---

### 1.4 Reset Password

**URL:** `https://biling.duckdns.org/reset-password/{token}`

**Screenshot:**
```
[SCREENSHOT: reset-password-page.png]
Gambar 1.4: Halaman Reset Password - Form untuk membuat password baru
```

**Deskripsi:**
Halaman untuk membuat password baru setelah user mengklik link reset password dari email.

**Fitur:**
- Input password baru
- Konfirmasi password baru
- Validasi token reset password

---

## 2. Dashboard

### 2.1 Dashboard Utama

**URL:** `https://biling.duckdns.org/dashboard`

**Screenshot:**
```
[SCREENSHOT: dashboard-main.png]
Gambar 2.1: Dashboard Utama - Overview statistik dan grafik
```

**Deskripsi:**
Dashboard utama menampilkan overview lengkap tentang bisnis ISP, termasuk statistik penting, grafik pendapatan, dan riwayat pembayaran terbaru.

**Fitur:**
- ✅ Statistik real-time (Total Pelanggan Aktif, Total Tagihan, dll)
- ✅ Grafik pendapatan 12 bulan terakhir (Chart.js)
- ✅ Distribusi status tagihan (pie chart)
- ✅ Riwayat pembayaran terbaru
- ✅ Export laporan ke Excel/CSV
- ✅ Filter berdasarkan periode

**Statistik yang Ditampilkan:**
1. **Total Pelanggan Aktif:** Jumlah pelanggan dengan status aktif
2. **Total Tagihan Bulan Ini:** Total nilai tagihan yang dibuat bulan ini
3. **Total Tagihan Belum Dibayar:** Total tagihan dengan status UNPAID atau OVERDUE
4. **Pelanggan Terlambat Bayar:** Jumlah pelanggan yang memiliki tagihan OVERDUE
5. **Total Pendapatan Bulan Ini:** Total pembayaran yang diterima bulan ini

**Grafik:**
- **Pendapatan 12 Bulan Terakhir:** Line chart menampilkan trend pendapatan
- **Distribusi Status Tagihan:** Pie chart menampilkan proporsi tagihan per status

**Role Access:**
- ✅ Admin - Full access
- ✅ Manager - Full access
- ✅ Moderator - Full access
- ✅ Staff - Full access (dashboard staff)
- ✅ User - Full access

**Cara Menggunakan:**
1. Login ke sistem
2. Dashboard akan otomatis ditampilkan setelah login
3. Lihat statistik dan grafik
4. Klik tombol "Export" untuk download laporan

---

### 2.2 Export Dashboard Report

**URL:** `https://biling.duckdns.org/dashboard/export?format=excel`

**Screenshot:**
```
[SCREENSHOT: dashboard-export.png]
Gambar 2.2: Export Dashboard - Dialog download laporan
```

**Deskripsi:**
Fitur untuk mengexport laporan dashboard ke format Excel atau CSV.

**Format Export:**
- Excel (.xls)
- CSV (.csv)

**Isi Laporan:**
- Statistik ringkasan
- Pendapatan bulanan (12 bulan terakhir)
- Detail semua tagihan

---

## 3. User Management

### 3.1 List Users

**URL:** `https://biling.duckdns.org/users`

**Screenshot:**
```
[SCREENSHOT: users-list.png]
Gambar 3.1: Daftar Users - Tabel dengan DataTables dan filter
```

**Deskripsi:**
Halaman untuk mengelola semua user di sistem. Admin dan Manager dapat melihat, menambah, mengedit, dan menghapus user.

**Fitur:**
- ✅ DataTables dengan search dan pagination
- ✅ Filter berdasarkan role
- ✅ CRUD user lengkap
- ✅ Assign roles dan permissions
- ✅ Modal-based operations

**Kolom Tabel:**
- Nama
- Email
- Role
- Status
- Tanggal Dibuat
- Aksi (View, Edit, Delete, Permissions)

**Role Access:**
- ✅ Admin - Full access
- ✅ Manager - Limited access (view, list, edit)
- ❌ Moderator - No access
- ❌ Staff - No access
- ❌ User - No access

**Cara Menggunakan:**
1. Klik menu "User Management" di sidebar
2. Lihat daftar semua users
3. Gunakan search box untuk mencari user
4. Gunakan filter untuk menyaring berdasarkan role
5. Klik tombol "Tambah User" untuk membuat user baru
6. Klik tombol action untuk view/edit/delete user

---

### 3.2 Tambah User

**URL:** `https://biling.duckdns.org/users/create`

**Screenshot:**
```
[SCREENSHOT: users-create.png]
Gambar 3.2: Form Tambah User - Modal dengan form input
```

**Deskripsi:**
Form untuk menambahkan user baru ke sistem.

**Field yang Tersedia:**
- **Nama:** Nama lengkap user (required)
- **Email:** Email user (required, unique)
- **Password:** Password (required, min 8 karakter)
- **Konfirmasi Password:** Konfirmasi password (required)
- **Roles:** Pilih role untuk user (multiple select)

**Validasi:**
- Email harus unique
- Password minimal 8 karakter
- Konfirmasi password harus sama dengan password

---

### 3.3 Edit User

**URL:** `https://biling.duckdns.org/users/{id}/edit`

**Screenshot:**
```
[SCREENSHOT: users-edit.png]
Gambar 3.3: Form Edit User - Modal dengan form edit
```

**Deskripsi:**
Form untuk mengedit informasi user yang sudah ada.

**Field yang Tersedia:**
- **Nama:** Nama lengkap user (required)
- **Email:** Email user (required, unique)
- **Password:** Password baru (optional, kosongkan jika tidak ingin mengubah)
- **Konfirmasi Password:** Konfirmasi password baru (required jika password diisi)
- **Roles:** Pilih role untuk user (multiple select)

---

### 3.4 Assign Permissions

**URL:** `https://biling.duckdns.org/users/{id}` (tab Permissions)

**Screenshot:**
```
[SCREENSHOT: users-permissions.png]
Gambar 3.4: Assign Permissions - Daftar permissions user
```

**Deskripsi:**
Halaman untuk mengelola permissions yang dimiliki oleh user tertentu.

**Fitur:**
- ✅ Lihat semua permissions user
- ✅ Assign permission baru
- ✅ Remove permission
- ✅ Filter permissions

**Role Access:**
- ✅ Admin - Full access

---

## 4. Customer Management

### 4.1 List Customers

**URL:** `https://biling.duckdns.org/customers`

**Screenshot:**
```
[SCREENSHOT: customers-list.png]
Gambar 4.1: Daftar Customers - Tabel dengan filter dan bulk assign
```

**Deskripsi:**
Halaman untuk mengelola semua pelanggan ISP. Admin, Manager, dan Moderator dapat melihat dan mengelola data pelanggan.

**Fitur:**
- ✅ DataTables dengan search dan pagination
- ✅ Filter berdasarkan type, status aktif, dan field officer
- ✅ CRUD customer lengkap
- ✅ Bulk assign ke staff
- ✅ Upload foto rumah
- ✅ Input koordinat GPS
- ✅ Modal-based operations

**Kolom Tabel:**
- Checkbox (untuk bulk select)
- Kode Pelanggan
- Nama
- Telepon
- Jenis (Rumahan/Kantor/Sekolah/Free)
- Penanggung Jawab (Field Officer)
- Biaya Bulanan
- Status (Aktif/Tidak Aktif)
- Tanggal Dibuat
- Aksi (View, Edit, Devices, Delete)

**Role Access:**
- ✅ Admin - Full access
- ✅ Manager - Full access
- ✅ Moderator - View access (read only)
- ❌ Staff - No access (menggunakan Field Officer routes)
- ❌ User - No access

**Cara Menggunakan:**
1. Klik menu "Customer Management" di sidebar
2. Lihat daftar semua customers
3. Gunakan filter untuk menyaring berdasarkan type, status, atau field officer
4. Centang checkbox untuk bulk assign ke staff
5. Klik tombol "Tambah Pelanggan" untuk membuat customer baru
6. Klik tombol action untuk view/edit/delete customer

---

### 4.2 Tambah Customer

**URL:** `https://biling.duckdns.org/customers/create`

**Screenshot:**
```
[SCREENSHOT: customers-create.png]
Gambar 4.2: Form Tambah Customer - Modal dengan form lengkap dan peta
```

**Deskripsi:**
Form untuk menambahkan pelanggan baru ke sistem dengan informasi lengkap termasuk lokasi GPS dan foto rumah.

**Field yang Tersedia:**

**Informasi Dasar:**
- **Kode Pelanggan:** Auto-generate jika kosong (optional)
- **Nama Lengkap:** Nama pelanggan (required)
- **Nomor WhatsApp:** Nomor telepon pelanggan (optional)
- **Alamat Lengkap:** Alamat pelanggan (optional)
- **Latitude:** Koordinat GPS latitude (optional)
- **Longitude:** Koordinat GPS longitude (optional)
- **Peta Lokasi:** Peta interaktif untuk memilih lokasi

**Paket & Biaya:**
- **Jenis Pelanggan:** Rumahan/Kantor/Sekolah/Free (required)
- **Penanggung Jawab:** Pilih Field Officer/Staff (optional)
- **Biaya Bulanan:** Biaya bulanan dalam Rupiah (required)
- **Diskon:** Diskon dalam Rupiah (optional)
- **PPN Sudah Termasuk:** Checkbox (optional)
- **Total Biaya Bulanan:** Dihitung otomatis (readonly)
- **Tanggal Jatuh Tempo:** Hari setiap bulan 1-31 (required)
- **Aktif:** Checkbox status aktif (default: checked)

**Foto Rumah:**
- **Upload Foto Rumah:** Upload foto rumah pelanggan (optional, max 5MB)

**Fitur Khusus:**
- Peta interaktif untuk memilih lokasi (Leaflet/Google Maps)
- Search lokasi untuk auto-fill koordinat
- Auto-calculate total biaya bulanan (termasuk PPN)

---

### 4.3 Detail Customer

**URL:** `https://biling.duckdns.org/customers/{id}`

**Screenshot:**
```
[SCREENSHOT: customers-detail.png]
Gambar 4.3: Detail Customer - Informasi lengkap pelanggan
```

**Deskripsi:**
Halaman detail menampilkan informasi lengkap tentang pelanggan termasuk foto rumah, lokasi di peta, daftar perangkat, dan history tagihan.

**Informasi yang Ditampilkan:**
- Informasi dasar pelanggan
- Foto rumah (jika ada)
- Lokasi di peta (jika koordinat tersedia)
- Daftar perangkat yang digunakan
- History tagihan
- History penggunaan inventori

---

### 4.4 Bulk Assign Customers

**URL:** `https://biling.duckdns.org/customers` (bulk assign button)

**Screenshot:**
```
[SCREENSHOT: customers-bulk-assign.png]
Gambar 4.4: Bulk Assign - Modal untuk assign multiple customers ke staff
```

**Deskripsi:**
Fitur untuk menugaskan beberapa pelanggan sekaligus ke field officer/staff tertentu.

**Cara Menggunakan:**
1. Centang checkbox pada pelanggan yang ingin di-assign
2. Klik tombol "Assign ke Staff" yang muncul
3. Pilih staff dari dropdown
4. Klik "Assign"
5. Pelanggan akan ditugaskan ke staff yang dipilih

**Fitur:**
- Select all checkbox untuk memilih semua
- Counter jumlah pelanggan yang dipilih
- Opsi untuk menghapus penugasan (kosongkan dropdown)

---

## 5. Device Management

### 5.1 Daftar Perangkat Customer

**URL:** `https://biling.duckdns.org/customers/{customer}/devices`

**Screenshot:**
```
[SCREENSHOT: devices-list.png]
Gambar 5.1: Daftar Perangkat - Tabel perangkat milik customer
```

**Deskripsi:**
Halaman untuk mengelola perangkat yang digunakan oleh pelanggan tertentu (router, ONT, switch, dll).

**Fitur:**
- ✅ CRUD perangkat lengkap
- ✅ Tracking MAC address dan serial number
- ✅ Status perangkat (aktif/nonaktif)
- ✅ Modal-based operations

**Kolom Tabel:**
- Nama Perangkat
- Tipe Perangkat
- MAC Address
- Serial Number
- IP Address
- Status
- Catatan
- Aksi (View, Edit, Delete)

**Role Access:**
- ✅ Admin - Full access
- ✅ Manager - Full access
- ✅ Moderator - View access (read only)
- ❌ Staff - No access
- ❌ User - No access

---

### 5.2 Tambah Perangkat

**URL:** `https://biling.duckdns.org/customers/{customer}/devices` (create button)

**Screenshot:**
```
[SCREENSHOT: devices-create.png]
Gambar 5.2: Form Tambah Perangkat - Modal dengan form input perangkat
```

**Deskripsi:**
Form untuk menambahkan perangkat baru ke pelanggan.

**Field yang Tersedia:**
- **Nama Perangkat:** Nama perangkat (required)
- **Tipe Perangkat:** Router/ONT/Switch/dll (required)
- **MAC Address:** MAC address perangkat (optional)
- **Serial Number:** Serial number perangkat (optional)
- **IP Address:** IP address perangkat (optional)
- **Status:** Aktif/Nonaktif (required)
- **Catatan:** Catatan tambahan (optional)

---

## 6. Invoice Management

### 6.1 List Invoices

**URL:** `https://biling.duckdns.org/invoices`

**Screenshot:**
```
[SCREENSHOT: invoices-list.png]
Gambar 6.1: Daftar Invoices - Tabel dengan filter dan tombol WhatsApp
```

**Deskripsi:**
Halaman untuk mengelola semua tagihan pelanggan. Admin, Manager, dan Moderator dapat melihat, membuat, dan mengelola tagihan.

**Fitur:**
- ✅ DataTables dengan search dan pagination
- ✅ Filter berdasarkan status, tahun, bulan, dan customer
- ✅ CRUD invoice lengkap
- ✅ Generate tagihan otomatis per periode
- ✅ Print tagihan (PDF-ready)
- ✅ Kirim notifikasi WhatsApp langsung
- ✅ Status tracking (PENDING, PAID, OVERDUE)

**Kolom Tabel:**
- Nomor Tagihan
- Customer
- Periode (Bulan/Tahun)
- Tanggal Jatuh Tempo
- Total Amount
- Status
- Tanggal Dibuat
- Aksi (View, Edit, Print, WhatsApp, Delete)

**Role Access:**
- ✅ Admin - Full access
- ✅ Manager - Full access
- ✅ Moderator - View access (read only)
- ❌ Staff - No access (menggunakan Field Officer routes)
- ❌ User - No access

---

### 6.2 Generate Invoice Otomatis

**URL:** `https://biling.duckdns.org/invoices/generate`

**Screenshot:**
```
[SCREENSHOT: invoices-generate.png]
Gambar 6.2: Generate Invoice - Modal form untuk generate tagihan otomatis
```

**Deskripsi:**
Fitur untuk membuat tagihan secara otomatis untuk semua pelanggan aktif dalam periode tertentu.

**Field yang Tersedia:**
- **Bulan:** Pilih bulan (1-12) (required)
- **Tahun:** Pilih tahun (required)
- **Customer:** Pilih customer tertentu atau biarkan kosong untuk semua (optional)

**Cara Menggunakan:**
1. Klik tombol "Generate Tagihan" di halaman invoices
2. Pilih periode (bulan dan tahun)
3. Pilih customer (atau biarkan kosong untuk semua)
4. Klik "Generate"
5. Sistem akan membuat tagihan untuk semua pelanggan aktif

**Hasil:**
- Tagihan akan dibuat untuk semua pelanggan aktif di periode tersebut
- Nomor tagihan auto-generate
- Total amount dihitung berdasarkan biaya bulanan customer
- Status default: PENDING

---

### 6.3 Print Invoice

**URL:** `https://biling.duckdns.org/invoices/{invoice}/print`

**Screenshot:**
```
[SCREENSHOT: invoices-print.png]
Gambar 6.3: Print Invoice - Halaman print tagihan
```

**Deskripsi:**
Halaman untuk print tagihan dalam format PDF-ready. Tagihan akan terbuka di tab baru untuk di-print.

**Fitur:**
- Format print-friendly
- Informasi lengkap tagihan
- Detail customer
- Detail item tagihan
- Total amount
- Status pembayaran

**Cara Menggunakan:**
1. Buka detail invoice
2. Klik tombol "Print"
3. Tagihan akan terbuka di tab baru
4. Gunakan print browser (Ctrl+P / Cmd+P)
5. Pilih printer atau save as PDF

---

### 6.4 Kirim Notifikasi WhatsApp Invoice

**URL:** `https://biling.duckdns.org/invoices` (WhatsApp button)

**Screenshot:**
```
[SCREENSHOT: invoices-whatsapp.png]
Gambar 6.4: Kirim WhatsApp - Konfirmasi pengiriman notifikasi tagihan
```

**Deskripsi:**
Fitur untuk mengirim notifikasi tagihan ke pelanggan melalui WhatsApp secara langsung dari halaman invoices.

**Cara Menggunakan:**
1. Di halaman list invoices, klik tombol WhatsApp pada tagihan
2. Konfirmasi pengiriman
3. Sistem akan mengirim notifikasi tagihan ke nomor WhatsApp pelanggan
4. Status pengiriman dapat dilihat di halaman WhatsApp Notifications

---

## 7. Inventory Management

### 7.1 List Inventory

**URL:** `https://biling.duckdns.org/inventory`

**Screenshot:**
```
[SCREENSHOT: inventory-list.png]
Gambar 7.1: Daftar Inventory - Tabel dengan filter dan alert stok menipis
```

**Deskripsi:**
Halaman untuk mengelola stok perangkat dan bahan (router, ONT, kabel, dll). Hanya Admin yang dapat mengakses fitur ini.

**Fitur:**
- ✅ CRUD item inventori lengkap
- ✅ Restock (tambah stok)
- ✅ Use item (penggunaan per customer/perangkat)
- ✅ Alert stok menipis (low stock alert)
- ✅ History pemakaian inventori
- ✅ Filter berdasarkan status dan lokasi
- ✅ Tracking penggunaan per customer

**Kolom Tabel:**
- Nama Item
- Deskripsi
- Stok Quantity
- Unit
- Minimum Stock Alert
- Lokasi
- Status
- Aksi (View, Edit, Use, Restock, Delete)

**Role Access:**
- ✅ Admin - Full access
- ❌ Manager - No access
- ❌ Moderator - No access
- ❌ Staff - No access
- ❌ User - No access

**Cara Menggunakan:**
1. Klik menu "Inventory Management" di sidebar (Admin only)
2. Lihat daftar semua item inventori
3. Gunakan filter untuk menyaring berdasarkan status atau lokasi
4. Lihat badge "Low Stock" untuk item dengan stok menipis
5. Klik tombol "Tambah Item" untuk membuat item baru
6. Klik tombol action untuk view/edit/use/restock/delete item

---

### 7.2 Tambah Item Inventori

**URL:** `https://biling.duckdns.org/inventory/create`

**Screenshot:**
```
[SCREENSHOT: inventory-create.png]
Gambar 7.2: Form Tambah Item - Modal dengan form input item inventori
```

**Deskripsi:**
Form untuk menambahkan item inventori baru ke sistem.

**Field yang Tersedia:**
- **Nama Item:** Nama item (required)
- **Deskripsi:** Deskripsi item (optional)
- **Stok Quantity:** Jumlah stok awal (required)
- **Unit:** Unit satuan (pcs, meter, roll, dll) (required)
- **Minimum Stock Alert:** Minimum stok untuk alert (required)
- **Lokasi:** Lokasi penyimpanan (optional)
- **Status:** Aktif/Nonaktif (required)

---

### 7.3 Restock Item

**URL:** `https://biling.duckdns.org/inventory/{inventory}/restock`

**Screenshot:**
```
[SCREENSHOT: inventory-restock.png]
Gambar 7.3: Form Restock - Modal untuk menambah stok item
```

**Deskripsi:**
Form untuk menambah stok item inventori yang sudah ada.

**Field yang Tersedia:**
- **Jumlah Stok yang Ditambahkan:** Jumlah stok yang akan ditambahkan (required, min 1)

**Cara Menggunakan:**
1. Klik tombol "Restock" pada item yang ingin ditambah stoknya
2. Masukkan jumlah stok yang ditambahkan
3. Klik "Simpan"
4. Stok akan otomatis bertambah

---

### 7.4 Use Item (Penggunaan)

**URL:** `https://biling.duckdns.org/inventory/{inventory}/use`

**Screenshot:**
```
[SCREENSHOT: inventory-use.png]
Gambar 7.4: Form Use Item - Modal untuk mencatat penggunaan item
```

**Deskripsi:**
Form untuk mencatat penggunaan item inventori oleh customer atau untuk keperluan tertentu.

**Field yang Tersedia:**
- **Tipe Penggunaan:** Installation/Replacement/Maintenance/Other (required)
- **Customer:** Pilih customer (optional, jika terkait dengan customer)
- **Device:** Pilih device (optional, muncul setelah memilih customer)
- **Jumlah:** Jumlah yang digunakan (required, min 1)
- **Catatan:** Catatan tambahan (optional)

**Cara Menggunakan:**
1. Klik tombol "Use" pada item yang ingin digunakan
2. Pilih tipe penggunaan
3. Pilih customer dan device (jika terkait)
4. Masukkan jumlah yang digunakan
5. Tambahkan catatan (opsional)
6. Klik "Simpan"
7. Stok akan otomatis berkurang

---

### 7.5 History Penggunaan Inventori

**URL:** `https://biling.duckdns.org/inventory/{id}` (tab History)

**Screenshot:**
```
[SCREENSHOT: inventory-history.png]
Gambar 7.5: History Penggunaan - Tabel history penggunaan item inventori
```

**Deskripsi:**
Halaman detail item inventori menampilkan history semua penggunaan item tersebut.

**Informasi yang Ditampilkan:**
- Tanggal penggunaan
- Tipe penggunaan
- Customer (jika terkait)
- Device (jika terkait)
- Jumlah yang digunakan
- Digunakan oleh (user)
- Catatan

---

## 8. WhatsApp Notification

### 8.1 List Notifikasi WhatsApp

**URL:** `https://biling.duckdns.org/whatsapp`

**Screenshot:**
```
[SCREENSHOT: whatsapp-list.png]
Gambar 8.1: Daftar Notifikasi WhatsApp - Tabel history pengiriman
```

**Deskripsi:**
Halaman untuk melihat history semua notifikasi WhatsApp yang telah dikirim melalui sistem.

**Fitur:**
- ✅ History semua notifikasi
- ✅ Filter berdasarkan status
- ✅ Resend untuk notifikasi yang gagal
- ✅ Detail notifikasi lengkap
- ✅ Status tracking (pending, sent, failed)

**Kolom Tabel:**
- Tanggal
- Customer
- Invoice (jika terkait)
- Nomor Telepon
- Pesan (preview)
- Status
- Aksi (View, Resend)

**Role Access:**
- ✅ Admin - Full access
- ✅ Manager - Full access
- ❌ Moderator - No access
- ❌ Staff - No access
- ❌ User - No access

---

### 8.2 Kirim Pesan Manual

**URL:** `https://biling.duckdns.org/whatsapp/create`

**Screenshot:**
```
[SCREENSHOT: whatsapp-create.png]
Gambar 8.2: Form Kirim Pesan - Modal untuk kirim pesan manual
```

**Deskripsi:**
Form untuk mengirim pesan WhatsApp manual ke pelanggan atau nomor tertentu.

**Field yang Tersedia:**
- **Customer:** Pilih customer (optional, untuk auto-fill nomor)
- **Invoice:** Pilih invoice (optional, untuk auto-fill nomor)
- **Nomor Telepon:** Nomor WhatsApp tujuan (required)
- **Pesan:** Isi pesan yang akan dikirim (required)

**Cara Menggunakan:**
1. Klik tombol "Kirim Pesan" di halaman WhatsApp Notifications
2. Pilih customer atau invoice (untuk auto-fill nomor)
3. Atau masukkan nomor telepon secara manual
4. Tulis pesan yang ingin dikirim
5. Klik "Kirim"
6. Sistem akan mengirim pesan melalui Fonnte API

---

### 8.3 Detail Notifikasi

**URL:** `https://biling.duckdns.org/whatsapp/{id}`

**Screenshot:**
```
[SCREENSHOT: whatsapp-detail.png]
Gambar 8.3: Detail Notifikasi - Informasi lengkap notifikasi WhatsApp
```

**Deskripsi:**
Halaman detail menampilkan informasi lengkap tentang notifikasi WhatsApp termasuk status, response dari API, dan error message (jika ada).

**Informasi yang Ditampilkan:**
- Status (pending, sent, failed)
- Customer
- Invoice (jika terkait)
- Nomor Telepon
- Template (jika menggunakan template)
- Pesan
- Tanggal Dijadwalkan
- Tanggal Dikirim
- Error Message (jika gagal)
- Provider Response (response dari Fonnte API)

**Fitur:**
- Tombol "Kirim Ulang" untuk notifikasi yang gagal
- Copy nomor telepon
- Copy pesan

---

## 9. Payment Report

### 9.1 Laporan Pembayaran

**URL:** `https://biling.duckdns.org/payments/report`

**Screenshot:**
```
[SCREENSHOT: payment-report.png]
Gambar 9.1: Laporan Pembayaran - Halaman dengan filter dan tabel detail pembayaran
```

**Deskripsi:**
Halaman untuk melihat dan menganalisis laporan pembayaran pelanggan dengan berbagai filter dan opsi export.

**Fitur:**
- ✅ Filter berdasarkan periode (tanggal mulai - tanggal akhir)
- ✅ Filter berdasarkan customer
- ✅ Filter berdasarkan metode pembayaran
- ✅ Filter berdasarkan staff yang menerima pembayaran
- ✅ Export ke CSV
- ✅ Export ke Excel
- ✅ Summary statistik pembayaran
- ✅ Grafik pendapatan harian

**Summary Cards:**
- Total Pembayaran
- Jumlah Transaksi
- Cash (jumlah dan total)
- Transfer (jumlah dan total)

**Kolom Tabel:**
- Tanggal
- No. Invoice
- Customer
- Metode (Cash/Transfer)
- Jumlah
- Diterima Oleh
- Bukti Transfer
- Catatan

**Role Access:**
- ✅ Admin - Full access
- ✅ Manager - Full access
- ❌ Moderator - No access
- ❌ Staff - No access
- ❌ User - No access

**Cara Menggunakan:**
1. Klik menu "Laporan Pembayaran" di sidebar
2. Pilih periode laporan (tanggal mulai dan tanggal akhir)
3. Pilih customer (opsional, biarkan kosong untuk semua)
4. Pilih metode pembayaran (opsional)
5. Pilih staff yang menerima (opsional)
6. Klik "Filter" untuk melihat laporan
7. Klik "Export CSV" atau "Export Excel" untuk download laporan

---

### 9.2 Export Laporan

**URL:** `https://biling.duckdns.org/payments/report/export`

**Screenshot:**
```
[SCREENSHOT: payment-report-export.png]
Gambar 9.2: Export Laporan - Dialog download file CSV/Excel
```

**Deskripsi:**
Fitur untuk mengexport laporan pembayaran ke format CSV atau Excel.

**Format Export:**
- CSV (.csv)
- Excel (.xls)

**Isi Laporan:**
- Ringkasan statistik
- Detail berdasarkan metode pembayaran
- Detail semua pembayaran

---

## 10. Map & Location

### 10.1 Peta Pelanggan

**URL:** `https://biling.duckdns.org/map`

**Screenshot:**
```
[SCREENSHOT: map-customers.png]
Gambar 10.1: Peta Pelanggan - Peta interaktif dengan marker customer
```

**Deskripsi:**
Halaman untuk melihat visualisasi lokasi semua pelanggan di peta interaktif menggunakan Leaflet atau Google Maps.

**Fitur:**
- ✅ Peta interaktif dengan marker pelanggan
- ✅ Info popup per pelanggan
- ✅ Filter berdasarkan type pelanggan
- ✅ Filter berdasarkan status aktif
- ✅ Klik marker untuk detail pelanggan
- ✅ Warna marker berdasarkan status tagihan

**Warna Marker:**
- **Hijau:** Tagihan sudah dibayar (PAID)
- **Merah:** Tagihan terlambat (OVERDUE)
- **Kuning:** Tagihan belum dibayar tapi belum jatuh tempo (UNPAID)
- **Abu-abu:** Belum ada tagihan

**Role Access:**
- ✅ Admin - Full access (semua customer)
- ✅ Manager - Full access (semua customer)
- ❌ Moderator - No access
- ❌ Staff - No access (menggunakan Field Officer map)
- ❌ User - No access

**Cara Menggunakan:**
1. Klik menu "Peta Lokasi" di sidebar
2. Peta akan menampilkan semua pelanggan yang memiliki koordinat GPS
3. Gunakan filter untuk menyaring pelanggan berdasarkan type atau status
4. Klik marker untuk melihat informasi pelanggan
5. Klik nama pelanggan di popup untuk membuka detail pelanggan

---

## 11. Field Officer Dashboard

### 11.1 Dashboard Staff

**URL:** `https://biling.duckdns.org/field-officer/dashboard`

**Screenshot:**
```
[SCREENSHOT: field-officer-dashboard.png]
Gambar 11.1: Dashboard Staff - Overview untuk field officer
```

**Deskripsi:**
Dashboard khusus untuk field officer/staff yang menampilkan statistik pelanggan yang ditugaskan kepada mereka.

**Fitur:**
- ✅ Statistik pelanggan yang ditugaskan
- ✅ Total tagihan belum dibayar
- ✅ Tagihan yang dibayar hari ini
- ✅ Daftar pelanggan yang ditugaskan
- ✅ Quick access ke detail pelanggan

**Statistik:**
- Total Pelanggan yang Ditugaskan
- Tagihan Belum Dibayar
- Tagihan Dibayar Hari Ini

**Role Access:**
- ✅ Staff - Full access (hanya pelanggan yang ditugaskan)
- ❌ Admin - No access (menggunakan dashboard utama)
- ❌ Manager - No access
- ❌ Moderator - No access
- ❌ User - No access

---

### 11.2 Daftar Pelanggan Staff

**URL:** `https://biling.duckdns.org/field-officer/customers`

**Screenshot:**
```
[SCREENSHOT: field-officer-customers.png]
Gambar 11.2: Daftar Pelanggan Staff - Tabel pelanggan yang ditugaskan
```

**Deskripsi:**
Halaman untuk melihat daftar pelanggan yang ditugaskan kepada field officer tertentu.

**Fitur:**
- ✅ Daftar pelanggan yang ditugaskan
- ✅ Search pelanggan
- ✅ Lihat detail pelanggan
- ✅ Lihat tagihan pelanggan
- ✅ Input pembayaran

**Kolom Tabel:**
- Nama Pelanggan
- Kode Pelanggan
- Telepon
- Alamat
- Tagihan Terbaru
- Status Tagihan
- Aksi (View, Input Pembayaran)

**Cara Menggunakan:**
1. Login sebagai staff/field officer
2. Klik menu "Daftar Pelanggan" di sidebar (section Tim Staff)
3. Lihat daftar pelanggan yang ditugaskan
4. Gunakan search untuk mencari pelanggan
5. Klik nama pelanggan untuk melihat detail
6. Klik "Input Pembayaran" untuk mencatat pembayaran tagihan

---

### 11.3 Detail Pelanggan Staff

**URL:** `https://biling.duckdns.org/field-officer/customers/{customer}`

**Screenshot:**
```
[SCREENSHOT: field-officer-customer-detail.png]
Gambar 11.3: Detail Pelanggan Staff - Informasi lengkap pelanggan dan tagihan
```

**Deskripsi:**
Halaman detail pelanggan untuk field officer menampilkan informasi lengkap termasuk daftar tagihan dan opsi input pembayaran.

**Informasi yang Ditampilkan:**
- Informasi dasar pelanggan
- Daftar semua tagihan
- Status setiap tagihan
- History pembayaran
- Tombol "Input Pembayaran" untuk tagihan yang belum dibayar

---

### 11.4 Input Pembayaran

**URL:** `https://biling.duckdns.org/field-officer/invoices/{invoice}/payment`

**Screenshot:**
```
[SCREENSHOT: field-officer-payment.png]
Gambar 11.4: Form Input Pembayaran - Modal untuk input pembayaran tagihan
```

**Deskripsi:**
Form untuk field officer untuk mencatat pembayaran tagihan pelanggan yang ditugaskan kepada mereka.

**Field yang Tersedia:**
- **Metode Pembayaran:** Cash/Transfer (required)
- **Jumlah:** Jumlah pembayaran (required)
- **Tanggal Pembayaran:** Tanggal pembayaran (required)
- **Bukti Transfer:** Upload bukti transfer (required jika metode Transfer)
- **Foto Lapangan:** Upload foto saat menerima pembayaran (optional)
- **Catatan:** Catatan tambahan (optional)

**Cara Menggunakan:**
1. Buka detail pelanggan
2. Lihat daftar tagihan pelanggan
3. Klik tombol "Input Pembayaran" pada tagihan yang belum dibayar
4. Pilih metode pembayaran
5. Masukkan jumlah pembayaran
6. Upload bukti transfer (jika transfer)
7. Upload foto lapangan (opsional)
8. Tambahkan catatan (opsional)
9. Klik "Simpan"
10. Status tagihan akan otomatis berubah menjadi "PAID"

---

### 11.5 Peta Pelanggan Staff

**URL:** `https://biling.duckdns.org/field-officer/map`

**Screenshot:**
```
[SCREENSHOT: field-officer-map.png]
Gambar 11.5: Peta Pelanggan Staff - Peta interaktif pelanggan yang ditugaskan
```

**Deskripsi:**
Peta interaktif yang menampilkan lokasi pelanggan yang ditugaskan kepada field officer tertentu.

**Fitur:**
- ✅ Hanya menampilkan pelanggan yang ditugaskan
- ✅ Marker dengan warna berdasarkan status tagihan
- ✅ Info popup per pelanggan
- ✅ Klik untuk detail pelanggan

**Warna Marker:**
- **Hijau:** Tagihan sudah dibayar
- **Merah:** Tagihan terlambat
- **Kuning:** Tagihan belum dibayar tapi belum jatuh tempo
- **Abu-abu:** Belum ada tagihan

---

## 12. Profile Management

### 12.1 Halaman Profil

**URL:** `https://biling.duckdns.org/profile`

**Screenshot:**
```
[SCREENSHOT: profile-page.png]
Gambar 12.1: Halaman Profil - Form untuk update profil user
```

**Deskripsi:**
Halaman untuk mengelola profil user yang sedang login, termasuk update informasi dan password.

**Fitur:**
- ✅ Update informasi profil (nama, email)
- ✅ Update password
- ✅ Validasi form
- ✅ Preview foto profil

**Field yang Tersedia:**
- **Nama:** Nama lengkap user (required)
- **Email:** Email user (required, unique)
- **Password Baru:** Password baru (optional, kosongkan jika tidak ingin mengubah)
- **Konfirmasi Password:** Konfirmasi password baru (required jika password diisi)

**Role Access:**
- ✅ Semua Role - Full access (hanya untuk profil sendiri)

**Cara Menggunakan:**
1. Klik menu "Profile" di header (dropdown user)
2. Update informasi yang ingin diubah
3. Untuk update password, isi field password baru dan konfirmasi password
4. Klik "Simpan"
5. Profil akan terupdate

---

## 13. Role & Permission Matrix

### 13.1 Tabel Akses Fitur per Role

| Fitur | Admin | Manager | Moderator | Staff | User |
|-------|:-----:|:-------:|:---------:|:-----:|:----:|
| **Dashboard** | ✅ | ✅ | ✅ | ✅ | ✅ |
| **User Management** | ✅ | ✅* | ❌ | ❌ | ❌ |
| **Customer Management** | ✅ | ✅ | ✅* | ❌ | ❌ |
| **Device Management** | ✅ | ✅ | ✅* | ❌ | ❌ |
| **Invoice Management** | ✅ | ✅ | ✅* | ❌ | ❌ |
| **Inventory Management** | ✅ | ❌ | ❌ | ❌ | ❌ |
| **WhatsApp Notification** | ✅ | ✅ | ❌ | ❌ | ❌ |
| **Payment Report** | ✅ | ✅ | ❌ | ❌ | ❌ |
| **Map & Location** | ✅ | ✅ | ❌ | ❌ | ❌ |
| **Field Officer Dashboard** | ❌ | ❌ | ❌ | ✅ | ❌ |
| **Field Officer Customers** | ❌ | ❌ | ❌ | ✅ | ❌ |
| **Field Officer Map** | ❌ | ❌ | ❌ | ✅ | ❌ |
| **Field Officer Payment** | ❌ | ❌ | ❌ | ✅ | ❌ |
| **Profile** | ✅ | ✅ | ✅ | ✅ | ✅ |

**Keterangan:**
- ✅ = Full access
- ✅* = Limited access (view only untuk Moderator, edit only untuk Manager di User Management)
- ❌ = No access

---

### 13.2 Permission Details

#### Admin Permissions
- Semua permissions (full access)

#### Manager Permissions
- `user.view`, `user.list`, `user.edit`
- `dashboard.view`
- `chat.view`, `chat.create`

#### Moderator Permissions
- `user.view`, `user.list`
- `dashboard.view`
- `chat.view`, `chat.create`, `chat.delete`

#### Staff Permissions
- `payment.create`, `payment.update`, `payment.view`
- `customer.view`, `customer.list`
- `invoice.view`, `invoice.list`

#### User Permissions
- `dashboard.view`
- `chat.view`, `chat.create`

---

## 📸 Instruksi Screenshot

Untuk membuat dokumentasi dengan screenshot:

1. **Login ke sistem** dengan berbagai role (Admin, Manager, Moderator, Staff, User)
2. **Ambil screenshot** setiap halaman/fitur yang dijelaskan di dokumentasi ini
3. **Simpan screenshot** dengan nama sesuai yang disebutkan di dokumentasi (contoh: `login-page.png`, `dashboard-main.png`)
4. **Tempatkan screenshot** di folder `docs/screenshots/` atau `public/docs/screenshots/`
5. **Update path screenshot** di dokumentasi sesuai lokasi file

**Format Screenshot:**
- Format: PNG atau JPG
- Resolusi: Minimal 1920x1080 untuk desktop, 375x667 untuk mobile
- Naming: Gunakan format `{feature}-{action}.png` (contoh: `customers-list.png`, `invoices-create.png`)

---

## 📄 Generate PDF

Untuk mengkonversi dokumentasi ini ke PDF:

### Menggunakan Pandoc (Recommended)
```bash
# Install pandoc (jika belum)
# macOS: brew install pandoc
# Ubuntu: sudo apt-get install pandoc

# Convert to PDF
pandoc FEATURES-DOCUMENTATION.md -o FEATURES-DOCUMENTATION.pdf --pdf-engine=xelatex -V geometry:margin=1in
```

### Menggunakan Online Tools
1. Buka https://www.markdowntopdf.com/
2. Upload file `FEATURES-DOCUMENTATION.md`
3. Download PDF

### Menggunakan VS Code Extension
1. Install extension "Markdown PDF"
2. Buka file `FEATURES-DOCUMENTATION.md`
3. Klik kanan > "Markdown PDF: Export (pdf)"

---

**Last Updated:** 2024  
**Version:** 1.0.0  
**Documentation by:** ISP Billing Management System Team

