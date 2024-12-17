<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserMediaSetting extends Model
{
    
    protected $fillable = [
        'video_type_id',
        'music_genre_id',
        'frequency',
        'frequency_type',
        'quantity',
        'user_audio',
        'digital_asset_id'
    ];

    protected $casts = [
        'video_type_id' => 'array',
        'music_genre_id' => 'array',
        'user_audio' => 'boolean'
    ];

}
