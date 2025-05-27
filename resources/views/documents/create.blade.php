@extends('layouts.app')

@section('title', 'Upload Dokumen Baru')

@section('content')
<div class="container mt-5">
    <h2 class="mb-4">Upload Dokumen Baru</h2>

    @if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form action="{{ route('documents.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="mb-3">
            <label for="document" class="form-label">Pilih file dokumen</label>
            <input type="file" class="form-control" id="document" name="document" required />
        </div>
        <button type="submit" class="btn btn-primary">Upload</button>
        <a href="{{ route('documents.index') }}" class="btn btn-secondary ms-2">Kembali ke Daftar</a>
    </form>
</div>
@endsection
