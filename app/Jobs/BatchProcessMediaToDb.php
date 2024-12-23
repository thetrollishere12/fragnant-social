<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

use App\Models\UserMedia;
use App\Models\DigitalAsset;

use App\Events\MediaProcessed;



use App\Jobs\ProcessMedia;


use App\Helper\SubscriptionHelper;
use App\Events\Subscription\SubscriptionStatus;



class BatchProcessMediaToDb implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;



    protected $arrayPath;
    protected $digital_asset_id;

    public function __construct($arrayPath, $digitalAssetId)
    {
        $this->arrayPath = $arrayPath;
        $this->digital_asset_id = $digitalAssetId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {

        $digitalAsset = DigitalAsset::find($this->digital_asset_id);

        foreach ($this->arrayPath as $key => $value) {

            if(SubscriptionHelper::hasExceededStorageLimit($digitalAsset->user_id) == true){

                event(new SubscriptionStatus($digitalAsset->user_id,'Surpassed'));
                break;
                
            }else{

                $userMedia = UserMedia::create([
                    'storage' => 'local',
                    'folder' => 'temp',
                    'filename' => $value['originalName'],
                    'size' => Storage::disk('local')->size($value['temporary_path']),
                    'digital_asset_id' => $this->digital_asset_id,
                    'type' => 'pending', // Mark as pending
                ]);

                $digitalAsset = DigitalAsset::find($this->digital_asset_id);

                event(new MediaProcessed($digitalAsset->user_id));

                // Dispatch the processing job
                ProcessMedia::dispatch($value['temporary_path'], $userMedia->id);

            }

            

        }



            


    }
}
