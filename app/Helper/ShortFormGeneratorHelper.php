<?php

namespace App\Helper;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Http;

use App\Models\UserMedia;
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

use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;



class ShortFormGeneratorHelper
{



	public static function getBackgroundMusicPath($digital_asset_id)
    {

        $setting = UserMediaSetting::where('digital_asset_id',$digital_asset_id)->first();

        if ($setting->user_audio == true) {
            $userMedia = UserMedia::where('type', 'audio')
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

        $outputFileName = 'fragnant_short_form_' . $digital_asset_id . '_' . now()->format('Ymd_His') . '.mp4';

        if($format == 'storage_path'){

        	$outputPath = $outputDir . $outputFileName;

        	return $outputPath;

        }elseif($format == 'folder'){

        	$outputPath = $folder . $outputFileName;

        	return $outputPath;

        }

    }


    public static function recordPublished($digitalAssetId, $outputPath, $template = [], $userMedia = null, $type = null, $description = null)
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

        // Check if userMedia is a collection or a single object
        if ($userMedia instanceof \Illuminate\Support\Collection) {
            // Loop through the collection and insert each item
            foreach ($userMedia as $key => $media) {
                PublishedAssetMap::create([
                    'published_id' => $published->id,
                    'user_media_id' => $media->id,
                    'weight' => $key,
                ]);
            }
        } elseif ($userMedia) {
            // Handle a single object
            PublishedAssetMap::create([
                'published_id' => $published->id,
                'user_media_id' => $userMedia->id,
                'weight' => 0, // Default weight for a single item
            ]);
        }

        return $published;
    }


    public static function textOverlayVideo($digital_asset_id){

    	try {



    		$userMedia = UserMedia::where('digital_asset_id', $digital_asset_id)
            ->where('type', 'video')
            ->get()->random(1)->first();

            $inputVideoPath = Storage::disk($userMedia->storage)->path("{$userMedia->folder}/{$userMedia->filename}");

            $outputPath = self::outputDirection($digital_asset_id,'storage_path');

            FFmpegHelper::textOverlay(inputPath:$inputVideoPath,outputPath:$outputPath);


            return self::recordPublished(digitalAssetId:$digital_asset_id, outputPath:$outputPath, type:'One Frame Text');


        } catch (\Exception $e) {

            return('Error generating daily donation reel: ' . $e->getMessage());

        }

    }



public static function clipTemplatePair($digital_asset_id, $template_id = null, $firstClipPath = null)
{
    if ($template_id === null) {
        // Use `inRandomOrder` to fetch a random MediaTemplate
        $template = MediaTemplate::where('type', 'LIKE', '%clip-template%')->inRandomOrder()->first();
    } else {
        // Fetch a specific template by ID
        $template = MediaTemplate::find($template_id);
    }


    $firstClipPath = Storage::disk($template->storage)->path("{$template->folder}/{$template->filename}");

    // Define paths
    $ffmpegPath = env('FFMPEG_BINARIES', 'C:/xampp/htdocs/fragnant-social/ffmpeg/bin/ffmpeg.exe');


    $userMedia = UserMedia::where('digital_asset_id', $digital_asset_id)
        ->where('type', 'video')
        ->get()->random(1)->first();

    if (!$userMedia) {
        throw new \Exception('No digital asset media found.');
    }



    $inputVideoPath = Storage::disk($userMedia->storage)->path("{$userMedia->folder}/{$userMedia->filename}");





    $outputPath = self::outputDirection($digital_asset_id,'storage_path');


    // Ensure source files exist
    if (!file_exists($firstClipPath) || !file_exists($inputVideoPath)) {
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
        inputPath: $inputVideoPath,
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
    $mergedVideoPath = FFmpegHelper::mergeFromConcatListWithFade($concatListPath);



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
        digitalAssetId:$digital_asset_id, outputPath:$outputPath, template:$template, userMedia:$userMedia, type:'clip template pair');

}






public static function clipTemplateSlideshow($digital_asset_id, $template_id = null, $slideDuration = null, $totalSlides = null, $firstClipPath = null)
{


    if ($template_id === null) {
        // Use `inRandomOrder` to fetch a random MediaTemplate
        $template = MediaTemplate::where('type', 'LIKE', '%clip-template-slideshow%')->inRandomOrder()->first();
    } else {
        // Fetch a specific template by ID
        $template = MediaTemplate::find($template_id);
    }

    $firstClipPath = Storage::disk($template->storage)->path("{$template->folder}/{$template->filename}");

// Define paths
    $ffmpegPath = env('FFMPEG_BINARIES', 'C:/xampp/htdocs/fragnant-social/ffmpeg/bin/ffmpeg.exe');
 


    $outputPath = self::outputDirection($digital_asset_id,'storage_path');

    // Ensure source files exist
    if (!file_exists($firstClipPath)) {
        throw new \Exception('One or more source files are missing.');
    }

    $splitByFrame = FFmpegHelper::detectFrameChanges($firstClipPath);

    $readsplitFrame = FFmpegHelper::readFrameChanges($splitByFrame);

    // Step 1: Process the intro clip (5-second snippet for video only)
    $introClipPath = FFmpegHelper::cutMedia(
        inputPath: $firstClipPath,
        seconds: reset($readsplitFrame),
        frame:30
    );

    // Step 2: Extract the full audio from the first clip
    $audioPath = FFmpegHelper::extractAudio($firstClipPath);

    if($totalSlides == null){
        $totalSlides = count($readsplitFrame);
    }

    // Step 3: Process user media (images/videos)
    $userMedia = UserMedia::where('digital_asset_id', $digital_asset_id)
        ->whereIn('type', ['image', 'video'])
        ->limit($totalSlides)
        ->get();

    if ($userMedia->isEmpty()) {
        throw new \Exception('No digital asset media found.');
    }



    $slidePaths = [];

    foreach ($userMedia as $index => $media) {

        $inputPath = Storage::disk($media->storage)->path("{$media->folder}/{$media->filename}");

        $slidePath = Storage::disk("local")->path("assets/clips/slide_{$index}.mp4");

        if ($index < count($userMedia) - 1) {
            // For all but the last media
            if ($slideDuration === null) {
                $slideDuration = $readsplitFrame[$index + 2] - $readsplitFrame[$index + 1];
            }
        } else {
            // For the last media

            // Step 2: Normalize the second clip
            $extract = AppHelper::extractFileDetails($firstClipPath);


            $length = FFmpegHelper::checkVideoInfo($extract['relative_path'], $extract['storage_disk']);



            $slideDuration = $length->get('duration') - end($readsplitFrame);
        }

        if ($media->type === 'image') {
            // Convert image to video slide
            $imageToVideoCommand = "\"$ffmpegPath\" -loop 1 -i \"$inputPath\" -c:v libx264 -t $slideDuration -vf \"scale=1080:1920:force_original_aspect_ratio=decrease,pad=1080:1920:(ow-iw)/2:(oh-ih)/2,fps=30\" -crf 23 \"$slidePath\"";
            exec($imageToVideoCommand, $outputImage, $resultCodeImage);
            if ($resultCodeImage !== 0) {
                throw new \Exception("Failed to process image: $inputPath. " . implode("\n", $outputImage));
            }
        } else {

            // Trim and resize video
            $slidePath = FFmpegHelper::cutMedia(
                inputPath: $inputPath,
                seconds: $slideDuration,
                frame:30
            );

        }

        $slidePaths[] = $slidePath;
    }

    // Step 4: Create concatenation list
    array_unshift($slidePaths, $introClipPath);
    $concatListPath = FFmpegHelper::generateConcatList($slidePaths);



    // Step 5: Concatenate all videos
    $mergedVideoPath = FFmpegHelper::mergeFromConcatListWithFade($concatListPath);

    // Step 6: Add audio and synchronize duration
    FFmpegHelper::replaceAudioFromVideo($audioPath, $mergedVideoPath, $outputPath);





    // Step 7: Clean up temporary files
    @unlink($introClipPath);
    foreach ($slidePaths as $slidePath) {
        @unlink($slidePath);
    }
    @unlink($concatListPath);
    @unlink($mergedVideoPath);
    @unlink($audioPath);
    @unlink($splitByFrame);

    // Save the final video in the database

    // Save it
    return self::recordPublished(digitalAssetId:$digital_asset_id, outputPath:$outputPath, template:$template, userMedia:$userMedia,type:'clip template slideshow');
}





