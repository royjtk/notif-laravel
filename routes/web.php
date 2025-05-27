<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\AuditLogController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/documents', [DocumentController::class, 'index'])->name('documents.index');
Route::get('/documents/create', [DocumentController::class, 'create'])->name('documents.create');
Route::post('/documents', [DocumentController::class, 'store'])->name('documents.store');
Route::get('/documents/preview/{id}', [DocumentController::class, 'preview'])->name('documents.preview');
Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
