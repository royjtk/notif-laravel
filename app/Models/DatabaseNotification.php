<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\DatabaseNotification as BaseDatabaseNotification;

class DatabaseNotification extends BaseDatabaseNotification
{
    protected $table = 'notifications';
    
    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
    ];

    public function notifiable()
    {
        return $this->morphTo();
    }
}
