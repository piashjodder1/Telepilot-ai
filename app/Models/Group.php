<?php

namespace App\Models;

class Group extends Channel
{
    protected $table = 'channels';

    protected static function booted()
    {
        parent::booted();
        
        static::addGlobalScope('group', function ($query) {
            $query->whereIn('type', ['group', 'supergroup']);
        });
    }
}
