@extends('layouts.app') {{-- Sesuaikan dengan layout yang kamu punya --}}

@section('content')
<div class="container mt-5">
    <h2>Audit Logs</h2>

    <table class="table table-striped">
        <thead>
            <tr>
                <th>User</th>
                <th>Dokumen</th>
                <th>Aksi</th>
                <th>Deskripsi</th>
                <th>Waktu</th>
            </tr>
        </thead>
        <tbody>
            @forelse($logs as $log)
            <tr>
                <td>{{ $log->user ? $log->user->name : '-' }}</td>
                <td>{{ $log->document ? $log->document->original_name : '-' }}</td>
                <td>{{ ucfirst($log->action) }}</td>
                <td>{{ $log->description }}</td>
                <td>{{ $log->created_at->format('d M Y H:i') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="text-center">Tidak ada log.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    {{ $logs->links() }}
</div>
@endsection
