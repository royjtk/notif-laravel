<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CustomEmailNotification extends Notification implements ShouldQueue
{
    use Queueable;

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
        return (new MailMessage)
                    ->subject($this->title)
                    ->line($this->message)
                    ->line('Email ini dikirim dari sistem Document Management.');
    }

    public function getEmail()
    {
        return $this->email;
    }
}
