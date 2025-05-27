@extends('layouts.app')

@section('content')
<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0">{{ $document->original_name }}</h5>
            <div class="btn-group">
                <a href="{{ route('documents.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left"></i> Back to List
                </a>
                <a href="{{ $fileUrl }}" download class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-download"></i> Download
                </a>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="embed-responsive embed-responsive-16by9" style="height: 75vh;">
            @php
                $ext = pathinfo($document->original_name, PATHINFO_EXTENSION);
            @endphp            @if(Str::contains($document->mime_type, 'pdf'))
                <iframe src="{{ $fileUrl }}" class="w-100 h-100" style="border: none;"></iframe>
            @elseif(Str::contains($document->mime_type, ['image/', 'jpeg', 'png', 'gif']))
                <div class="d-flex align-items-center justify-content-center h-100 bg-light">
                    <img src="{{ $fileUrl }}" alt="{{ $document->original_name }}" class="img-fluid" style="max-height: 75vh;">
                </div>
            @elseif(Str::contains($document->mime_type, ['word', 'document']))
                <div class="d-flex align-items-center justify-content-center bg-light p-5">
                    <div class="text-center">
                        <i class="bi bi-file-word text-primary" style="font-size: 4rem;"></i>
                        <p class="mt-3">
                            This is a Microsoft Word document.<br>
                            Please download to view its contents.
                        </p>
                        <a href="{{ $fileUrl }}" download class="btn btn-primary">
                            <i class="bi bi-download"></i> Download Document
                        </a>
                    </div>
                </div>
            @else
                <div class="d-flex align-items-center justify-content-center bg-light p-5">
                    <div class="text-center">
                        <i class="bi bi-file-text text-muted" style="font-size: 4rem;"></i>
                        <p class="mt-3">
                            Preview not available for this file type.<br>
                            Please download to view its contents.
                        </p>
                        <a href="{{ $fileUrl }}" download class="btn btn-primary">
                            <i class="bi bi-download"></i> Download File
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
    <div class="card-footer bg-light">
        <div class="row text-muted small">
            <div class="col-sm-4">
                <i class="bi bi-file-earmark me-1"></i> Type: {{ Str::upper(Str::afterLast($document->mime_type, '/')) }}
            </div>
            <div class="col-sm-4">
                <i class="bi bi-harddrive me-1"></i> Size: {{ number_format($document->size / 1024, 2) }} KB
            </div>
            <div class="col-sm-4">
                <i class="bi bi-clock me-1"></i> Uploaded: {{ $document->created_at->diffForHumans() }}
            </div>
        </div>
    </div>
</div>
@endsection
