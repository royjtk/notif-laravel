<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Notifications\CustomNotification;

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
}
