<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class MediaThumbnail extends Model
{
    use HasFactory;

    protected $fillable = [
        'media_id',
        'storage',
        'folder',
        'filename'
    ];

    public function userMedia()
    {
        return $this->belongsTo(UserMedia::class, 'media_id');
    }

    // Method to get the full URL of the thumbnail
    public function getFullUrlAttribute()
    {
        return Storage::disk($this->storage)->url($this->folder . '/' . $this->filename);
    }
}