# tesKP

## Setup Cloudinary (Untuk Vercel)

Project ini sudah dijalurkan agar upload gambar bisa disimpan ke Cloudinary.

### 1. Isi Environment Variables

Tambahkan variable berikut di Vercel Project Settings -> Environment Variables:

```env
MEDIA_DISK=cloudinary
CLOUDINARY_URL=cloudinary://API_KEY:API_SECRET@CLOUD_NAME
CLOUDINARY_KEY=API_KEY
CLOUDINARY_SECRET=API_SECRET
CLOUDINARY_CLOUD_NAME=CLOUD_NAME
CLOUDINARY_SECURE=true
CLOUDINARY_PREFIX=trenmart
```

Catatan:
- `MEDIA_DISK=cloudinary` mengalihkan semua upload gambar (produk, banner, bukti transfer) ke Cloudinary.
- Jika ingin balik ke penyimpanan lokal, set `MEDIA_DISK=public`.

### 2. Deploy Ulang

Setelah env diisi, lakukan redeploy di Vercel agar konfigurasi baru aktif.

### 3. Kompatibilitas Data Lama

Data gambar lama yang masih berupa path lokal tetap didukung lewat fallback helper gambar.

## Setup Environment Vercel

Environment di Vercel harus memakai nama variabel yang sama persis dengan yang dibaca Laravel di code. Untuk project ini, yang penting adalah:

```env
APP_NAME=Laravel
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:...
APP_URL=https://sistempenjualantrenmartkp.vercel.app

DB_CONNECTION=mysql
DB_HOST=...
DB_PORT=3306
DB_DATABASE=...
DB_USERNAME=...
DB_PASSWORD=...

SESSION_DRIVER=cookie
QUEUE_CONNECTION=sync
FILESYSTEM_DISK=cloudinary

MAIL_MAILER=smtp
MAIL_SCHEME=tls
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=...
MAIL_PASSWORD=...
MAIL_FROM_ADDRESS=...
MAIL_FROM_NAME="${APP_NAME}"

CLOUDINARY_URL=...
CLOUDINARY_CLOUD_NAME=...
CLOUDINARY_UPLOAD_PRESET=...
CLOUDINARY_API_KEY=...
CLOUDINARY_API_SECRET=...
```

Catatan penting:
- Kode mail di project ini membaca `MAIL_SCHEME`, bukan `MAIL_ENCRYPTION`.
- Vercel tidak menyediakan server email sendiri, jadi `MAIL_HOST` dan `MAIL_PORT` harus dari provider SMTP eksternal seperti Gmail, Mailtrap, SendGrid, atau Postmark.
- `APP_URL` harus sama dengan domain publik Vercel agar link verifikasi email valid.

### Langkah set env di Vercel

1. Buka Vercel Dashboard.
2. Masuk ke Project `tesKP`.
3. Buka `Settings` > `Environment Variables`.
4. Tambahkan variabel satu per satu sesuai daftar di atas.
5. Pilih scope `Production`.
6. Jika ingin dicoba di preview, tambahkan juga scope `Preview`.
7. Simpan lalu lakukan redeploy.

### Env yang perlu diganti sesuai code

- `APP_URL` gunakan domain Vercel yang aktif.
- `MAIL_MAILER` tetap `smtp`.
- `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_FROM_ADDRESS` harus sesuai provider SMTP.
- `MAIL_SCHEME` isi `tls` untuk Gmail atau Mailtrap TLS.
- Jangan pakai `MAIL_ENCRYPTION` jika mengikuti code project ini, karena file `config/mail.php` tidak membacanya.

