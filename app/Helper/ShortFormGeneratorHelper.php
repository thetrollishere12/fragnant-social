<?php

namespace App\Helper;

use GuzzleHttp\Client;

use Illuminate\Support\Facades\Http;

use App\Models\Product\Product;

use App\Models\UserMediaSetting;

use App\Models\PublishedMedia;

use App\Models\MusicGenre;

use App\Models\User;

use Illuminate\Support\Arr;

use Illuminate\Support\Facades\File;

use Storage;

use Db;


use App\Models\MediaTemplate;

use App\Models\PublishedDetail;

use App\Models\PublishedAssetMap;


use App\Helper\FFmpegHelper;


use App\Helper\Editor\ImageHelper;


use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;

use App\Helper\Editor\StructureHelper;

class ShortFormGeneratorHelper
{



	public static function getBackgroundMusicPath($digital_asset_id)
    {

        $setting = UserMediaSetting::where('digital_asset_id',$digital_asset_id)->first();

        if ($setting->user_audio == true) {
            $userMedia = Product::where('type', 'audio')
                ->where('digital_asset_id', $digital_asset_id)
                ->inRandomOrder()
                ->first();

            if($userMedia){
                return Storage::disk($userMedia->storage)->path($userMedia->folder . '/' . $userMedia->filename);
            }else{



                $randomGenreName = MusicGenre::get()->random(1)->first()->value('name');
                $trendingTracks = AudiusHelper::getTrendingTracks($randomGenreName);
                $trackArray = $trendingTracks['data'] ?? [];
                $randomTrack = is_array($trackArray) && !empty($trackArray)
                    ? Arr::random($trackArray)
                    : null;

                $backgroundMusicPath = AudiusHelper::streamTrack($randomTrack['id']);
                $localMusicPath = Storage::disk('private')->path('temp/' . uniqid('', true) . '.mp3');

                try {
                    $musicContent = file_get_contents($backgroundMusicPath);
                    file_put_contents($localMusicPath, $musicContent);
                    return $localMusicPath;
                } catch (\Exception $e) {
                    logger()->error("Failed to download background music: " . $e->getMessage());
                    return null;
                }


            }

        } else {
            $randomGenre = is_array($setting->music_genre_id) && !empty($setting->music_genre_id)
                ? Arr::random($setting->music_genre_id)
                : null;

            $randomGenreName = MusicGenre::find($randomGenre)->value('name');
            $trendingTracks = AudiusHelper::getTrendingTracks($randomGenreName);
            $trackArray = $trendingTracks['data'] ?? [];
            $randomTrack = is_array($trackArray) && !empty($trackArray)
                ? Arr::random($trackArray)
                : null;

            $backgroundMusicPath = AudiusHelper::streamTrack($randomTrack['id']);
            $localMusicPath = Storage::disk('private')->path('temp/' . uniqid('', true) . '.mp3');

            try {
                $musicContent = file_get_contents($backgroundMusicPath);
                file_put_contents($localMusicPath, $musicContent);
                return $localMusicPath;
            } catch (\Exception $e) {
                logger()->error("Failed to download background music: " . $e->getMessage());
                return null;
            }
        }
    }



    public static function outputDirection($digital_asset_id, $format = 'short', $folderName = 'published'){

    	$folder = 'digital-assets/'.$digital_asset_id.'/'.$folderName.'/';

    	$outputDir = storage_path('app/public/'.$folder);
        
        if (!File::exists($outputDir)) {
            File::makeDirectory($outputDir, 0755, true);
        }

        $outputFileName = 'prixsel_short_form_' . $digital_asset_id . '_' . now()->format('Ymd_His') . '.mp4';

        if($format == 'storage_path'){

        	$outputPath = $outputDir . $outputFileName;

        	return $outputPath;

        }elseif($format == 'folder'){

        	$outputPath = $folder . $outputFileName;

        	return $outputPath;

        }

    }


