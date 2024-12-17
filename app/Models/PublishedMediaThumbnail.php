<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;


class PublishedMediaThumbnail extends Model
{
    
    use HasFactory;

    protected $fillable = [
        'published_media_id',
        'storage',
        'folder',
        'filename'
    ];

    public function publishedMedia()
    {
        return $this->belongsTo(PublishedMedia::class, 'published_media_id');
    }

    // Method to get the full URL of the thumbnail
    public function getFullUrlAttribute()
    {
        return Storage::disk($this->storage)->url($this->folder . '/' . $this->filename);
    }

    // Method to get the full URL of the thumbnail
    public function getFullUrlAllFilesAttribute()
    {
        return Storage::disk($this->storage)->allFiles($this->folder);
    }

}
