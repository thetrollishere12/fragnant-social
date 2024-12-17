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
                $localMusicPath = Storage::disk('public')->path('assets/music/' . uniqid('', true) . '.mp3');

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
            $localMusicPath = Storage::disk('public')->path('assets/music/' . uniqid('', true) . '.mp3');

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


    public static function outputDirection($digital_asset_id,$format = 'short'){

    	$folder = 'digital-assets/'.$digital_asset_id.'/published/';

    	$outputDir = storage_path('app/public/'.$folder);
        
        if (!File::exists($outputDir)) {
            File::makeDirectory($outputDir, 0755, true);
        }

        $outputFileName = 'combined_reel_user_' . $digital_asset_id . '_' . now()->format('Ymd_His') . '.mp4';

        if($format == 'storage_path'){

        	$outputPath = $outputDir . $outputFileName;

        	return $outputPath;

        }elseif($format == 'folder'){

        	$outputPath = $folder . $outputFileName;

        	return $outputPath;

        }

    }

    public static function OneFrameText($digital_asset_id,$textColor = "white", $topText = "This is the top text" ,$topTextPosition = 200, $bottomText = "This is the bottom text" ,$bottomTextPosition = 300){

    	try {

    		$userMedia = UserMedia::where('digital_asset_id', $digital_asset_id)
            ->where('type', 'video')
            ->get()->random(1)->first();

            $inputVideo = $userMedia->folder.'/'.$userMedia->filename;

            $outputPath = self::outputDirection($digital_asset_id,'folder');

            $fontPath = str_replace('C:', 'C\:', str_replace('\\', '\\\\',Storage::disk('public')->path('assets/fonts/Funnel_Sans/FunnelSans-Italic-VariableFont_wght.ttf')));

            // Generate video with text overlays and resizing
            FFMpeg::fromDisk('public')
                ->open($inputVideo)
                ->addFilter([
                    '-vf',
                    "scale=1080:1920:force_original_aspect_ratio=decrease,pad=1080:1920:(ow-iw)/2:(oh-ih)/2," .
                    "drawtext=fontfile='$fontPath':text='".$topText."':fontcolor=".$textColor.":fontsize=60:x=(w-text_w)/2:y=".$topTextPosition."," .
                    "drawtext=fontfile='$fontPath':text='".$bottomText."':fontcolor=".$textColor.":fontsize=60:x=(w-text_w)/2:y=h-".$bottomTextPosition.""
                ])
                ->export()
                // ->inFormat(new \FFMpeg\Format\Video\X264)
                ->toDisk('public')
                ->save($outputPath);


            return $outputPath;


        } catch (\Exception $e) {
            return('Error generating daily donation reel: ' . $e->getMessage());
        }

    }

