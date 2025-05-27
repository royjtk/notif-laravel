<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\Document;
use Illuminate\Contracts\Queue\ShouldQueue;

class DocumentUploaded extends Notification implements ShouldQueue
{
    use Queueable;

    protected $document;

    public function __construct(Document $document)
    {
        $this->document = $document;
    }

    public function via($notifiable)
    {
        $channels = ['database'];  // Always send to database for in-app notifications
        
        if ($notifiable->notify_on_document_upload) {
            $channels[] = 'mail';
        }
        
        return $channels;
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
