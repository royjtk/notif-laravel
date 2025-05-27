<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\Document;

class DocumentUploaded extends Notification
{
    use Queueable;

    protected $document;

    public function __construct(Document $document)
    {
        $this->document = $document;
    }

    public function via($notifiable)
    {
        return ['mail', 'database']; // Kirim via email, bisa ditambah database, broadcast, dll
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->subject('Dokumen Berhasil Diunggah')
                    ->greeting('Halo ' . $notifiable->name . '!')
                    ->line('Dokumen "' . $this->document->original_name . '" berhasil kamu unggah.')
                    ->action('Lihat Dokumen', url(route('documents.index')))
                    ->line('Terima kasih sudah menggunakan aplikasi kami.');
    }

    public function toArray($notifiable)
    {
        return [
            'document_id' => $this->document->id,
            'document_name' => $this->document->original_name,
            'message' => 'Dokumen "' . $this->document->original_name . '" berhasil diupload.',
        ];
    }

}
