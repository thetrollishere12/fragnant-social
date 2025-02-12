<?php

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Model;

class ProductMedia extends Model
{
    protected $fillable = ['product_id', 'url', 'transparent'];

    protected $casts = [
        'transparent'=>'boolean'
    ];

}
