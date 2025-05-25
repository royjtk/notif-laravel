<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Daftar Dokumen</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
<div class="container mt-5">
    <h2 class="mb-4">Daftar Dokumen</h2>

    @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <a href="{{ route('documents.create') }}" class="btn btn-primary mb-3">Upload Dokumen Baru</a>

    @if($documents->isEmpty())
        <p class="text-muted">Belum ada dokumen yang diupload.</p>
    @else
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Nama File</th>
                <th>Ukuran (KB)</th>
                <th>Jenis File</th>
                <th>Upload Pada</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($documents as $doc)
            <tr>
                <td>{{ $doc->original_name }}</td>
                <td>{{ number_format($doc->size / 1024, 2) }}</td>
                <td>{{ $doc->mime_type }}</td>
                <td>{{ $doc->created_at->format('d M Y H:i') }}</td>
                <td>
                    <a href="{{ asset('storage/' . $doc->filename) }}" target="_blank" class="btn btn-sm btn-info">Lihat</a>
                    <a href="{{ route('documents.preview', $doc->id) }}" class="btn btn-sm btn-success" target="_blank">Preview</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
