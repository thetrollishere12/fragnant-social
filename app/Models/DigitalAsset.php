<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DigitalAsset extends Model
{
    
    protected $fillable = [
        'user_id',
        'name',
        'image',
        'description',
    ];


    // Define the relationship with DigitalAsset
    public function setting()
    {
        return $this->hasOne(UserMediaSetting::class, 'digital_asset_id');
    }

}
