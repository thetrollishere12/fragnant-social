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

use App\Models\DigitalAsset;

class ProcessMedia implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $tempPath;
    protected $mediaId;

    public function __construct($tempPath, $mediaId)
    {
        $this->tempPath = $tempPath;
        $this->mediaId = $mediaId;
    }

    public function handle()
    {



     $media = UserMedia::find($this->mediaId);

        if (!$media) {
            Log::error("Media not found for ID: {$media->id}");
            return;
        }

        $storage = 'public';
        $filePath = 'digital-assets/'.$media->digital_asset_id.'/media';
        $thumbnailFolder = 'digital-assets/'.$media->digital_asset_id.'/media-thumbnails';

        $originalName = basename($this->tempPath);
        $fileName = 'MD-' . \Str::uuid() . '.' . pathinfo($originalName, PATHINFO_EXTENSION);

        // Move the file to the final destination
        $finalPath = Storage::disk($storage)->putFileAs(
            $filePath,
            Storage::disk('local')->path($this->tempPath),
            $fileName
        );

        $mimeType = Storage::disk($storage)->mimeType($filePath . '/' . $fileName);

        // Determine media type
        $type = match (true) {
            str_contains($mimeType, 'video/') => 'video',
            str_contains($mimeType, 'image/') => 'image',
            str_contains($mimeType, 'audio/') => 'audio',
            default => 'other',
        };

        // Update media metadata
        $media->update([
            'storage' => $storage,
            'folder' => $filePath,
            'filename' => $fileName,
            'type' => $type,
        ]);

        if ($type === 'video') {
            $this->generateThumbnail($media, $storage, $filePath, $fileName, $thumbnailFolder);
        }

        // Clean up temporary file
        Storage::disk('local')->delete($this->tempPath);

        $digitalAsset = DigitalAsset::find($media->digital_asset_id);

        Log::info("sending Signal For Media Processed For User - ".$digitalAsset->user_id);

        event(new MediaProcessed($digitalAsset->user_id));


    }

    private function generateThumbnail($media, $storage, $filePath, $fileName, $thumbnailFolder)
    {
        $thumbnailFilename = 'TH-' . \Str::uuid() . '.jpg';
        $thumbnailOutputPath = $thumbnailFolder . '/' . $thumbnailFilename;

        // Ensure thumbnail folder exists
        Storage::disk($storage)->makeDirectory($thumbnailFolder);

        try {
            FFmpegHelper::generateThumbnail(
                $filePath . '/' . $fileName,
                $thumbnailOutputPath,
                1,
                300
            );

            MediaThumbnail::create([
                'media_id' => $media->id,
                'storage' => $storage,
                'folder' => $thumbnailFolder,
                'filename' => $thumbnailFilename,
            ]);

            Log::info("Thumbnail generated successfully for video: {$filePath}/{$fileName}");
        } catch (\Exception $e) {
            Log::error("Failed to generate thumbnail: {$e->getMessage()}");
        }
    }
}
