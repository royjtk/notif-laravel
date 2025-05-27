<nav class="navbar navbar-expand-lg navbar-light bg-light">
  <div class="container-fluid">
    <a class="navbar-brand" href="{{ url('/') }}">MyApp</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
      aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav">
        <li class="nav-item">
          <a class="nav-link" href="{{ route('documents.index') }}">Dokumen</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="{{ route('documents.create') }}">Upload Dokumen</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="{{ route('audit-logs.index') }}">Audit Log</a>  {{-- Link audit log --}}
        </li>
      </ul>
    </div>
  </div>
</nav>
@auth
    <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#" id="notifDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            Notifikasi <span class="badge bg-danger">{{ auth()->user()->unreadNotifications->count() }}</span>
        </a>
        <ul class="dropdown-menu" aria-labelledby="notifDropdown">
            @forelse(auth()->user()->unreadNotifications as $notification)
                <li><a class="dropdown-item" href="#">{{ $notification->data['message'] ?? 'Dokumen baru diupload' }}</a></li>
            @empty
                <li><span class="dropdown-item">Tidak ada notifikasi</span></li>
            @endforelse
        </ul>
    </li>
@endauth
