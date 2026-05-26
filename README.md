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

