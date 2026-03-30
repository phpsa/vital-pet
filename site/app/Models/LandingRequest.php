<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LandingRequest extends Model
{
    protected $guarded = [];

    protected $casts = [
        'payload' => 'array',
        'gateway_payload' => 'array',
        'meta' => 'array',
    ];
}