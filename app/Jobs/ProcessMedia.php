<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use App\Models\UserMedia;
use App\Models\MediaThumbnail;
use App\Helper\FFmpegHelper;
use Log;


use App\Events\MediaProcessed;



class ProcessMedia implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $tempPath;
    protected $userId;

    /**
     * Create a new job instance.
     *
     * @param string $tempPath
     * @param int $userId
     */
    public function __construct($tempPath, $userId)
    {
        $this->tempPath = $tempPath;
        $this->userId = $userId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $storage = 'public';
        $filePath = 'media';
        $thumbnailFolder = 'thumbnails';

        // Move the file to final destination
        $originalName = basename($this->tempPath);
        $fileName = 'MD-' . \Str::uuid() . '.' . pathinfo($originalName, PATHINFO_EXTENSION);

        $finalPath = Storage::disk($storage)->putFileAs(
            $filePath,
            Storage::disk('local')->path($this->tempPath),
            $fileName
        );

        // Determine file type
        $mimeType = Storage::disk($storage)->mimeType($filePath . '/' . $fileName);

    

        // Clean up the temporary file
        Storage::disk('local')->delete($this->tempPath);






        // if ($type === 'video') {

        //     $inputPath = $filePath . '/' . basename($path);
        //     $compressedName = 'C-' . basename($path);
        //     $compressedOutputPath = $filePath . '/' . $compressedName;

        //     FFmpegHelper::compressVideo($inputPath, $compressedOutputPath, 1000);
        //     Storage::disk($storage)->delete($inputPath);

        //     $fileName = $compressedName;

        // } elseif ($type === 'image') {

        //     $inputPath = $filePath . '/' . basename($path);
        //     $compressedName = 'C-' . basename($path);
        //     $compressedOutputPath = $filePath . '/' . $compressedName;

        //     FFmpegHelper::compressImage($inputPath, $compressedOutputPath, 75);
        //     Storage::disk($storage)->delete($inputPath);

        //     $fileName = $compressedName;

        // }






        // Determine the type of media based on MIME type
        $type = match (true) {
            str_contains($mimeType, 'video/') => 'video',
            str_contains($mimeType, 'image/') => 'image',
            str_contains($mimeType, 'audio/') => 'audio',
            default => 'other',
        };

        // Save media details to the database
        $media = UserMedia::create([
            'storage' => $storage,
            'folder' => $filePath,
            'filename' => $fileName,
            'size' => Storage::disk($storage)->size($filePath . '/' . $fileName),
            'user_id' => $this->userId,
            'type' => $type, // Save the detected type
        ]);

        if (str_contains($mimeType, 'video/')) {
            // Generate a thumbnail for the video
            $thumbnailFilename = 'TH-' . \Str::uuid() . '.jpg';
            $thumbnailOutputPath = $thumbnailFolder . '/' . $thumbnailFilename;

            // Ensure thumbnail folder exists
            Storage::disk($storage)->makeDirectory($thumbnailFolder);

            // Call the generateThumbnail function
            try {
                FFmpegHelper::generateThumbnail(
                    $filePath . '/' . $fileName, // Relative input path
                    $thumbnailOutputPath, // Relative output path
                    1, // Time in seconds
                    300 // Width
                );

                // Save thumbnail details to database
                MediaThumbnail::create([
                    'media_id' => $media->id, // Update with actual media ID if available
                    'storage' => $storage,
                    'folder' => $thumbnailFolder,
                    'filename' => $thumbnailFilename,
                ]);

                Log::info("Thumbnail generated successfully for video: {$filePath}/{$fileName}");

                // Emit event after processing
                event(new MediaProcessed($this->userId));

            } catch (\Exception $e) {
                Log::error("Failed to generate thumbnail for video: {$filePath}/{$fileName}", [
                    'exception' => $e->getMessage(),
                ]);
                throw $e;
            }
        }





    }
}