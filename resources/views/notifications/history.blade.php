@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Successful Notifications -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
            <h5 class="mb-0">
                <i class="bi bi-check-circle text-success me-2"></i>
                Notifikasi Terkirim
            </h5>
        </div>
        <div class="card-body">
            @if($successfulNotifications->isEmpty())
                <div class="text-center py-4">
                    <i class="bi bi-inbox text-muted" style="font-size: 2rem;"></i>
                    <p class="text-muted mb-0 mt-2">Belum ada notifikasi yang terkirim.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th scope="col">Tanggal</th>
                                <th scope="col">Judul</th>
                                <th scope="col">Pesan</th>
                                <th scope="col">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($successfulNotifications as $notification)
                            <tr>
                                <td>{{ $notification->created_at->format('d M Y H:i:s') }}</td>
                                <td>{{ $notification->data['title'] ?? '-' }}</td>
                                <td class="text-break">{{ $notification->data['message'] ?? '-' }}</td>
                                <td>
                                    @if($notification->read_at)
                                        <span class="badge bg-success"><i class="bi bi-check2 me-1"></i>Dibaca</span>
                                    @else
                                        <span class="badge bg-warning"><i class="bi bi-clock me-1"></i>Belum Dibaca</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    {{ $successfulNotifications->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Failed Notifications -->
    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
            <h5 class="mb-0">
                <i class="bi bi-exclamation-circle text-danger me-2"></i>
                Notifikasi Gagal Terkirim
            </h5>
            @if(!$failedNotifications->isEmpty())
                <form action="{{ route('notifications.resend-all') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="bi bi-arrow-repeat me-1"></i> Kirim Ulang Semua
                    </button>
                </form>
            @endif
        </div>
        <div class="card-body">
            @if($failedNotifications->isEmpty())
                <div class="text-center py-4">
                    <i class="bi bi-check2-circle text-muted" style="font-size: 2rem;"></i>
                    <p class="text-muted mb-0 mt-2">Tidak ada notifikasi yang gagal terkirim.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th scope="col">Tanggal</th>
                                <th scope="col">Error</th>
                                <th scope="col" width="120">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($failedNotifications as $notification)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($notification->failed_at)->format('d M Y H:i:s') }}</td>
                                <td>
                                    <button type="button" class="btn btn-link text-danger p-0" data-bs-toggle="modal" data-bs-target="#errorModal{{ $notification->id }}">
                                        <i class="bi bi-exclamation-triangle me-1"></i>Lihat Error
                                    </button>
                                    
                                    <!-- Error Modal -->
                                    <div class="modal fade" id="errorModal{{ $notification->id }}" tabindex="-1">
                                        <div class="modal-dialog modal-lg modal-dialog-scrollable">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">
                                                        <i class="bi bi-exclamation-triangle text-danger me-2"></i>
                                                        Detail Error
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <pre class="bg-light p-3 mb-0 rounded"><code class="text-break">{{ $notification->exception }}</code></pre>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <form action="{{ route('notifications.resend', $notification->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-primary btn-sm">
                                            <i class="bi bi-arrow-repeat me-1"></i> Kirim Ulang
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    {{ $failedNotifications->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
