<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserNotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function edit()
    {
        return view('users.notification-settings');
    }

    public function update(Request $request)
    {
        $user = $request->user();
        
        $user->update([
            'notify_on_document_upload' => $request->has('notify_on_document_upload'),
            'notify_on_document_update' => $request->has('notify_on_document_update'),
            'notify_on_document_delete' => $request->has('notify_on_document_delete')
        ]);

        return redirect()
            ->route('user.notifications.edit')
            ->with('status', 'Notification preferences updated successfully!');
    }
}
