<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Verifikasi Email Trenmart</title>
</head>
<body>
    <p>Halo {{ $user->name }},</p>

    <p>Terima kasih sudah mendaftar di Trenmart. Silakan klik tautan di bawah untuk memverifikasi alamat email Anda:</p>

    <p><a href="{{ $url }}">Verifikasi Email Saya</a></p>

    <p>Jika tautan di atas tidak dapat diklik, salin dan tempel URL berikut ke peramban Anda:</p>
    <p>{{ $url }}</p>

    <p>Tautan ini akan kedaluwarsa dalam 60 menit.</p>

    <p>Salam,<br>Tim Trenmart</p>
</body>
</html>