<?php

namespace App\Models\Platform\Account;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class YoutubeChannel extends Model
{


    use HasFactory;

    protected $fillable = [
        'digital_asset_id', 'channel_id', 'channel_name', 'channel_url', 'channel_image', // Add 'channel_image' here
    ];

    /**
     * Get the user that owns the YouTube channel.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    
}
