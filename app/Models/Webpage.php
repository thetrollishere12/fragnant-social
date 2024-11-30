<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Screen\AsSource;
class Webpage extends Model
{
    use HasFactory,AsSource;

    protected $fillable = [
        'uri',
        'name',
        'title',
        'description',
        'indexable'
    ];

    protected $casts = [
        'indexable' => 'boolean'
    ];


}
