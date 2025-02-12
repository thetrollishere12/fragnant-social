<?php

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Model;

class ProductImportFeed extends Model
{

    protected $fillable = [
        'digital_asset_id',
        'name',
        'file_type',
        'url',
    ];
    
    public function products()
    {
        return $this->hasMany(Product::class, 'platform_id', 'id')
                    ->where('platform', 'Import Feed');
    }

}
