<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Contracts\Queue\ShouldQueue;

class CustomNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $title;
    protected $message;

    public function __construct($title, $message)
    {
        $this->title = $title;
        $this->message = $message;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->subject($this->title)
                    ->greeting('Halo ' . $notifiable->name . '!')
                    ->line($this->message)
                    ->line('Terima kasih sudah menggunakan aplikasi kami.');
    }

    public function toArray($notifiable)
    {
        return [
            'title' => $this->title,
            'message' => $this->message
        ];
    }
}
