<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Notifications\CustomNotification;
use App\Notifications\CustomEmailNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware("auth");
    }

    public function create()
    {
        $users = User::all();
        return view("notifications.create", compact("users"));
    }

    public function createCustom()
    {
        return view("notifications.create-custom");
    }

    public function send(Request $request)
    {
        $request->validate([
            "users" => "required|array",
            "users.*" => "exists:users,id",
            "title" => "required|string|max:255",
            "message" => "required|string"
        ]);

        $users = User::whereIn("id", $request->users)->get();

        foreach ($users as $user) {
            $user->notify(new CustomNotification($request->title, $request->message));
        }

        return redirect()->back()->with("success", "Notifikasi berhasil dikirim!");
    }

    public function sendCustom(Request $request)
    {
        $request->validate([
            "emails" => "required|string",
            "title" => "required|string|max:255",
            "message" => "required|string"
        ]);

        $emails = array_map("trim", explode(",", $request->emails));
        $successCount = 0;
        $failedEmails = [];

        foreach ($emails as $email) {
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                try {
                    Log::info("Attempting to send email to: " . $email);

                    $notification = new CustomEmailNotification(
                        $request->title,
                        $request->message,
                        $email
                    );

                    Notification::route("mail", ["email" => $email, "name" => "Recipient"])
                        ->notify($notification);

                    $successCount++;
                    Log::info("Email notification queued successfully for: " . $email);
                } catch (\Exception $e) {
                    Log::error("Failed to queue email for " . $email . ": " . $e->getMessage());
                    $failedEmails[] = $email;
                }
            } else {
                Log::warning("Invalid email format: " . $email);
                $failedEmails[] = $email;
            }
        }

        if ($failedEmails) {
            return redirect()->back()
                ->with("warning", "Beberapa email gagal terkirim: " . implode(", ", $failedEmails))
                ->withInput();
        }

        return redirect()->back()->with("success", "Notifikasi berhasil dikirim ke {$successCount} email!");
    }
}
