<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserMedia extends Model
{
    use HasFactory;

    protected $fillable = [
        'storage',
        'folder',
        'filename',
        'size',
        'user_id',
        'code_id',
        'type'
    ];

    public function thumbnail()
    {
        return $this->hasOne(MediaThumbnail::class, 'media_id');
    }

    // Method to get the URL of the associated thumbnail
    public function getThumbnailUrlAttribute()
    {
        return $this->thumbnail ? $this->thumbnail->full_url : null;
    }


    /**
     * Get the total storage used by a user (in bytes).
     */
    public static function getTotalStorageUsed(int $userId): int
    {
        // Fetch the total storage used by the user
        return self::where('user_id', $userId)
            ->sum('size'); // Assuming 'size' is stored in bytes
    }
        

}