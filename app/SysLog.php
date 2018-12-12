<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SysLog extends Model
{
    //
    protected $fillable = [
        'description',
        'context',
        'origin',
        'type',
        'result',
        'level',
        'token',
        'ip',
        'user_agent',
        'session',
    ];

    protected $casts = [
        'context' => 'array',
    ];
}
