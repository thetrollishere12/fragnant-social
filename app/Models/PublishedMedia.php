<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;



class PublishedMedia extends Model
{
    protected $fillable = ['url','digital_asset_id'];

    /**
     * Get the total published videos for a user within the current month.
     */
    public static function getMonthlyPublishedCount(int $userId): int
    {
        return self::where('user_id', $userId)
            ->where('created_at', '>=', Carbon::now()->startOfMonth())
            ->count();
    }


    public function thumbnail()
    {
        return $this->hasOne(PublishedMediaThumbnail::class, 'published_media_id');
    }

    // Method to get the URL of the associated thumbnail
    public function getThumbnailUrlAttribute()
    {
        return $this->thumbnail ? $this->thumbnail->full_url : null;
    }


    // Method to get the URL of the associated thumbnail
    public function getThumbnailUrlAllFilesAttribute()
    {
        return $this->thumbnail ? $this->thumbnail->full_url_all_files : null;
    }


    // Define the relationship with DigitalAsset
    public function digitalAsset()
    {
        return $this->belongsTo(DigitalAsset::class, 'digital_asset_id');
    }



    /**
     * Relationship with PublishedDetail.
     */
    public function details()
    {
        return $this->hasOne(PublishedDetail::class, 'published_id');
    }

    /**
     * Relationship with PublishedAssetMap.
     */
    public function assetMaps()
    {
        return $this->hasMany(PublishedAssetMap::class, 'published_id');
    }
    

    
}
