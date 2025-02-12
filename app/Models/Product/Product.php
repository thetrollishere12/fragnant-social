<?php

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{

    
    protected $fillable = ['digital_asset_id', 'code_id', 'platform', 'platform_id'];

    /**
     * Relationship: Product has one ProductDetail
     */
    public function detail()
    {
        return $this->hasOne(ProductDetail::class, 'product_id', 'id');
    }

    /**
     * Relationship: Product has many ProductMedia
     */
    public function media()
    {
        return $this->hasMany(ProductMedia::class, 'product_id', 'id');
    }

}
