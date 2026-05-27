<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PasswordResetCodeNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $code,
        public int $expiresInMinutes = 15,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Kode Reset Password Trenmart')
            ->greeting('Halo ' . ($notifiable->name ?? 'Pelanggan Trenmart') . ',')
            ->line('Berikut kode reset password Anda:')
            ->line('Kode: ' . $this->code)
            ->line('Kode ini berlaku selama ' . $this->expiresInMinutes . ' menit.')
            ->line('Jika Anda tidak meminta reset password, abaikan email ini.')
            ->salutation('Salam hangat, Tim Trenmart');
    }
}