<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WebsiteBlock extends Model
{
    use HasFactory;

    protected $fillable = ['block_key', 'block_value','block_type'];
    
}
