<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Topic extends Model
{
    protected $fillable = [
        'rule_id',
        'topic',
        'last_used_at',
        'use_count',
    ];

    protected $casts = [
        'last_used_at' => 'datetime',
    ];

    public function rule()
    {
        return $this->belongsTo(Rule::class);
    }
}