    public static function templateSlideshow($digital_asset_id, $template_id = null)
    {





    if ($template_id === null) {
        // Use `inRandomOrder` to fetch a random MediaTemplate
        $template = MediaTemplate::where('type', 'template-slideshow')->inRandomOrder()->first();
    } else {
        // Fetch a specific template by ID
        $template = MediaTemplate::find($template_id);
    }

    $firstClipPath = Storage::disk($template->storage)->path("{$template->folder}/{$template->filename}");

// Define paths
    $ffmpegPath = env('FFMPEG_BINARIES', 'C:/xampp/htdocs/fragnant-social/ffmpeg/bin/ffmpeg.exe');
 


    $outputPath = self::outputDirection($digital_asset_id,'storage_path');

    // Ensure source files exist
    if (!file_exists($firstClipPath)) {
        throw new \Exception('One or more source files are missing.');
    }

    $splitByFrame = FFmpegHelper::detectFrameChanges($firstClipPath, 0.1);

    $readsplitFrame = FFmpegHelper::readFrameChanges($splitByFrame);



    // Step 2: Extract the full audio from the first clip
    $audioPath = FFmpegHelper::extractAudio($firstClipPath);


    $totalSlides = count($readsplitFrame)-1;
    

    // Step 3: Process user media (images/videos)
    $userMedia = UserMedia::where('digital_asset_id', $digital_asset_id)
        ->whereIn('type', ['image', 'video'])
        ->limit($totalSlides)
        ->get();

    if ($userMedia->isEmpty()) {
        throw new \Exception('No digital asset media found.');
    }



    $slidePaths = [];

    foreach ($userMedia as $index => $media) {

        $inputPath = Storage::disk($media->storage)->path("{$media->folder}/{$media->filename}");

        $slidePath = Storage::disk("local")->path("assets/clips/slide_{$index}.mp4");

        $slideDuration = $readsplitFrame[$index+1] - $readsplitFrame[$index];
        
        if ($media->type === 'image') {
            // Convert image to video slide
            $imageToVideoCommand = "\"$ffmpegPath\" -loop 1 -i \"$inputPath\" -c:v libx264 -t $slideDuration -vf \"scale=1080:1920:force_original_aspect_ratio=decrease,pad=1080:1920:(ow-iw)/2:(oh-ih)/2,fps=30\" -crf 23 \"$slidePath\"";
            exec($imageToVideoCommand, $outputImage, $resultCodeImage);
            if ($resultCodeImage !== 0) {
                throw new \Exception("Failed to process image: $inputPath. " . implode("\n", $outputImage));
            }
        } else {

            // Trim and resize video
            $slidePath = FFmpegHelper::cutMedia(
                inputPath: $inputPath,
                seconds: $slideDuration,
                frame:30
            );

        }

        $slidePaths[] = $slidePath;
    }

    // Step 4: Create concatenation list
    $concatListPath = FFmpegHelper::generateConcatList($slidePaths);



    // Step 5: Concatenate all videos
    $mergedVideoPath = FFmpegHelper::mergeFromConcatList($concatListPath);

    // Step 6: Add audio and synchronize duration
    FFmpegHelper::replaceAudioFromVideo($audioPath, $mergedVideoPath, $outputPath);





    // Step 7: Clean up temporary files
    @unlink($introClipPath);
    foreach ($slidePaths as $slidePath) {
        @unlink($slidePath);
    }
    @unlink($concatListPath);
    @unlink($mergedVideoPath);
    @unlink($audioPath);
    @unlink($splitByFrame);

    // Save the final video in the database

    // Save it
    return self::recordPublished(digitalAssetId:$digital_asset_id, outputPath:$outputPath, template:$template, userMedia:$userMedia,type:'template slideshow');






    }










