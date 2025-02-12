<?php

namespace App\Helper\Editor;

use App\Helper\FFmpegHelper;
use App\Helper\AppHelper;

use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;

use Illuminate\Support\Facades\File;

use Storage;

use App\Models\Product\Product;

use Db;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;


use App\Helper\Media\PexelsHelper;

use FFMpeg\Format\Video\X264;

class ImageHelper
{









public static function transparent_background($input, $output = null)
{
    // Step 1: Detect if the background is blank using Python
    $pythonScriptPath = base_path('python/background_image/detect.py');
    
    // Run Python script and capture output
    $process = new Process(['python', $pythonScriptPath, $input]);
    $process->run();

    if (!$process->isSuccessful()) {
        logger()->error("Python detection script failed: {$process->getErrorOutput()}");
        throw new ProcessFailedException($process);
    }

    // Capture output (make sure Python prints only "True" or "False")
    $isBlank = trim($process->getOutput()) === "True";

    if ($isBlank) {

        if (empty($output)) {
            $directory = pathinfo($input, PATHINFO_DIRNAME);
            $filename = pathinfo($input, PATHINFO_FILENAME); // Filename without extension
            $output = $directory . DIRECTORY_SEPARATOR . "bg_r_{$filename}.png";
        }

        logger()->info("✅ Image has a blank background. Proceeding with background removal...");

        // Step 2: Remove background using rembg
        $rembgScriptPath = base_path('python/background_image/remove.py');
        $rembgProcess = new Process(['python', $rembgScriptPath, $input, $output]);
        $rembgProcess->run();

        if (!$rembgProcess->isSuccessful()) {

            return logger()->error("Background removal failed: {$rembgProcess->getErrorOutput()}");
        }

        logger()->info("✅ Background removed and saved as {$output}");

        return $output;

    } else {

        logger()->info("❌ Image does NOT have a blank background. Skipping background removal.");
        return null;

    }
}



public static function imageOverlay(
    $imagePath,
    $outputPath = null,
    $format = 'mp4',
    $resolution = '1080:1920', // Updated resolution for Instagram Reels
    $duration = 5 
) {
    // Step 1: Get a background video from Pexels
    $background = PexelsHelper::videos('background');

    if (empty($background->videos)) {
        throw new \Exception("No background videos found.");
    }

    $videoUrl = $background->videos[0]->video_files[0]->link;

    // Step 2: Download the video locally
    $bgPath = AppHelper::downloadContent(url:$videoUrl, disk:'local');

    // Step 3: Set up FFmpeg binary path
    $ffmpegPath = env('FFMPEG_BINARIES', 'C:/xampp/htdocs/fragnant-social/ffmpeg/bin/ffmpeg.exe');

    // Ensure output path
    $outputPath = $outputPath ?? Storage::disk('local')->path('temp/' . uniqid('img_overlay_vid_', false) . '.' . $format);

    // Extract width & height
    list($width, $height) = explode(':', $resolution);

    // Step 4: Overlay image on video using FFmpeg
    $ffmpegCommand = "\"{$ffmpegPath}\" -i \"{$bgPath}\" -i \"{$imagePath}\" -filter_complex \"[1:v]scale={$width}:{$height}[img];[0:v]scale={$width}:{$height}[bg];[bg][img]overlay=(W-w)/2:(H-h)/2\" -c:a copy \"{$outputPath}\"";

    exec($ffmpegCommand, $output, $returnCode);

    if ($returnCode !== 0) {
        throw new \Exception("FFmpeg failed to process video: " . implode("\n", $output));
    }

    // Step 5: Clean up temporary video file
    unlink($bgPath);

    return $outputPath;
}


public static function convertImageToVideoPan(
    $imagePath, 
    $outputPath = null, 
    $format = 'mp4', 
    $duration = 5, 
    $resolution = '1000:1920', 
    $zoomFactor = 2, 
    $startCorner = 'top-left', 
    $endCorner = 'bottom-left', 
    $movementSpeed = 0.02
) {
    try {
        $ffmpegPath = env('FFMPEG_BINARIES', 'C:/xampp/htdocs/fragnant-social/ffmpeg/bin/ffmpeg.exe');

        $outputPath = $outputPath ?? Storage::disk('local')->path('temp/' . uniqid('img_pan_vid_', false) . '.' . $format);

        // Extract width & height
        list($width, $height) = explode(':', $resolution);
        
        // Define start coordinates
        $cornerPositions = [
            'top-left'     => ["0", "0"],
            'top-right'    => ["iw - iw/$zoomFactor", "0"],
            'bottom-left'  => ["0", "ih - ih/$zoomFactor"],
            'bottom-right' => ["iw - iw/$zoomFactor", "ih - ih/$zoomFactor"]
        ];

        // Get start and end positions
        $xStart = $cornerPositions[$startCorner][0] ?? "0";
        $yStart = $cornerPositions[$startCorner][1] ?? "0";
        $xEnd = $cornerPositions[$endCorner][0] ?? "(iw - iw/$zoomFactor)";
        $yEnd = $cornerPositions[$endCorner][1] ?? "(ih - ih/$zoomFactor)";

        // Adjust movement speed
        $speedFactor = $movementSpeed / $duration; 

        // Zoom-in pan effect from start to end with speed adjustment
        $zoomPanFilter = "zoompan=z='$zoomFactor':x='lerp($xStart,$xEnd,on*$speedFactor)':y='lerp($yStart,$yEnd,on*$speedFactor)':d=" . ($duration * 30);

        // Scale and pad while maintaining aspect ratio
        $videoFilter = "[0:v]$zoomPanFilter,scale=$width:$height:force_original_aspect_ratio=decrease,pad=$width:$height:(ow-iw)/2:(oh-ih)/2,format=yuv420p";

        // FFmpeg command
        $command = "\"$ffmpegPath\" -loop 1 -i \"$imagePath\" -vf \"$videoFilter\" -c:v libx264 -t $duration -r 30 -pix_fmt yuv420p \"$outputPath\"";

        exec($command, $output, $returnCode);

        if ($returnCode === 0) {
            Log::info("Pan effect video created successfully: $outputPath");
            return $outputPath;
        } else {
            Log::error('FFmpeg Image to Video Pan Error: ' . implode("\n", $output));
        }
    } catch (\Exception $e) {
        Log::error('FFmpeg Image to Video Pan Error: ' . $e->getMessage());
        throw $e;
    }
}


public static function convertImageToVideo($imagePath, $outputPath = null, $format = 'mp4', $duration = 5, $resolution = '1000:1920')
{
    try {
        $ffmpegPath = env('FFMPEG_BINARIES', 'C:/xampp/htdocs/fragnant-social/ffmpeg/bin/ffmpeg.exe');

        $outputPath = $outputPath ?? Storage::disk('local')->path('temp/' . uniqid('img_to_vid_', false) . '.' . $format);

        // Extract width & height from resolution (e.g., "1000:1920")
        list($width, $height) = explode(':', $resolution);

        // Scale the image to fit while maintaining aspect ratio, then pad if necessary
        $videoFilter = "scale=$width:$height:force_original_aspect_ratio=decrease,pad=$width:$height:(ow-iw)/2:(oh-ih)/2";

        // FFmpeg command with padding instead of squeezing
        $command = "\"$ffmpegPath\" -loop 1 -i \"$imagePath\" -c:v libx264 -t $duration -pix_fmt yuv420p -vf \"$videoFilter\" \"$outputPath\"";

        exec($command, $output, $returnCode);

        if ($returnCode === 0) {
            Log::info("Image converted to video successfully: $outputPath");
            return $outputPath;
        } else {
            Log::error('FFmpeg Image to Video Conversion Error: ' . implode("\n", $output));
        }
    } catch (\Exception $e) {
        Log::error('FFmpeg Image to Video Conversion Error: ' . $e->getMessage());
        throw $e;
    }
}







public static function convertImageToZoomVideo(
    $imagePath,
    $outputPath = null,
    $format = 'mp4', 
    $duration = 5, 
    $resolution = '1000x1920', 
    $zoomSpeed = 0.002, 
    $zoomFocus = 'center'
) {
    try {
        $ffmpegPath = env('FFMPEG_BINARIES', 'C:/xampp/htdocs/fragnant-social/ffmpeg/bin/ffmpeg.exe');

        $outputPath = $outputPath ?? Storage::disk('local')->path('temp/' . uniqid('img_to_vid_', false) . '.' . $format);

        // Extract width and height from resolution
        list($width, $height) = explode('x', $resolution);

        // Define zoom focus points
        switch ($zoomFocus) {
            case 'top-left':
                $x = "0"; 
                $y = "0";
                break;
            case 'top-right':
                $x = "iw-(iw/zoom)"; 
                $y = "0";
                break;
            case 'bottom-left':
                $x = "0"; 
                $y = "ih-(ih/zoom)";
                break;
            case 'bottom-right':
                $x = "iw-(iw/zoom)"; 
                $y = "ih-(ih/zoom)";
                break;
            case 'center':
            default:
                // Smooth center zoom using fixed reference
                $x = "(iw - iw/zoom)/2";
                $y = "(ih - ih/zoom)/2";
                break;
        }

        // FFmpeg zoom filter with improved centering formula
        $zoomFilter = "zoompan=z='zoom+$zoomSpeed':x=$x:y=$y:d=" . ($duration * 30) . ":s=$resolution,format=yuv420p";

        // Final FFmpeg command
        $command = "\"$ffmpegPath\" -loop 1 -i \"$imagePath\" -vf \"$zoomFilter\" -c:v libx264 -t $duration -r 300 -pix_fmt yuv420p \"$outputPath.$format\"";

        exec($command, $output, $returnCode);

        if ($returnCode === 0) {
            Log::info("Image converted to zoom-in video successfully: {$outputPath}.$format");
            return $outputPath . '.' . $format;
        } else {
            Log::error('FFmpeg Image to Video Conversion Error: ' . implode("\n", $output));
        }
    } catch (\Exception $e) {
        Log::error('FFmpeg Image to Video Conversion Error: ' . $e->getMessage());
        throw $e;
    }
}











}