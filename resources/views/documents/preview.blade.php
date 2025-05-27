<!DOCTYPE html>
<html>
<head>
    <title>Preview Dokumen - {{ $document->original_name }}</title>
    <style>
        body, html { margin: 0; padding: 0; height: 100%; }
        iframe { width: 100%; height: 100vh; border: none; }
        img { max-width: 100%; height: auto; display: block; margin: auto; max-height: 100vh; }
    </style>
</head>
<body>
    @php
        $ext = pathinfo($document->original_name, PATHINFO_EXTENSION);
    @endphp

    @if(in_array(strtolower($ext), ['pdf']))
        <iframe src="{{ $fileUrl }}"></iframe>
    @elseif(in_array(strtolower($ext), ['jpg','jpeg','png','gif','bmp']))
        <img src="{{ $fileUrl }}" alt="{{ $document->original_name }}" />
    @else
        <p>Preview tidak tersedia untuk tipe file ini.</p>
        <a href="{{ $fileUrl }}" target="_blank">Download file</a>
    @endif
</body>
</html>
