<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rule extends Model
{
    protected $fillable = [
        'user_id',
        'channel_id',
        'ai_model_id',
        'name',
        'topic',
        'tone',
        'language',
        'format',
        'frequency',
        'custom_minutes',
        'active_from',
        'active_until',
        'max_per_day',
        'timezone',
        'days_active',
        'is_active',
        'last_run_at',
        'next_run_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'active_from' => 'datetime:H:i',
        'active_until' => 'datetime:H:i',
        'last_run_at' => 'datetime',
        'next_run_at' => 'datetime',
        'days_active' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function channel()
    {
        return $this->belongsTo(Channel::class);
    }

    public function aiModel()
    {
        return $this->belongsTo(AiModel::class);
    }

    public function topics()
    {
        return $this->hasMany(Topic::class);
    }
}
