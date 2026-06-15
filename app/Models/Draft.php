<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Draft extends Model
{
    protected $fillable = [
        'rule_id',
        'channel_id',
        'ai_model_id',
        'topic_used',
        'content',
        'content_hash',
        'format',
        'image_path',
        'ai_tokens_used',
        'status',
        'attempts',
        'fail_reason',
    ];

    public function rule()
    {
        return $this->belongsTo(Rule::class);
    }

    public function channel()
    {
        return $this->belongsTo(Channel::class);
    }

    public function aiModel()
    {
        return $this->belongsTo(AiModel::class);
    }

    public function scheduledPost()
    {
        return $this->hasOne(ScheduledPost::class);
    }

    public function publishedPost()
    {
        return $this->hasOne(PublishedPost::class);
    }
}
