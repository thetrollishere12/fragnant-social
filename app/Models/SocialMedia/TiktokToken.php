<?php

namespace App\Models\SocialMedia;

use Illuminate\Database\Eloquent\Model;

class TiktokToken extends Model
{
    
    protected $fillable = [
        'digital_asset_id',
        'platform_id',
        'platform',
        'access_token',
        'refresh_token',
        'expires_at',
        'scopes'
    ];

    protected $casts = [
        'scopes'=>'array'
    ];

    protected $dates = ['expires_at'];
    
    public function digitalAsset()
    {
        return $this->belongsTo(DigitalAsset::class);
    }

}
