<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

class AuditLogController extends Controller
{
    //
    public function index()
    {
        Auth::loginUsingId(1);
        $logs = AuditLog::with(['user', 'document'])->latest()->paginate(20);
        return view('audit_logs.index', compact('logs'));
    }
}

