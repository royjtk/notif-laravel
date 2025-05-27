<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Notifications\CustomNotification;
use App\Notifications\CustomEmailNotification;
use Illuminate\Support\Facades\Notification;

class NotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function create()
    {
        $users = User::all();
        return view('notifications.create', compact('users'));
    }

    public function createCustom()
    {
        return view('notifications.create-custom');
    }

    public function send(Request $request)
    {
        $request->validate([
            'users' => 'required|array',
            'users.*' => 'exists:users,id',
            'title' => 'required|string|max:255',
            'message' => 'required|string'
        ]);

        $users = User::whereIn('id', $request->users)->get();
        
        foreach ($users as $user) {
            $user->notify(new CustomNotification($request->title, $request->message));
        }

        return redirect()->back()->with('success', 'Notifikasi berhasil dikirim!');
    }

    public function sendCustom(Request $request)
    {
        $request->validate([
            'emails' => 'required|string',
            'title' => 'required|string|max:255',
            'message' => 'required|string'
        ]);

        $emails = array_map('trim', explode(',', $request->emails));
        
        foreach ($emails as $email) {
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                Notification::route('mail', $email)
                    ->notify(new CustomEmailNotification(
                        $request->title,
                        $request->message,
                        $email
                    ));
            }
        }

        return redirect()->back()->with('success', 'Notifikasi berhasil dikirim ke email yang ditentukan!');
    }
}
