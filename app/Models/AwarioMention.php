<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AwarioMention extends Model
{
    protected $guarded = [];

    protected $casts = [
        'raw' => 'array',
        'mentioned_at' => 'datetime',
    ];
}