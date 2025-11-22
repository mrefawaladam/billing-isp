# Setup Notifikasi WhatsApp dengan Fonnte

## Konfigurasi

### 1. Tambahkan ke file `.env`:

```env
# Fonnte API Configuration
FONNTE_URL=https://api.fonnte.com
FONNTE_API_KEY=your_fonnte_api_key_here
FONNTE_DELAY_BETWEEN_MESSAGES=3
```

**Cara mendapatkan API Key Fonnte:**
1. Daftar/login di https://fonnte.com
2. Buat device/akun WhatsApp
3. Salin API Key dari dashboard
4. Paste ke `.env` sebagai `FONNTE_API_KEY`

### 2. Jalankan Migration:

```bash
php artisan migrate
```

## Fitur Anti Ban

Sistem sudah dilengkapi dengan fitur anti ban:

1. **Rate Limiting**: Max 5 pesan per 5 menit per nomor
2. **Delay Antar Pesan**: Default 3 detik (bisa diatur di `.env`)
3. **Random Delay**: Delay random 1-3 detik pada setiap request
4. **Tracking**: Semua notifikasi dicatat di database untuk monitoring

## Penggunaan Command

### Kirim Notifikasi untuk Invoice Jatuh Tempo Hari Ini:
```bash
php artisan wa:send-notifications --due-date
```

### Kirim Notifikasi untuk Invoice yang Sudah Terlambat:
```bash
php artisan wa:send-notifications --overdue
```

### Kirim Notifikasi untuk Customer Tertentu:
```bash
php artisan wa:send-notifications --customer={customer_id}
```

### Kirim Notifikasi untuk Invoice Tertentu:
```bash
php artisan wa:send-notifications --invoice={invoice_id}
```

## Scheduled Task (Auto Send)

Sistem sudah dikonfigurasi untuk auto-send:

1. **Jatuh Tempo Hari Ini**: Setiap hari jam 08:00 WIB
2. **Invoice Terlambat**: Setiap hari jam 10:00 WIB

### Setup Cron Job

Tambahkan ke crontab server:

```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

Atau jika menggunakan hosting, tambahkan di panel hosting:
- **Command**: `php artisan schedule:run`
- **Frequency**: Every minute

## Template Pesan

### 1. Jatuh Tempo Hari Ini:
```
📋 *PEMBERITAHUAN TAGIHAN*

Yth. {Nama Customer}

Tagihan Anda *jatuh tempo hari ini*.

📄 *No. Tagihan:* {Invoice Number}
📅 *Jatuh Tempo:* {Due Date}
💰 *Total Tagihan:* Rp {Amount}

Mohon segera lakukan pembayaran.

Terima kasih.
```

### 2. Invoice Terlambat:
```
⚠️ *PEMBERITAHUAN TAGIHAN TERLAMBAT*

Yth. {Nama Customer}

Tagihan Anda sudah *TERLAMBAT {X} hari*.

📄 *No. Tagihan:* {Invoice Number}
📅 *Jatuh Tempo:* {Due Date}
💰 *Total Tagihan:* Rp {Amount}

Mohon segera lakukan pembayaran untuk menghindari gangguan layanan.

Terima kasih.
```

## Monitoring

Semua notifikasi dicatat di tabel `wa_notifications` dengan informasi:
- Status (pending/sent/failed)
- Waktu pengiriman
- Response dari API
- Error message (jika gagal)

## Catatan Penting

1. **Format Nomor Telepon**: 
   - Sistem otomatis format ke format Fonnte (62xxxxxxxxxx)
   - Bisa input: 081234567890, +6281234567890, atau 6281234567890

2. **Anti Ban**:
   - Jangan ubah delay terlalu kecil (< 2 detik)
   - Rate limit: Max 5 pesan per 5 menit per nomor
   - Sistem otomatis skip jika sudah kirim hari ini

3. **Testing**:
   - Test dulu dengan `--customer` atau `--invoice` sebelum setup auto-send
   - Pastikan API key valid dan device Fonnte sudah terhubung

