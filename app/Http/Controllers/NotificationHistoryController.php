<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DatabaseNotification;
use App\Models\FailedJob;
use Illuminate\Support\Facades\DB;
use App\Notifications\CustomNotification;

class NotificationHistoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        // Get successful notifications
        $successfulNotifications = DatabaseNotification::orderBy('created_at', 'desc')
            ->paginate(10, ['*'], 'successful_page');

        // Get failed notification jobs
        $failedNotifications = FailedJob::where('queue', 'default')
            ->where('payload', 'like', '%CustomNotification%')
            ->orderBy('failed_at', 'desc')
            ->paginate(10, ['*'], 'failed_page');

        return view('notifications.history', compact('successfulNotifications', 'failedNotifications'));
    }    public function resend($id)
    {
        try {
            $failedJob = FailedJob::findOrFail($id);
            
            // Extract notification data from failed job payload
            $payload = json_decode($failedJob->payload, true);
            if (!isset($payload['data']['command'])) {
                throw new \Exception('Invalid job payload structure');
            }
            
            $command = unserialize($payload['data']['command']);
            if (!$command || !isset($command->title) || !isset($command->message) || !isset($command->notifiable)) {
                throw new \Exception('Invalid notification data structure');
            }
            
            // Get original notification data
            $title = $command->title;
            $message = $command->message;
            $notifiable = $command->notifiable;

            // Create and send new notification
            try {
                $notifiable->notify(new CustomNotification($title, $message));
            } catch (\Exception $e) {
                throw new \Exception('Failed to send notification: ' . $e->getMessage());
            }

            // Delete the failed job after successful resend
            $failedJob->delete();

            return back()->with('success', 'Notifikasi berhasil dikirim ulang.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mengirim ulang notifikasi: ' . $e->getMessage());
        }
    }

    /**
     * Resend all failed notifications
     */
    public function resendAll()
    {
        try {
            $failedJobs = FailedJob::where('queue', 'default')
                ->where('payload', 'like', '%CustomNotification%')
                ->get();

            if ($failedJobs->isEmpty()) {
                return back()->with('info', 'Tidak ada notifikasi yang perlu dikirim ulang.');
            }

            $successCount = 0;
            $failedCount = 0;
            $errors = [];

            foreach ($failedJobs as $job) {
                try {
                    // Extract notification data from failed job payload
                    $payload = json_decode($job->payload, true);
                    if (!isset($payload['data']['command'])) {
                        throw new \Exception('Invalid job payload structure');
                    }

                    $command = unserialize($payload['data']['command']);
                    if (!$command || !isset($command->title) || !isset($command->message) || !isset($command->notifiable)) {
                        throw new \Exception('Invalid notification data structure');
                    }

                    // Resend notification
                    $command->notifiable->notify(new CustomNotification($command->title, $command->message));
                    $job->delete();
                    $successCount++;
                } catch (\Exception $e) {
                    $failedCount++;
                    $errors[] = $e->getMessage();
                }
            }

            $message = "Hasil pengiriman ulang: {$successCount} berhasil";
            if ($failedCount > 0) {
                $message .= ", {$failedCount} gagal.";
                return back()->with('warning', $message)->with('errors', $errors);
            }

            return back()->with('success', $message . '.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mengirim ulang notifikasi: ' . $e->getMessage());
        }
    }
}
