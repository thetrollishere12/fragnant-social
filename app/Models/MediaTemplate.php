<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MediaTemplate extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'media_templates';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'title',
        'platform',
        'url',
        'storage',
        'folder',
        'filename',
        'tags',
        'attributes',
        'type'
    ];


    protected $casts = [
        'tags' => 'array',
        'attributes' => 'array',
    ];
    

    /**
     * Get the user associated with this media template.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }




    public function thumbnail()
    {
        return $this->hasOne(MediaTemplateThumbnail::class, 'media_template_id');
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




}
