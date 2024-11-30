<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MusicGenre extends Model
{
    
    protected $fillable = ['name', 'slug', 'description', 'is_active'];
    
}
