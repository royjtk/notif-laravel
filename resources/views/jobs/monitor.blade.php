@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Queue Size</h5>
                    <h2>{{ $queueSize }}</h2>
                    <p class="text-muted mb-0">Pending Jobs</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Failed Jobs</h5>
                    <h2>{{ $failedCount }}</h2>
                    <p class="text-muted mb-0">Total Failed</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Job Batches</h5>
                    <h2>{{ $batchCount }}</h2>
                    <p class="text-muted mb-0">Active Batches</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Jobs -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Pending Jobs</h5>
        </div>
        <div class="card-body">
            @if($pendingJobs->isEmpty())
                <p class="text-muted mb-0">No pending jobs found.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Queue</th>
                                <th>Attempts</th>
                                <th>Created</th>
                                <th>Available At</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pendingJobs as $job)
                            <tr>
                                <td>{{ $job->id }}</td>
                                <td>{{ $job->queue }}</td>
                                <td>{{ $job->attempts }}</td>
                                <td>{{ date('Y-m-d H:i:s', $job->created_at) }}</td>
                                <td>{{ date('Y-m-d H:i:s', $job->available_at) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <!-- Failed Jobs -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Failed Jobs</h5>
            <div>
                @if($failedCount > 0)
                    <form action="{{ route('jobs.flush') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete all failed jobs?')">
                            Clear All Failed Jobs
                        </button>
                    </form>
                @endif
            </div>
        </div>
        <div class="card-body">
            @if($failedJobs->isEmpty())
                <p class="text-muted mb-0">No failed jobs found.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>UUID</th>
                                <th>Queue</th>
                                <th>Failed At</th>
                                <th>Exception</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($failedJobs as $job)
                            <tr>
                                <td>{{ $job->uuid }}</td>
                                <td>{{ $job->queue }}</td>
                                <td>{{ $job->failed_at }}</td>
                                <td>
                                    <button type="button" class="btn btn-link p-0" data-bs-toggle="modal" data-bs-target="#errorModal{{ $job->id }}">
                                        View Error
                                    </button>
                                    
                                    <!-- Error Modal -->
                                    <div class="modal fade" id="errorModal{{ $job->id }}" tabindex="-1">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Error Details</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <pre class="bg-light p-3 mb-0"><code>{{ $job->exception }}</code></pre>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <form action="{{ route('jobs.retry', $job->uuid) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-primary btn-sm">Retry</button>
                                    </form>
                                    <form action="{{ route('jobs.forget', $job->uuid) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <!-- Job Batches -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Job Batches</h5>
        </div>
        <div class="card-body">
            @if($batches->isEmpty())
                <p class="text-muted mb-0">No job batches found.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Total Jobs</th>
                                <th>Pending</th>
                                <th>Failed</th>
                                <th>Created</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($batches as $batch)
                            <tr>
                                <td>{{ $batch->id }}</td>
                                <td>{{ $batch->name }}</td>
                                <td>{{ $batch->total_jobs }}</td>
                                <td>{{ $batch->pending_jobs }}</td>
                                <td>{{ $batch->failed_jobs }}</td>
                                <td>{{ date('Y-m-d H:i:s', $batch->created_at) }}</td>
                                <td>
                                    @if($batch->cancelled_at)
                                        <span class="badge bg-danger">Cancelled</span>
                                    @elseif($batch->finished_at)
                                        <span class="badge bg-success">Completed</span>
                                    @elseif($batch->failed_jobs > 0)
                                        <span class="badge bg-warning">Has Failures</span>
                                    @else
                                        <span class="badge bg-info">In Progress</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
