<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InternalAccountCreatedNotification extends Notification
{
    use Queueable;

    public function __construct(
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
            ->greeting('Halo ' . ($notifiable->name ?? $this->roleLabel) . ',')
            ->line('Akun ' . $this->roleLabel . ' Anda sudah berhasil dibuat di sistem Trenmart.')
            ->line('Berikut detail akun Anda:')
            ->line('Email: ' . ($notifiable->email ?? '-'))
            ->line('Password awal: ' . $this->plainPassword)
            ->action('Masuk ke Trenmart', $this->loginUrl)
            ->line('Silakan login menggunakan kredensial di atas, lalu segera ubah password Anda setelah masuk.')
            ->salutation('Salam hangat, Tim Trenmart');
    }
}