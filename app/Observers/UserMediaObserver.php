<?php

namespace App\Observers;

use App\Models\UserMedia;

use Illuminate\Support\Str;


use App\Models\MediaThumbnail;
use App\Models\MediaDetail;


use Storage;

class UserMediaObserver
{






    public function creating(UserMedia $userMedia)
    {
        // Generate a unique code_id using a UUID
        $userMedia->code_id = 'CID-' . Str::uuid();
    }

    /**
     * Handle the UserMedia "created" event.
     */
    public function created(UserMedia $userMedia): void
    {
        //
    }

    /**
     * Handle the UserMedia "updated" event.
     */
    public function updated(UserMedia $userMedia): void
    {
        //
    }

    /**
     * Handle the UserMedia "deleted" event.
     */
    public function deleted(UserMedia $userMedia): void
    {
        //
    }


    public function deleting(UserMedia $userMedia)
    {

        // Delete related MediaThumbnail and its file
        $thumbnail = MediaThumbnail::where('media_id', $userMedia->id)->first();
        if ($thumbnail) {
            Storage::disk($thumbnail->storage)->delete($thumbnail->folder . '/' . $thumbnail->filename);
            $thumbnail->delete();
        }

        // Delete related MediaThumbnail
        MediaThumbnail::where('media_id', $userMedia->id)->delete();

      

        // Delete related MediaDetail
        MediaDetail::where('media_id', $userMedia->id)->delete();


        // Delete the media file for UserMedia
        Storage::disk($userMedia->storage)->delete($userMedia->folder . '/' . $userMedia->filename);

    }



    /**
     * Handle the UserMedia "restored" event.
     */
    public function restored(UserMedia $userMedia): void
    {
        //
    }

    /**
     * Handle the UserMedia "force deleted" event.
     */
    public function forceDeleted(UserMedia $userMedia): void
    {
        //
    }
}
