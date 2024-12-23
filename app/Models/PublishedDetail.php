<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PublishedDetail extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'published_details';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'published_id',
        'media_template_id',
        'type',
    ];

    /**
     * Get the published content associated with this detail.
     */
    public function published()
    {
        return $this->belongsTo(Published::class, 'published_id');
    }

    /**
     * Get the media template associated with this published detail.
     */
    public function mediaTemplate()
    {
        return $this->belongsTo(MediaTemplate::class, 'media_template_id');
    }
}
