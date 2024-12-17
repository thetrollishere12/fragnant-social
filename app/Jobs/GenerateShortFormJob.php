<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;

use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;


use App\Models\UserMedia;
use App\Models\UserMediaSetting;
use App\Models\PublishedMedia;
use App\Models\PublishedMediaThumbnail;

use App\Models\MusicGenre;
use App\Models\User;

use App\Models\DigitalAsset;

use App\Mail\Media\MailMediaLink;

use App\Helper\ShortFormGeneratorHelper;

use App\Helper\FFmpegHelper;

use Log;

use App\Events\MediaPublished;


class GenerateShortFormJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $ffmpegPath;
    protected $ffprobePath;
    protected $videoDuration = 2; // Duration of each trimmed video
    protected $totalVideo = 2;




    protected $digitalAssetId; // Add a property to store the ID

    /**
     * Create a new job instance.
     *
     * @param int $digitalAssetId
     */
    public function __construct($digitalAssetId)
    {
        $this->digitalAssetId = $digitalAssetId; // Assign the ID to the property
    }



    /**
     * Execute the job.
     */
    public function handle()
    {


        $outputPath = ShortFormGeneratorHelper::slideShow($this->digitalAssetId);


        $media = PublishedMedia::create([
            'url' => 'digital-assets/'.$this->digitalAssetId.'/published/'.basename($outputPath),
            'digital_asset_id' => $this->digitalAssetId,
        ]);

        $filePath = 'digital-assets/'.$this->digitalAssetId.'/published';
        $fileName = basename($outputPath);
        $thumbnailFolder = 'digital-assets/'.$this->digitalAssetId.'/published-media-thumbnails';


        $this->generateThumbnail($media, 'public', $filePath, $fileName, $thumbnailFolder);

        $digitalAsset = DigitalAsset::find($this->digitalAssetId);

        $user = User::find($digitalAsset->user_id);

        Log::info("sending Signal For MediaPublished For User - ".$user->id);
        
        event(new MediaPublished($user->id));

        if (!empty($user->email)) {
            $this->sendEmail($user->email, $outputPath);
        }


        


    }

    

    private function sendEmail($email, $outputPath)
    {
        try {
            Mail::to($email)->send(new MailMediaLink($outputPath));
        } catch (\Exception $e) {
            logger()->error("Failed to send email: " . $e->getMessage());
        }
    }




    private function generateThumbnail($media, $storage, $filePath, $fileName, $thumbnailFolder)
    {


        $thumbnailOutputPath = $thumbnailFolder . '/PM_'.$media->id.'_'.\Str::uuid().'/';


        try {

            FFmpegHelper::generateFrames(
                inputPath: $filePath . '/' . $fileName,
                outputPath: $thumbnailOutputPath,
                frameRate: .1,
                width:250
            );

            PublishedMediaThumbnail::create([
                'published_media_id' => $media->id,
                'storage' => $storage,
                'folder' => $thumbnailOutputPath,
                'filename' => 'frame_000000.jpg',
            ]);

            Log::info("Thumbnail generated successfully for video: {$filePath}/{$fileName}");

        } catch (\Exception $e) {

            Log::error("Failed to generate thumbnail: {$e->getMessage()}");

        }








    }



}