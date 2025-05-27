@extends('layouts.app')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Audit Logs</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Document</th>
                        <th>Action</th>
                        <th>Description</th>
                        <th>Time</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-sm bg-primary-subtle rounded-circle me-2">
                                    <span class="avatar-text">{{ substr($log->user ? $log->user->name : 'Unknown', 0, 1) }}</span>
                                </div>
                                {{ $log->user ? $log->user->name : '-' }}
                            </div>
                        </td>
                        <td>
                            @if($log->document)
                                <a href="{{ route('documents.preview', $log->document->id) }}" class="text-decoration-none">
                                    <i class="bi bi-file-text me-1"></i>
                                    {{ $log->document->original_name }}
                                </a>
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            @switch($log->action)
                                @case('upload')
                                    <span class="badge bg-success">Upload</span>
                                    @break
                                @case('preview')
                                    <span class="badge bg-info">Preview</span>
                                    @break
                                @default
                                    <span class="badge bg-secondary">{{ ucfirst($log->action) }}</span>
                            @endswitch
                        </td>
                        <td>{{ $log->description }}</td>
                        <td>
                            <span title="{{ $log->created_at->format('M d, Y H:i:s') }}">
                                {{ $log->created_at->diffForHumans() }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-4">
                            <div class="text-muted">
                                <i class="bi bi-clipboard2-x mb-2" style="font-size: 2rem;"></i>
                                <p class="mb-0">No audit logs found</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($logs->hasPages())
    <div class="card-footer">
        {{ $logs->links() }}
    </div>
    @endif
</div>

<style>
.avatar {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.avatar-text {
    font-size: 14px;
    font-weight: 500;
    color: var(--bs-primary);
}
.bg-primary-subtle {
    background-color: var(--bs-primary-bg-subtle);
}
</style>
@endsection
