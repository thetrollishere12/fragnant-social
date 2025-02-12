<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlatformToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'digital_asset_id',
        'platform',
        'platform_id',
        'access_token',
        'refresh_token',
        'scopes',
        'attributes',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'attributes' => 'array'
    ];

}
