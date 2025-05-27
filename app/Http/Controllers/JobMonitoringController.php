<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Job;
use App\Models\FailedJob;
use App\Models\JobBatch;
use Illuminate\Support\Facades\DB;

class JobMonitoringController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $pendingJobs = Job::orderBy('created_at', 'desc')->take(100)->get();
        $failedJobs = FailedJob::orderBy('failed_at', 'desc')->take(100)->get();
        $batches = JobBatch::orderBy('created_at', 'desc')->take(100)->get();
        
        $queueSize = DB::table('jobs')->count();
        $failedCount = DB::table('failed_jobs')->count();
        $batchCount = DB::table('job_batches')->count();

        return view('jobs.monitor', compact(
            'pendingJobs', 
            'failedJobs', 
            'batches',
            'queueSize',
            'failedCount',
            'batchCount'
        ));
    }

    public function retry($id)
    {
        try {
            \Artisan::call('queue:retry', ['id' => $id]);
            return back()->with('success', 'Job has been queued for retry');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to retry job: ' . $e->getMessage());
        }
    }

    public function forget($id)
    {
        try {
            \Artisan::call('queue:forget', ['id' => $id]);
            return back()->with('success', 'Failed job has been deleted');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete job: ' . $e->getMessage());
        }
    }

    public function flush()
    {
        try {
            \Artisan::call('queue:flush');
            return back()->with('success', 'All failed jobs have been deleted');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to flush jobs: ' . $e->getMessage());
        }
    }
}
