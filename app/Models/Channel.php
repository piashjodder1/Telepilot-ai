<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Channel extends Model
{
    protected $fillable = [
        'user_id',
        'account_id',
        'username',
        'chat_id',
        'title',
        'type',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function account()
    {
        return $this->belongsTo(TelegramAccount::class, 'account_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function rules()
    {
        return $this->hasMany(Rule::class);
    }

    public function drafts()
    {
        return $this->hasMany(Draft::class);
    }

    public function scheduledPosts()
    {
        return $this->hasMany(ScheduledPost::class);
    }

    public function publishedPosts()
    {
        return $this->hasMany(PublishedPost::class);
    }
}
