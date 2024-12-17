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

}
