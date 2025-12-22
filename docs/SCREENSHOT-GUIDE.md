# Panduan Screenshot untuk Dokumentasi

Panduan lengkap untuk mengambil screenshot setiap fitur di aplikasi ISP Billing Management System.

## 📋 Daftar Screenshot yang Diperlukan

### 1. Authentication
- [ ] `login-page.png` - Halaman login
- [ ] `register-page.png` - Halaman register
- [ ] `forgot-password-page.png` - Halaman lupa password
- [ ] `reset-password-page.png` - Halaman reset password

### 2. Dashboard
- [ ] `dashboard-main.png` - Dashboard utama
- [ ] `dashboard-export.png` - Dialog export dashboard

### 3. User Management
- [ ] `users-list.png` - Daftar users
- [ ] `users-create.png` - Form tambah user
- [ ] `users-edit.png` - Form edit user
- [ ] `users-permissions.png` - Assign permissions

### 4. Customer Management
- [ ] `customers-list.png` - Daftar customers
- [ ] `customers-create.png` - Form tambah customer
- [ ] `customers-detail.png` - Detail customer
- [ ] `customers-bulk-assign.png` - Bulk assign customers

### 5. Device Management
- [ ] `devices-list.png` - Daftar perangkat
- [ ] `devices-create.png` - Form tambah perangkat

### 6. Invoice Management
- [ ] `invoices-list.png` - Daftar invoices
- [ ] `invoices-generate.png` - Generate invoice
- [ ] `invoices-print.png` - Print invoice
- [ ] `invoices-whatsapp.png` - Kirim WhatsApp

### 7. Inventory Management
- [ ] `inventory-list.png` - Daftar inventory
- [ ] `inventory-create.png` - Form tambah item
- [ ] `inventory-restock.png` - Form restock
- [ ] `inventory-use.png` - Form use item
- [ ] `inventory-history.png` - History penggunaan

### 8. WhatsApp Notification
- [ ] `whatsapp-list.png` - Daftar notifikasi
- [ ] `whatsapp-create.png` - Form kirim pesan
- [ ] `whatsapp-detail.png` - Detail notifikasi

### 9. Payment Report
- [ ] `payment-report.png` - Laporan pembayaran
- [ ] `payment-report-export.png` - Export laporan

### 10. Map & Location
- [ ] `map-customers.png` - Peta pelanggan

### 11. Field Officer
- [ ] `field-officer-dashboard.png` - Dashboard staff
- [ ] `field-officer-customers.png` - Daftar pelanggan staff
- [ ] `field-officer-customer-detail.png` - Detail pelanggan staff
- [ ] `field-officer-payment.png` - Form input pembayaran
- [ ] `field-officer-map.png` - Peta pelanggan staff

### 12. Profile
- [ ] `profile-page.png` - Halaman profil

## 🛠️ Tools untuk Screenshot

### Browser Extensions (Recommended)
1. **Full Page Screen Capture** (Chrome/Edge)
   - URL: https://chrome.google.com/webstore/detail/full-page-screen-capture/fdpohaocaechififmbbbbbknoalclacl
   - Bisa capture seluruh halaman sekaligus

2. **Awesome Screenshot** (Chrome/Edge/Firefox)
   - URL: https://www.awesomescreenshot.com/
   - Bisa annotate dan edit screenshot

3. **Nimbus Screenshot** (Chrome/Edge/Firefox)
   - URL: https://nimbusweb.me/screenshot/
   - Bisa capture full page dan edit

### Desktop Tools
1. **macOS:**
   - Built-in: `Cmd + Shift + 4` (area), `Cmd + Shift + 3` (full screen)
   - **CleanShot X** (paid, recommended)
   - **Snagit** (paid)

2. **Windows:**
   - Built-in: `Win + Shift + S` (Snipping Tool)
   - **Greenshot** (free)
   - **Snagit** (paid)

3. **Linux:**
   - Built-in: `Print Screen`
   - **Flameshot** (free, recommended)
   - **Shutter** (free)

## 📐 Spesifikasi Screenshot

### Resolusi
- **Desktop:** Minimal 1920x1080 (Full HD)
- **Tablet:** 1024x768 atau 1366x768
- **Mobile:** 375x667 (iPhone SE) atau 390x844 (iPhone 12)

### Format
- **Format:** PNG (recommended) atau JPG
- **Quality:** High quality (minimal 80% untuk JPG)
- **Color:** RGB

### Naming Convention
- Format: `{feature}-{action}.png`
- Contoh: `customers-list.png`, `invoices-create.png`, `field-officer-dashboard.png`
- Gunakan lowercase dengan dash sebagai separator

## 📝 Tips Screenshot

### 1. Persiapan
- Pastikan browser dalam mode full screen atau windowed (tidak minimized)
- Hapus extension yang tidak perlu dari browser
- Gunakan data sample yang rapi dan konsisten
- Pastikan tidak ada informasi sensitif di screenshot

### 2. Saat Screenshot
- Capture seluruh halaman (jika memungkinkan)
- Pastikan semua elemen penting terlihat
- Hindari scroll bar jika tidak perlu
- Pastikan text readable dan tidak blur

### 3. Editing (Opsional)
- Crop jika perlu untuk fokus ke area penting
- Blur informasi sensitif jika ada
- Tambahkan annotation jika perlu (panah, highlight, dll)
- Pastikan konsisten dengan screenshot lainnya

## 🔐 Data untuk Screenshot

### Test Accounts
Gunakan akun test berikut untuk screenshot:

**Admin:**
- Email: admin@example.com
- Password: password

**Manager:**
- Email: manager@example.com
- Password: password

**Staff:**
- Email: staff@example.com (buat jika belum ada)
- Password: password

**Customer Sample:**
- Nama: "PT. Contoh Perusahaan"
- Kode: "CUST001"
- Alamat: "Jl. Contoh No. 123, Jakarta"

## 📁 Struktur Folder

```
docs/
├── screenshots/
│   ├── authentication/
│   │   ├── login-page.png
│   │   ├── register-page.png
│   │   └── ...
│   ├── dashboard/
│   │   ├── dashboard-main.png
│   │   └── ...
│   ├── customers/
│   │   ├── customers-list.png
│   │   └── ...
│   └── ...
└── SCREENSHOT-GUIDE.md
```

## 🚀 Workflow

1. **Setup Environment**
   - Login dengan berbagai role
   - Siapkan data sample
   - Install screenshot tool

2. **Take Screenshots**
   - Ikuti daftar checklist di atas
   - Simpan dengan nama sesuai konvensi
   - Simpan di folder `docs/screenshots/`

3. **Update Documentation**
   - Update path screenshot di `FEATURES-DOCUMENTATION.md`
   - Pastikan semua screenshot ter-reference

4. **Generate PDF**
   - Run script `./generate-pdf.sh`
   - Atau gunakan pandoc manual
   - Review PDF hasil

## ✅ Checklist

- [ ] Semua screenshot sudah diambil
- [ ] Semua screenshot sudah disimpan dengan nama yang benar
- [ ] Semua screenshot sudah di-reference di dokumentasi
- [ ] PDF sudah di-generate
- [ ] PDF sudah di-review dan tidak ada error

---

**Last Updated:** 2024

