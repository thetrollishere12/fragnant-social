<?php

namespace App\Models\SocialMedia;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InstagramToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'platform',
        'platform_id',
        'digital_asset_id',
        'access_token',
        'refresh_token',
        'expires_at',
        'scopes', // Add this line
    ];

    protected $casts = [
        'scopes'=>'array'
    ];

    protected $dates = ['expires_at'];

    
}


