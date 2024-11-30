<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserMediaSetting extends Model
{
    
    protected $fillable = [
        'user_id',
        'video_type_id',
        'music_genre_id',
        'frequency',
        'frequency_type',
        'quantity',
        'user_audio'
    ];

    protected $casts = [
        'video_type_id' => 'array',
        'music_genre_id' => 'array',
        'user_audio' => 'boolean'
    ];

}
