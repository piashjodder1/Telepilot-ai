<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduledPost extends Model
{
    protected $fillable = [
        'draft_id',
        'channel_id',
        'rule_id',
        'scheduled_at',
        'status',
        'attempts',
        'last_attempt_at',
        'fail_reason',
    ];

    protected $casts = [
        'scheduled_at'    => 'datetime',
        'last_attempt_at' => 'datetime',
    ];

    public function draft()
    {
        return $this->belongsTo(Draft::class);
    }

    public function channel()
    {
        return $this->belongsTo(Channel::class);
    }

    public function rule()
    {
        return $this->belongsTo(Rule::class);
    }

    public function publishedPost()
    {
        return $this->hasOne(PublishedPost::class);
    }
}
