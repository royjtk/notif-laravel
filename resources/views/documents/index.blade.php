@extends('layouts.app')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Document List</h5>
        <a href="{{ route('documents.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg"></i> Upload New Document
        </a>
    </div>
    <div class="card-body">

    <a href="{{ route('documents.create') }}" class="btn btn-primary mb-3">Upload Dokumen Baru</a>

    @if($documents->isEmpty())
        <p class="text-muted">Belum ada dokumen yang diupload.</p>
    @else    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>File Name</th>
                    <th>Size</th>
                    <th>Type</th>
                    <th>Uploaded</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            @foreach($documents as $doc)
            <tr>                <td class="align-middle">
                    <i class="bi {{ $doc->mime_type == 'application/pdf' ? 'bi-file-pdf' : 'bi-file-text' }} text-muted me-2"></i>
                    {{ $doc->original_name }}
                </td>
                <td class="align-middle">{{ number_format($doc->size / 1024, 2) }} KB</td>
                <td class="align-middle">{{ Str::upper(Str::afterLast($doc->mime_type, '/')) }}</td>
                <td class="align-middle">{{ $doc->created_at->diffForHumans() }}</td>
                <td class="align-middle">
                    <div class="btn-group">
                        <a href="{{ asset('storage/' . $doc->filename) }}" target="_blank" class="btn btn-sm btn-outline-primary" title="Download">
                            <i class="bi bi-download"></i>
                        </a>
                        <a href="{{ route('documents.preview', $doc->id) }}" class="btn btn-sm btn-outline-info" target="_blank" title="Preview">
                            <i class="bi bi-eye"></i>
                        </a>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>        </table>
    </div>
    @endif
    </div>
</div>
@endsection
