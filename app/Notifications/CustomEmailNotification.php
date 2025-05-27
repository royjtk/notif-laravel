<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CustomEmailNotification extends Notification implements ShouldQueue
{

    public $tries = 3;  // Jumlah percobaan jika gagal
    public $timeout = 30;  // Batas waktu dalam detik

    protected $title;
    protected $message;
    protected $email;

    public function __construct($title, $message, $email)
    {
        $this->title = $title;
        $this->message = $message;
        $this->email = $email;
    }
    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        try {
            \Log::debug('Preparing email message', [
                'to' => $notifiable->routes['mail'],
                'subject' => $this->title,
                'message' => $this->message
            ]);

            $mail = (new MailMessage)
                ->subject($this->title)
                ->greeting('Hello!')
                ->line($this->message)
                ->line('Email ini dikirim dari sistem Document Management.')
                ->salutation('Terima kasih');

            \Log::debug('Mail message prepared successfully');

            return $mail;
        } catch (\Exception $e) {
            \Log::error('Failed to send email notification: ' . $e->getMessage());
            throw $e;
        }
    }

    public function failed(\Exception $exception)
    {
        \Log::error('Notification failed for email: ' . $this->email);
        \Log::error($exception->getMessage());
    }

    public function getEmail()
    {
        return $this->email;
    }
}
