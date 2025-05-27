<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\UserNotificationController;
use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\HomeController;

// Authentication Routes
Auth::routes();

// Guest Routes
Route::get('/', function () {
    return redirect()->route('login');
});

// Authenticated Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/home', [HomeController::class, 'index'])->name('home');
    
    // Document Routes
    Route::get('/documents', [DocumentController::class, 'index'])->name('documents.index');
    Route::get('/documents/create', [DocumentController::class, 'create'])->name('documents.create');
    Route::post('/documents', [DocumentController::class, 'store'])->name('documents.store');
    Route::get('/documents/preview/{id}', [DocumentController::class, 'preview'])->name('documents.preview');
    
    // Audit Log Routes
    Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');

    // Notification Settings Routes
    Route::get('/notifications/settings', [UserNotificationController::class, 'edit'])->name('user.notifications.edit');
    Route::patch('/notifications/settings', [UserNotificationController::class, 'update'])->name('users.update-notifications');
    
    // Custom Notification Routes
    Route::get('/notifications/create', [NotificationController::class, 'create'])->name('notifications.create');
    Route::post('/notifications/send', [NotificationController::class, 'send'])->name('notifications.send');
    Route::get('/notifications/custom', [NotificationController::class, 'createCustom'])->name('notifications.create-custom');
    Route::post('/notifications/send-custom', [NotificationController::class, 'sendCustom'])->name('notifications.send-custom');
});
