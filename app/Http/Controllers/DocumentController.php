<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Document;
use Illuminate\Support\Facades\Storage;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

use App\Notifications\DocumentUploaded;
use Illuminate\Support\Facades\Notification;


class DocumentController extends Controller
{
    public function index()
    {
        $documents = Document::all();
        return view('documents.index', compact('documents'));
    }

    public function create()
    {
        return view('documents.create');
    }

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function store(Request $request)
    {
        $request->validate([
            'document' => 'required|file|max:20000', // max 20MB
        ]);

        $file = $request->file('document');

        // Simpan file ke storage/app/public/documents
        $path = $file->store('documents', 'public');

        // Simpan info ke DB
        $document = new Document();
        $document->filename = $path;
        $document->original_name = $file->getClientOriginalName();
        $document->mime_type = $file->getMimeType();
        $document->size = $file->getSize();
        $document->save();

        // Simpan audit log
        AuditLog::create([
            'user_id' => Auth::id(),
            'document_id' => $document->id,
            'action' => 'upload',
            'description' => 'User mengupload dokumen ' . $document->original_name,
        ]);

        $user = Auth::user();
        if ($user) {
            $user->notify(new DocumentUploaded($document));
        }

        return redirect()->route('documents.index')->with('success', 'Dokumen berhasil diupload!');
    }

    public function preview($id)
    {
        $document = Document::findOrFail($id);
        $fileUrl = asset('storage/' . $document->filename);

        // Log preview
        AuditLog::create([
            'user_id' => Auth::id(),
            'document_id' => $document->id,
            'action' => 'preview',
            'description' => Auth::user()->name . ' melihat preview dokumen ' . $document->original_name,
        ]);

        // Notifikasi
        // $user = Auth::user();
        // if ($user) {
        //     $user->notify(new DocumentUploaded($document));
        // }

        return view('documents.preview', compact('document', 'fileUrl'));
    }

}
