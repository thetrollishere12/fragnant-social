<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;



class PublishedMedia extends Model
{
    protected $fillable = ['url', 'user_id'];

    /**
     * Get the total published videos for a user within the current month.
     */
    public static function getMonthlyPublishedCount(int $userId): int
    {
        return self::where('user_id', $userId)
            ->where('created_at', '>=', Carbon::now()->startOfMonth())
            ->count();
    }
    
}
