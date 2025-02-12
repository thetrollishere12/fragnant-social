<?php

namespace App\Models\Platform\Account;

use Illuminate\Database\Eloquent\Model;

class EbayAccount extends Model
{
    
    protected $fillable = [
        'account_id',
        'digital_asset_id',
        'name',
        'accountType',
        'registrationMarketplaceId',
        'url',
        'avatar_url',
    ];

    // Relationship with DigitalAsset
    public function digitalAsset()
    {
        return $this->belongsTo(DigitalAsset::class);
    }

}
