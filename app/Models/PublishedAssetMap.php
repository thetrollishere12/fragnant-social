<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PublishedAssetMap extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'published_asset_maps';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'published_id',
        'user_media_id',
        'attributes',
        'weight',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'attributes' => 'array',
    ];

    /**
     * Get the published content associated with this asset map.
     */
    public function published()
    {
        return $this->belongsTo(Published::class, 'published_id');
    }

    /**
     * Get the user media associated with this asset map.
     */
    public function userMedia()
    {
        return $this->belongsTo(UserMedia::class, 'user_media_id');
    }
}