    public static function customSlideShow($digital_asset_id,$slideDuration = 1,$totalVideo = 5)
    {

        $ffmpegPath =  env('FFMPEG_BINARIES', '/usr/bin/ffmpeg');
        $ffprobePath = env('FFPROBE_BINARIES', '/usr/bin/ffprobe');

        $userMedia = UserMedia::where('digital_asset_id', $digital_asset_id)
            ->where('type', 'video')
            ->get()->random($totalVideo);

        if ($userMedia->isEmpty()) {
            logger()->error("No videos found for digital asset ID {$digital_asset_id}");
            return;
        }

        $outputPath = self::outputDirection($digital_asset_id,'storage_path');

        $localMusicPath = self::getBackgroundMusicPath($digital_asset_id);

        if (!$localMusicPath) {
            logger()->error("Failed to retrieve background music for digital asset ID {$digital_asset_id}");
            return;
        }


        $slidePaths = [];

        foreach ($userMedia as $index => $media) {

            $inputPath = Storage::disk($media->storage)->path("{$media->folder}/{$media->filename}");

            $slidePath = Storage::disk("local")->path("assets/clips/slide_{$index}.mp4");


            if ($media->type === 'image') {
                // Convert image to video slide
                $imageToVideoCommand = "\"$ffmpegPath\" -loop 1 -i \"$inputPath\" -c:v libx264 -t $slideDuration -vf \"scale=1080:1920:force_original_aspect_ratio=decrease,pad=1080:1920:(ow-iw)/2:(oh-ih)/2,fps=30\" -crf 23 \"$slidePath\"";
                exec($imageToVideoCommand, $outputImage, $resultCodeImage);
                if ($resultCodeImage !== 0) {
                    throw new \Exception("Failed to process image: $inputPath. " . implode("\n", $outputImage));
                }
            } else {

                // Trim and resize video
                $slidePath = FFmpegHelper::cutMedia(
                    inputPath: $inputPath,
                    seconds: $slideDuration,
                    frame:30
                );

            }

            $slidePaths[] = $slidePath;
        }

        // Step 4: Create concatenation list
        $concatListPath = FFmpegHelper::generateConcatList($slidePaths);

        // Step 5: Concatenate all videos
        $mergedVideoPath = FFmpegHelper::mergeFromConcatList($concatListPath);

        $totalDuration = count($slidePaths) * $slideDuration;

        // Step 6: Extract the full audio from the first clip
        $audioPath = FFmpegHelper::extractAudioRandomStart($localMusicPath,$totalDuration);

        // Step 6: Add audio and synchronize duration
        FFmpegHelper::replaceAudioFromVideo($audioPath, $mergedVideoPath, $outputPath);

         // Step 7: Clean up temporary files
        foreach ($slidePaths as $slidePath) {
            @unlink($slidePath);
        }
        @unlink($localMusicPath);
        @unlink($concatListPath);
        @unlink($audioPath);
        @unlink($mergedVideoPath);

    
        // Step 6.5
        return self::recordPublished(
            digitalAssetId:$digital_asset_id,
            outputPath:$outputPath,
            userMedia:$userMedia,
            type:'slideshow'
        );
      
    }



}