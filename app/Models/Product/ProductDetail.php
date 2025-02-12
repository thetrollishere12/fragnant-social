<?php

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Model;

class ProductDetail extends Model
{
    protected $fillable = ['product_id', 'name', 'description', 'price','sale_price','tags'];
}
