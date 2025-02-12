<?php

namespace App\Models\Platform\Account;

use Illuminate\Database\Eloquent\Model;

class TiktokAccount extends Model
{
    
    protected $fillable = [
        'digital_asset_id',
        'account_id',
        'display_name',
        'profile_url',
        'avatar_url',
    ];

    public function digitalAsset()
    {
        return $this->belongsTo(DigitalAsset::class);
    }
    
}
