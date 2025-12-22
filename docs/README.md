# Dokumentasi ISP Billing Management System

Folder ini berisi dokumentasi lengkap sistem termasuk screenshot dan panduan.

## 📁 Struktur Folder

```
docs/
├── screenshots/          # Screenshot semua fitur
│   ├── authentication/   # Screenshot halaman auth
│   ├── dashboard/        # Screenshot dashboard
│   ├── customers/        # Screenshot customer management
│   ├── invoices/         # Screenshot invoice management
│   └── ...               # Folder lainnya
├── SCREENSHOT-GUIDE.md   # Panduan screenshot
└── README.md            # File ini
```

## 📄 File Dokumentasi

- `FEATURES-DOCUMENTATION.md` - Dokumentasi lengkap semua fitur (root folder)
- `README.md` - Dokumentasi utama sistem (root folder)
- `FEATURES-AND-ROLES.md` - Dokumentasi role dan permission (root folder)

## 🖼️ Screenshot

Screenshot disimpan di folder `screenshots/` dengan struktur sesuai fitur.

Lihat `SCREENSHOT-GUIDE.md` untuk panduan lengkap mengambil screenshot.

## 📄 Generate PDF

Untuk generate PDF dari dokumentasi:

```bash
# Menggunakan script
./generate-pdf.sh

# Atau manual dengan pandoc
pandoc FEATURES-DOCUMENTATION.md -o FEATURES-DOCUMENTATION.pdf --pdf-engine=xelatex
```

## 📝 Notes

- Pastikan semua screenshot sudah diambil sebelum generate PDF
- Update path screenshot di dokumentasi sesuai struktur folder
- Review PDF sebelum publish

