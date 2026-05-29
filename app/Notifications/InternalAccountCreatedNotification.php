<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InternalAccountCreatedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $accountName,
        public string $accountEmail,
        public string $roleLabel,
        public string $plainPassword,
        public string $loginUrl,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Akun ' . $this->roleLabel . ' Trenmart')
            ->greeting('Halo ' . ($this->accountName ?: $this->roleLabel) . ',')
            ->line('Akun Anda sudah berhasil dibuat di sistem Trenmart.')
            ->line('Berikut informasi akun Anda:')
            ->line('Nama: ' . $this->accountName)
            ->line('Email: ' . $this->accountEmail)
            ->line('Role: ' . $this->roleLabel)
            ->line('Password awal: ' . $this->plainPassword)
            ->action('Masuk ke Trenmart', $this->loginUrl)
            ->line('Silakan login menggunakan kredensial di atas, lalu segera ubah password Anda setelah masuk.')
            ->salutation('Salam hangat, Tim Trenmart');
    }
}