public static function OneFrameSnippet($digital_asset_id)
{
    // Define paths
    $ffmpegPath = env('FFMPEG_BINARIES', 'C:/xampp/htdocs/fragnant-social/ffmpeg/bin/ffmpeg.exe');
    $firstClipPath = storage_path('app/public/assets/clips/micheal.mp4');

    $userMedia = UserMedia::where('digital_asset_id', $digital_asset_id)
        ->where('type', 'video')
        ->get()->random(1)->first();

    if (!$userMedia) {
        throw new \Exception('No digital asset media found.');
    }

    $inputVideoPath = storage_path('app/public/' . $userMedia->folder . '/' . $userMedia->filename);
    $outputDir = storage_path('app/public/published');
    if (!file_exists($outputDir)) {
        mkdir($outputDir, 0777, true);
    }

    $tempSnippetPath = storage_path('app/public/assets/clips/temp_snippet.mp4');
    $tempInputVideoPath = storage_path('app/public/assets/clips/temp_input_resized.mp4');
    $concatListPath = storage_path('app/public/assets/clips/concat_list.txt');
    $audioPath = storage_path('app/public/assets/clips/extracted_audio.mp3');
    $finalOutputPath = $outputDir . '/final_video_' . time() . '.mp4';

    // Ensure source files exist
    if (!file_exists($firstClipPath) || !file_exists($inputVideoPath)) {
        throw new \Exception('One or more source files are missing.');
    }

    // Step 1: Normalize the first clip
    $resizeSnippetCommand = "\"$ffmpegPath\" -i \"$firstClipPath\" -an -t 5.4 -vf \"scale=1080:1920:force_original_aspect_ratio=decrease,pad=1080:1920:(ow-iw)/2:(oh-ih)/2,fps=30\" \"$tempSnippetPath\"";
    exec($resizeSnippetCommand, $outputSnippet, $resultCodeSnippet);
    if ($resultCodeSnippet !== 0) {
        throw new \Exception('Failed to resize the first clip: ' . implode("\n", $outputSnippet));
    }

    // Step 2: Normalize the second clip
    $resizeInputCommand = "\"$ffmpegPath\" -i \"$inputVideoPath\" -an -vf \"scale=1080:1920:force_original_aspect_ratio=decrease,pad=1080:1920:(ow-iw)/2:(oh-ih)/2,fps=30\" \"$tempInputVideoPath\"";
    exec($resizeInputCommand, $outputInput, $resultCodeInput);
    if ($resultCodeInput !== 0) {
        throw new \Exception('Failed to resize the second clip: ' . implode("\n", $outputInput));
    }

    // Step 3: Extract audio from the first clip
    $extractAudioCommand = "\"$ffmpegPath\" -i \"$firstClipPath\" -q:a 0 -map a \"$audioPath\"";
    exec($extractAudioCommand, $outputAudio, $resultCodeAudio);
    if ($resultCodeAudio !== 0) {
        throw new \Exception('Failed to extract audio: ' . implode("\n", $outputAudio));
    }

    // Step 4: Create the concatenation list
    file_put_contents($concatListPath, "file '$tempSnippetPath'\nfile '$tempInputVideoPath'");

    // Step 5: Concatenate the videos
    $mergedVideoPath = $outputDir . '/merged_video_' . time() . '.mp4';
    $concatCommand = "\"$ffmpegPath\" -f concat -safe 0 -i \"$concatListPath\" -c:v libx264 -preset fast -crf 23 -an \"$mergedVideoPath\"";
    exec($concatCommand, $outputConcat, $resultCodeConcat);
    if ($resultCodeConcat !== 0) {
        throw new \Exception('Failed to concatenate the videos: ' . implode("\n", $outputConcat));
    }

    // Step 6: Match audio and video duration
    $syncCommand = "\"$ffmpegPath\" -i \"$mergedVideoPath\" -i \"$audioPath\" -c:v copy -c:a aac -shortest \"$finalOutputPath\"";
    exec($syncCommand, $outputSync, $resultCodeSync);
    if ($resultCodeSync !== 0) {
        throw new \Exception('Failed to synchronize audio and video: ' . implode("\n", $outputSync));
    }

    // Step 7: Clean up temporary files
    @unlink($tempSnippetPath);
    @unlink($tempInputVideoPath);
    @unlink($concatListPath);
    @unlink($audioPath);
    @unlink($mergedVideoPath);


    return $finalOutputPath;
}






