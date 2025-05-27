<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobBatch extends Model
{
    protected $table = 'job_batches';
    
    protected $fillable = [
        'name',
        'total_jobs',
        'pending_jobs',
        'failed_jobs',
        'failed_job_ids',
        'options',
        'cancelled_at',
        'created_at',
        'finished_at'
    ];

    public $timestamps = false;
}
