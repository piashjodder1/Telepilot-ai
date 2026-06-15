<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HeartbeatLog extends Model
{
    protected $fillable = [
        'ticked_at',
        'rules_checked',
        'jobs_dispatched',
        'posts_published',
        'errors',
        'duration_ms',
    ];

    protected $casts = [
        'ticked_at' => 'datetime',
    ];
}