public static function SlideShowSnippet($digital_asset_id, $slideDuration = 2, $totalSlides = 2)
{
// Define paths
    $ffmpegPath = env('FFMPEG_BINARIES', 'C:/xampp/htdocs/fragnant-social/ffmpeg/bin/ffmpeg.exe');
    $firstClipPath = storage_path('app/public/assets/clips/micheal.mp4');
    $outputDir = storage_path('app/public/published');
    if (!file_exists($outputDir)) {
        mkdir($outputDir, 0777, true);
    }

    $tempDir = storage_path('app/public/assets/clips/');
    $concatListPath = $tempDir . 'concat_list.txt';
    $introClipPath = $tempDir . 'intro_clip.mp4';
    $audioPath = $tempDir . 'full_audio.mp3';
    $finalOutputPath = $outputDir . '/slideshow_video_' . time() . '.mp4';

    // Step 1: Process the intro clip (5-second snippet for video only)
    $resizeIntroCommand = "\"$ffmpegPath\" -i \"$firstClipPath\" -t 5.4 -vf \"scale=1080:1920:force_original_aspect_ratio=decrease,pad=1080:1920:(ow-iw)/2:(oh-ih)/2,fps=30\" -c:v libx264 -crf 23 \"$introClipPath\"";
    exec($resizeIntroCommand, $outputIntro, $resultCodeIntro);
    if ($resultCodeIntro !== 0) {
        throw new \Exception('Failed to process the intro clip: ' . implode("\n", $outputIntro));
    }

    // Step 2: Extract the full audio from the first clip
    $extractFullAudioCommand = "\"$ffmpegPath\" -i \"$firstClipPath\" -q:a 0 -map a \"$audioPath\"";
    exec($extractFullAudioCommand, $outputAudio, $resultCodeAudio);
    if ($resultCodeAudio !== 0) {
        throw new \Exception('Failed to extract full audio: ' . implode("\n", $outputAudio));
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
        $inputPath = storage_path('app/public/' . $media->folder . '/' . $media->filename);
        $slidePath = $tempDir . "slide_$index.mp4";

        if ($media->type === 'image') {
            // Convert image to video slide
            $imageToVideoCommand = "\"$ffmpegPath\" -loop 1 -i \"$inputPath\" -c:v libx264 -t $slideDuration -vf \"scale=1080:1920:force_original_aspect_ratio=decrease,pad=1080:1920:(ow-iw)/2:(oh-ih)/2,fps=30\" -crf 23 \"$slidePath\"";
            exec($imageToVideoCommand, $outputImage, $resultCodeImage);
            if ($resultCodeImage !== 0) {
                throw new \Exception("Failed to process image: $inputPath. " . implode("\n", $outputImage));
            }
        } else {
            // Trim and resize video
            $videoToSlideCommand = "\"$ffmpegPath\" -i \"$inputPath\" -an -vf \"scale=1080:1920:force_original_aspect_ratio=decrease,pad=1080:1920:(ow-iw)/2:(oh-ih)/2,fps=30\" -t $slideDuration -c:v libx264 -crf 23 \"$slidePath\"";
            exec($videoToSlideCommand, $outputVideo, $resultCodeVideo);
            if ($resultCodeVideo !== 0) {
                throw new \Exception("Failed to process video: $inputPath. " . implode("\n", $outputVideo));
            }
        }

        $slidePaths[] = $slidePath;
    }

    // Step 4: Create concatenation list
    $concatContent = "file '$introClipPath'\n";
    foreach ($slidePaths as $slidePath) {
        $concatContent .= "file '$slidePath'\n";
    }
    file_put_contents($concatListPath, $concatContent);

    // Step 5: Concatenate all videos
    $mergedVideoPath = $tempDir . 'merged_slideshow.mp4';
    $concatCommand = "\"$ffmpegPath\" -f concat -safe 0 -i \"$concatListPath\" -c:v libx264 -preset fast -crf 23 \"$mergedVideoPath\"";
    exec($concatCommand, $outputConcat, $resultCodeConcat);
    if ($resultCodeConcat !== 0) {
        throw new \Exception('Failed to concatenate videos: ' . implode("\n", $outputConcat));
    }

    // Step 6: Add audio and synchronize duration
    $syncCommand = "\"$ffmpegPath\" -i \"$mergedVideoPath\" -i \"$audioPath\" -filter_complex \"[0:v:0][1:a:0]concat=n=1:v=1:a=1[outv][outa]\" -map \"[outv]\" -map \"[outa]\" -c:v libx264 -c:a aac \"$finalOutputPath\"";
    exec($syncCommand, $outputSync, $resultCodeSync);
    if ($resultCodeSync !== 0) {
        throw new \Exception('Failed to synchronize audio and video: ' . implode("\n", $outputSync));
    }

    // Step 7: Clean up temporary files
    @unlink($introClipPath);
    foreach ($slidePaths as $slidePath) {
        @unlink($slidePath);
    }
    @unlink($concatListPath);
    @unlink($mergedVideoPath);
    @unlink($audioPath);

    // Save the final video in the database

    return $finalOutputPath;
}







    public static function slideShow($digital_asset_id,$videoDuration = 2,$totalVideo = 5)
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



        $inputFiles = [];
        $filterComplex = '';
        $concatParts = '';
        $validVideoCount = 0;

        foreach ($userMedia as $mediaFile) {
            $videoPath = Storage::disk($mediaFile->storage)->path("{$mediaFile->folder}/{$mediaFile->filename}");
            if (!file_exists($videoPath)) {
                logger()->error("Video file not found: $videoPath");
                continue;
            }

            $inputFiles[] = "-i " . escapeshellarg($videoPath);
            $filterComplex .= "[{$validVideoCount}:v]trim=duration={$videoDuration},setpts=PTS-STARTPTS,scale=1920:1080:force_original_aspect_ratio=decrease,pad=1920:1080:(ow-iw)/2:(oh-ih)/2[vid{$validVideoCount}];";
            $concatParts .= "[vid{$validVideoCount}]";
            $validVideoCount++;
        }

        if ($validVideoCount === 0) {
            logger()->error("No valid videos found for digital asset ID {$digital_asset_id}");
            return;
        }

        $totalVideoDuration = $validVideoCount * $videoDuration;

        $filterComplex .= "{$concatParts}concat=n={$validVideoCount}:v=1:a=0[outv];";
        $inputFiles[] = "-i " . escapeshellarg($localMusicPath);

        $audioDuration = 60;
        $randomStart = max(0, rand(0, $audioDuration - $totalVideoDuration));

        $filterComplex .= "[{$validVideoCount}:a]atrim=start={$randomStart}:duration={$totalVideoDuration},asetpts=PTS-STARTPTS,volume=1.9[finalaudio]";

        $ffmpegCmd = $ffmpegPath . ' ' . implode(' ', $inputFiles) .
            " -filter_complex \"" . $filterComplex . "\" " .
            " -map \"[outv]\" -map \"[finalaudio]\" -r 60 -y " . escapeshellarg($outputPath);

        exec($ffmpegCmd . ' 2>&1', $output, $returnVar);

        if ($returnVar === 0 && file_exists($outputPath)) {

            return $outputPath;

        } else {
            logger()->error("Failed to generate combined reel for digital asset ID {$digital_asset_id}. FFmpeg output: " . implode("\n", $output));
        }


    }



}