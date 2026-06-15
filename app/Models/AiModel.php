<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiModel extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'provider',
        'model',
        'api_key',
        'base_url',
        'temperature',
        'max_tokens',
        'timeout_seconds',
        'retry_attempts',
        'is_default',
        'is_active',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_active'  => 'boolean',
        'api_key'    => 'encrypted',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function rules()
    {
        return $this->hasMany(Rule::class);
    }
}
