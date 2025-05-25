<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Document;
use Illuminate\Support\Facades\Storage;

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

        return redirect()->route('documents.index')->with('success', 'Dokumen berhasil diupload!');
    }

    public function preview($id)
    {
        $document = Document::findOrFail($id);
        $fileUrl = asset('storage/' . $document->filename);

        return view('documents.preview', compact('document', 'fileUrl'));
    }

}
