<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Storage;

class MediaTemplateThumbnail extends Model
{
    

    use HasFactory;

    protected $fillable = [
        'media_template_id',
        'storage',
        'folder',
        'filename'
    ];

    public function publishedMedia()
    {
        return $this->belongsTo(MediaTemplate::class, 'media_template_id');
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
