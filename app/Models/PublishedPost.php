<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PublishedPost extends Model
{
    protected $fillable = [
        'scheduled_post_id',
        'channel_id',
        'draft_id',
        'telegram_message_id',
        'content_preview',
        'published_at',
        'views',
        'forwards',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    public function scheduledPost()
    {
        return $this->belongsTo(ScheduledPost::class);
    }

    public function channel()
    {
        return $this->belongsTo(Channel::class);
    }

    public function draft()
    {
        return $this->belongsTo(Draft::class);
    }
}
