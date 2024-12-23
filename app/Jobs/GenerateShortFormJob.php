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

use App\Events\Publish\PublishProcessing;


use App\Helper\SubscriptionHelper;
use App\Events\Subscription\SubscriptionStatus;


use Illuminate\Support\Arr;


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
        try {


            // Retrieve Digital Asset
            $digitalAsset = DigitalAsset::find($this->digitalAssetId);

            if (!$digitalAsset) {
                Log::error("Digital Asset not found: {$this->digitalAssetId}");
                $this->triggerFailureEvent($this->digitalAssetId, 'Digital Asset not found.');
                return;
            }

            // Retrieve the User
            $user = User::find($digitalAsset->user_id);

            if (!$user) {
                Log::error("User not found for Digital Asset: {$this->digitalAssetId}");
                $this->triggerFailureEvent($this->digitalAssetId, 'User not found.');
                return;
            }

            // Checking Subscription
            event(new PublishProcessing(
                $user->id,
                'Checking',
                'Checking if user has not surpassed plan.',
                5
            ));

            if (SubscriptionHelper::hasExceededMonthlyVideoLimit($user->id)) {
                event(new SubscriptionStatus($digitalAsset->user_id, 'Surpassed'));
                $this->triggerFailureEvent($this->digitalAssetId, 'Surpassed Subscription.');
                return;
            }

            // Starting Processing
            event(new PublishProcessing(
                $user->id,
                'Starting',
                'Generating has started.',
                10
            ));




            $videoTypeId = is_array($digitalAsset->setting->video_type_id)
            ? Arr::random($digitalAsset->setting->video_type_id)
            : $digitalAsset->setting->video_type_id; // Fallback if not an array


            // Process Media
            $media = ShortFormGeneratorHelper::$videoTypeId($this->digitalAssetId);

            event(new PublishProcessing(
                $user->id,
                'Creating',
                'Media creating is done and now being recorded.',
                40
            ));


            event(new PublishProcessing(
                $user->id,
                'Created',
                'Media has been created and recorded.',
                50
            ));

            

            event(new PublishProcessing(
                $user->id,
                'Thumbnail',
                'Thumbnail creation is in progress.',
                75
            ));


            // Generate Thumbnail
            $this->generateThumbnail($media, 'public');

            event(new PublishProcessing(
                $user->id,
                'CompletedThumbnail',
                'Thumbnail creation is completed.',
                85
            ));

            // Log and Complete Job
            Log::info("Signal Media Published for User - {$user->id}");

            event(new PublishProcessing(
                $user->id,
                'Completed',
                'Media processing has been completed!',
                100
            ));

            // Send Email
            if (!empty($user->email)) {
                $this->sendEmail($user->email, $media->url);
            }
        } catch (\Exception $e) {
            // Log the exception and trigger failure event
            Log::error("Media processing failed for User - ".$digitalAsset->user_id);

            $this->triggerFailureEvent($digitalAsset->user_id ?? null, $e->getMessage());

        }
    }

    /**
     * Trigger a failure event with a message.
     *
     * @param int|null $userId
     * @param string $errorMessage
     */
    private function triggerFailureEvent($userId, $errorMessage)
    {
        if ($userId) {
            event(new PublishProcessing(
                $userId,
                'Failed',
                $errorMessage,
                100
            ));
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




    private function generateThumbnail($media, $storage)
    {


        // Thumbnail Processing

        $thumbnailOutputPath = 'digital-assets/' . $media->published_asset_id . '/published-media-thumbnails/'.'/PM_'.$media->id.'_'.\Str::uuid().'/';


        try {

            FFmpegHelper::generateFrames(
                inputPath: $media->url,
                outputPath: $thumbnailOutputPath,
                frameRate: 1,
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