    public static function recordPublished($digitalAssetId, $outputPath, $template = [], $productMedia = null, $type = null, $description = null)
    {

        // Create the published media record
        $published = PublishedMedia::create([
            'url' => 'digital-assets/' . $digitalAssetId . '/published/' . basename($outputPath),
            'digital_asset_id' => $digitalAssetId,
        ]);

        // Create the published detail record
        PublishedDetail::create([
            'published_id' => $published->id,
            'media_template_id' => $template->id ?? null, // Handle optional template ID
            'type' => $type,
            'description' => $description
        ]);

        // Check if productMedia is a collection or a single object
        if ($productMedia instanceof \Illuminate\Support\Collection) {
            // Loop through the collection and insert each item
            foreach ($productMedia as $key => $media) {
                PublishedAssetMap::create([
                    'published_id' => $published->id,
                    'product_media_id' => $media->id,
                    'weight' => $key,
                ]);
            }
        } elseif ($productMedia) {
            // Handle a single object
            PublishedAssetMap::create([
                'published_id' => $published->id,
                'product_media_id' => $productMedia->id,
                'weight' => 0, // Default weight for a single item
            ]);
        }

        return $published;
    }




// public static function clipTemplatePairStructure(&$project, $product_id = 1, $template_id = null)
// {
//     $template = MediaTemplate::where('type', 'LIKE', '%clip-template%')->inRandomOrder()->first();
//     $firstClipPath = Storage::disk($template->storage)->path("{$template->folder}/{$template->filename}");

//     // Add Video Track
//     $trackId = 1;
//     StructureHelper::addTrack($project, 'video', $trackId);

//     // First Clip (Video)
//     StructureHelper::addClip(
//         $project, 'video', $trackId, 'clip1', $firstClipPath, 
//         "00:00:00", "00:00:04", "00:00:00"
//     );

//     // Fetch Product Image
//     $productMedia = Product::find($product_id)->media->random(1)->first();
//     $content = AppHelper::extractFileDetails(AppHelper::downloadContent(url:$productMedia->url, disk:'local'));

//     // Second Clip (Image) - Treat it as a static video frame
//     StructureHelper::addClip(
//         $project, 'video', $trackId, 'clip2', $content['full_path'], 
//         "00:00:00", "00:00:04", "00:00:04", 
//         [], 1.0, 1.0, 0, 0 // Ensuring full opacity and original scale
//     );


//     // ✅ Add Audio Track (Ensure it's initialized)
//     $audioTrackId = 2; // Different from video track
//     StructureHelper::addTrack($project, 'audio', $audioTrackId);

//     // ✅ Add Audio Clip to the Correct Track
//     StructureHelper::addClip(
//         $project, 'audio', $audioTrackId, 'audio1', $firstClipPath, 
//         "00:00:00", "00:00:08", "00:00:00"
//     );


//     return $project;

    
// }



public static function clipTemplatePair($digital_asset_id, $product_id, $template_id = null)
{
    if ($template_id === null) {
        // Use `inRandomOrder` to fetch a random MediaTemplate
        $template = MediaTemplate::where('type', 'LIKE', '%clip-template%')->inRandomOrder()->first();
    } else {
        // Fetch a specific template by ID
        $template = MediaTemplate::find($template_id);
    }


    $firstClipPath = Storage::disk($template->storage)->path("{$template->folder}/{$template->filename}");


    $productMedia = Product::find($product_id)->media->random(1)->first();

    $content = AppHelper::extractFileDetails(AppHelper::downloadContent($productMedia->url));

    $converted_video = ImageHelper::convertImageToVideoPan(
        imagePath: $content['full_path'],
        duration: 5);



    $outputPath = self::outputDirection($digital_asset_id,'storage_path');


    // Ensure source files exist
    if (!file_exists($firstClipPath) || !file_exists($converted_video)) {
        throw new \Exception('One or more source files are missing.');
    }

    // Step 1: Normalize the first clip
    $splitByFrame = FFmpegHelper::detectFrameChanges($firstClipPath);

    $readsplitFrame = FFmpegHelper::readFrameChanges($splitByFrame);

    // Step 1: Process the intro clip (5-second snippet for video only)
    $introClipPath = FFmpegHelper::cutMedia(
        inputPath: $firstClipPath,
        seconds: reset($readsplitFrame),
        frame:30
    );

    // Step 2: Normalize the second clip
    $extract = AppHelper::extractFileDetails($firstClipPath);

    $length = FFmpegHelper::checkVideoInfo($extract['relative_path'], $extract['storage_disk']);

    $tempInputVideoPath = FFmpegHelper::cutMedia(
        inputPath: $converted_video,
        seconds:$length->get('duration')-reset($readsplitFrame),
        frame:30
    );


    // Step 3: Extract audio from the first clip
    $audioPath = FFmpegHelper::extractAudio($firstClipPath);

    // Step 4: Create the concatenation list
    $concatListPath = FFmpegHelper::generateConcatList([
        $introClipPath,
        $tempInputVideoPath
    ]);


    // Step 5: Concatenate the videos
    $mergedVideoPath = FFmpegHelper::mergeFromConcatList($concatListPath);


    // Step 6: Match audio and video duration
    FFmpegHelper::replaceAudioFromVideo($audioPath, $mergedVideoPath, $outputPath);


    // Step 7: Clean up temporary files
    @unlink($introClipPath);
    @unlink($tempInputVideoPath);
    @unlink($concatListPath);
    @unlink($audioPath);
    @unlink($mergedVideoPath);
    @unlink($splitByFrame);

    // Save it
    return self::recordPublished(
        digitalAssetId:$digital_asset_id, outputPath:$outputPath, template:$template, productMedia:$productMedia, type:'clip template pair');

}





}