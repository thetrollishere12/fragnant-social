<?php

namespace App\Helper;


use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg as LaravelFFMpeg;
use Storage;
use Illuminate\Support\Facades\Log;

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

use Illuminate\Support\Str;
use Symfony\Component\Process\Process;
use Illuminate\Support\Facades\File;

use App\Helper\AppHelper;

class FFmpegHelper
{





//     public static function parseSrtFile($srtFilePath)
//     {
//         $timestamps = [];
//         $srtContent = Storage::disk('public')->path($srtFilePath);
//         preg_match_all('/(\d+)\n(\d{2}:\d{2}:\d{2},\d{3}) --> (\d{2}:\d{2}:\d{2},\d{3})\n(.*?)\n\n/s', $srtContent, $matches, PREG_SET_ORDER);

//         foreach ($matches as $match) {
//             $start = strtotime("1970-01-01 {$match[2]} UTC");
//             $end = strtotime("1970-01-01 {$match[3]} UTC");
//             $timestamps[] = ['start' => $start, 'end' => $end];
//         }

//         return $timestamps;
//     }



//     public static function checkSrt($inputPath) {
        



//         // Path to the SRT file
//         $filePath = Storage::disk('public')->path($inputPath);

//         // Read the file contents
//         $fileContents = file_get_contents($filePath);

//         // Split the contents by new line
//         $lines = explode("\n", $fileContents);

//         $subtitles = [];
//         $subtitle = [];
//         foreach ($lines as $line) {
//             // If line is empty, it means end of one subtitle block
//             if (trim($line) == "") {
//                 if (!empty($subtitle)) {
//                     $subtitles[] = $subtitle;
//                     $subtitle = [];
//                 }
//             } else {
//                 // Add line to the current subtitle block
//                 $subtitle[] = str_replace(array("\r", "\n"), '',$line);
//             }
//         }
        
//         // Add last subtitle block if exists
//         if (!empty($subtitle)) {
//             $subtitles[] = $subtitle;
//         }

//         // Example: Return the parsed subtitles as a JSON response
//         return $subtitles;




//     }











//     public static function convertVideoFormat($inputPath, $outputPath, $format)
//     {
//         try {
//             $ffmpeg = LaravelFFMpeg::fromDisk('public')
//                 ->open($inputPath)
//                 ->export()
//                 ->toDisk('public')
//                 ->inFormat(new \FFMpeg\Format\Video\X264('libmp3lame', 'libx264'))
//                 ->save($outputPath);
//         } catch (\Exception $e) {
//             Log::error('FFmpeg Error: ' . $e->getMessage());
//             throw $e; // Re-throw for debugging purposes
//         }
//     }





public static function generateFrames($inputPath, $frameRate = 1, $width = 512, $outputPath = null, $height = null)
{
try {

        // If $outputPath is not provided, generate a default one
        if (!$outputPath) {
            $outputPath = 'assets/frames/' . (string)Str::uuid() . '/';
        }

        $ffmpeg = LaravelFFMpeg::fromDisk('public')->open($inputPath);

        $duration = $ffmpeg->getDurationInSeconds();


        Storage::disk('public')->makeDirectory($outputPath);

        // Frame counter for consistent naming
        $frameIndex = 0;

        while (true) {
            // Calculate the exact timestamp for this frame
            $seconds = $frameIndex * $frameRate;

            // Break the loop if the calculated time exceeds the duration
            if ($seconds >= $duration) {
                break;
            }

            // Format the frame index for consistent filenames (e.g., 000060, 000061)
            $formattedFrameIndex = str_pad((string)$frameIndex, 6, '0', STR_PAD_LEFT);

            // Use the formatted frame index in the filename
            $outputFullPath = $outputPath . 'frame_' . $formattedFrameIndex . '.jpg';

            // Generate the frame
            self::generateFrame($inputPath, $outputFullPath, $seconds, $width, $height);

            // Increment the frame index
            $frameIndex++;
        }


        return $outputPath;


    } catch (\Exception $e) {
        // Log::error('FFmpeg Error: ' . $e->getMessage());
        throw $e; // Re-throw for debugging purposes
    }
}




    public static function generateFrame($inputPath, $outputPath, $timeInSeconds, $width, $height)
    {





        try {
            $ffmpeg = LaravelFFMpeg::fromDisk('public')
                ->open($inputPath)
                ->getFrameFromSeconds($timeInSeconds)
                ->export()
                ->toDisk('public')
                // ->resize($width, null, function ($constraint) {
                //         $constraint->aspectRatio();
                // })
                ->save($outputPath);


            // Resize the thumbnail while keeping the aspect ratio
            // Create a new ImageManager instance
	        $imageManager = new ImageManager(new Driver()); // Use 'imagick' if ImageMagick is available

	        // Load the extracted frame and resize it
	        $image = $imageManager->read(Storage::disk('public')->path($outputPath))
	            ->scale($width, $height, function ($constraint) {
                    $constraint->aspectRatio(); // Keep the aspect ratio
                })
	            ->save(Storage::disk('public')->path($outputPath));




        } catch (\Exception $e) {

            Log::error('FFmpeg Error: ' . $e->getMessage());
            throw $e; // Re-throw for debugging purposes
        }
    }








// public static function detectSilences($filePath, $silenceDuration = 0.2, $silenceThreshold = -40, $minAudioLength = 1.0)
// {
//     $ffmpegPath = base_path('ffmpeg/bin/ffmpeg');
//     $inputFile = Storage::disk('public')->path($filePath);
//     $txtname = 'audio/silence_times.txt';
//     $silenceTimesFile = Storage::disk('public')->path($txtname);

//     // Build the FFmpeg command
//     $cmd = sprintf(
//         '%s -i %s -af silencedetect=noise=%sdB:d=%s -f null - 2>&1 | findstr /r /c:"silence" > %s',
//         escapeshellarg($ffmpegPath),
//         escapeshellarg($inputFile),
//         escapeshellarg($silenceThreshold),
//         escapeshellarg($silenceDuration),
//         escapeshellarg($silenceTimesFile)
//     );

//     $outputLog = [];
//     $returnCode = 0;
// dd($cmd);
//     // Execute the FFmpeg command
//     exec($cmd, $outputLog, $returnCode);

//     // Log the output and return code
//     Log::info('FFmpeg Command Output: ' . implode("\n", $outputLog));
//     Log::info('FFmpeg Command Return Code: ' . $returnCode);

//     // Read the silence times from the file
//     $silenceTimes = file($silenceTimesFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

//     $filteredSilenceTimes = [];
//     $previousSilenceEnd = 0;

//     foreach ($silenceTimes as $line) {
//         if (preg_match('/silence_start: (\d+\.\d+)/', $line, $startMatches)) {
//             $silenceStart = (float)$startMatches[1];
//             continue; // Continue to the next line to find the silence_end
//         }

//         if (preg_match('/silence_end: (\d+\.\d+)/', $line, $endMatches)) {
//             $silenceEnd = (float)$endMatches[1];
//             $silenceDuration = $silenceEnd - $previousSilenceEnd;

//             // Include only if the resulting audio clip is at least the specified length
//             if ($silenceDuration >= $minAudioLength) {
//                 $filteredSilenceTimes[] = $line;
//                 $previousSilenceEnd = $silenceEnd;
//             }
//         }
//     }

//     // Save the filtered results back to the file
//     file_put_contents($silenceTimesFile, implode("\n", $filteredSilenceTimes));

//     return $txtname;
// }





public static function splitMedia($inputPath, $seconds = null, $outputPath = null, $frame = 30)
{
    // Generate a unique output path for the media file
    $outputPath = $outputPath ?? Storage::disk('local')->path('temp/' . uniqid('splitted_', false) . '.mp4');

    // Define the path to the ffmpeg executable
    $ffmpegPath = env('FFMPEG_BINARIES', 'C:/xampp/htdocs/fragnant-social/ffmpeg/bin/ffmpeg.exe');

    // Build the ffmpeg command
    $resizeSnippetCommand = "\"$ffmpegPath\" -i \"$inputPath\" -an"
    . ($seconds ? " -t $seconds" : "") // Include the `-t` flag only if $seconds is set
    . " -vf \"scale=540:960:force_original_aspect_ratio=decrease,pad=540:960:(ow-iw)/2:(oh-ih)/2,fps=$frame\""
    . " -c:v libx264 \"$outputPath\"";

    // Execute the ffmpeg command
    exec($resizeSnippetCommand, $outputSnippet, $resultCodeSnippet);

    // Return the path to the output file
    return $outputPath;
}




public static function generateConcatList($files = []){

    $outputPath = Storage::disk('local')->path('temp/' . uniqid('concat_', false) . '.txt');


    $concatContent = "";

    foreach ($files as $key => $file) {
         $concatContent .= "file '$file'\n";
    }

    file_put_contents($outputPath, $concatContent);

    return $outputPath;

}


public static function mergeFromConcatList($concatPath){

    $ffmpegPath = env('FFMPEG_BINARIES', 'C:/xampp/htdocs/fragnant-social/ffmpeg/bin/ffmpeg.exe');

    $outputPath = Storage::disk('local')->path('temp/' . uniqid('merged_', false) . '.mp4');

    $concatCommand = "\"$ffmpegPath\" -f concat -safe 0 -i \"$concatPath\" -c:v libx264 -preset fast -crf 23 -an \"$outputPath\"";

    exec($concatCommand, $outputConcat, $resultCodeConcat);

    if ($resultCodeConcat !== 0) {
        throw new \Exception('Failed to concatenate the videos: ' . implode("\n", $outputConcat));
    }

    return $outputPath;

}



public static function replaceAudioFromVideo($audioPath, $videoPath, $outputPath = null){

    $ffmpegPath = env('FFMPEG_BINARIES', 'C:/xampp/htdocs/fragnant-social/ffmpeg/bin/ffmpeg.exe');

    $syncCommand = "\"$ffmpegPath\" -i \"$videoPath\" -i \"$audioPath\" -filter_complex \"[0:v:0][1:a:0]concat=n=1:v=1:a=1[outv][outa]\" -map \"[outv]\" -map \"[outa]\" -c:v libx264 -c:a aac -shortest \"$outputPath\"";

    exec($syncCommand, $outputSync, $resultCodeSync);

    if ($resultCodeSync !== 0) {
        throw new \Exception('Failed to synchronize audio and video: ' . implode("\n", $outputSync));
    }

    return $outputPath;

}


public static function replaceAudioFromVideo($audioPath, $videoPath, $outputPath = null)
{
    $ffmpegPath = env('FFMPEG_BINARIES', 'C:/xampp/htdocs/fragnant-social/ffmpeg/bin/ffmpeg.exe');

    // Construct the FFmpeg command to merge video and audio
    $syncCommand = "\"$ffmpegPath\" -i \"$videoPath\" -i \"$audioPath\" -c:v copy -c:a aac -strict experimental -shortest \"$outputPath\"";

    // Execute the command
    exec($syncCommand, $outputSync, $resultCodeSync);

    // Check for errors
    if ($resultCodeSync !== 0) {
        throw new \Exception('Failed to synchronize audio and video: ' . implode("\n", $outputSync));
    }

    return $outputPath;
}





// filepath = 'assets/clips/micheal.mp4'
public static function detectFrameChangesPython($videoPath, $threshold = 50000, $frameInterval = 0.1)
{
    // Step 1: Generate frames using FFMpeg
    $framesDir = self::generateFrames($videoPath, $frameInterval);

    if (empty($framesDir)) {
        throw new \RuntimeException("Failed to generate frames.");
    }

    // Log progress
    logger()->info("Frames generated at {$framesDir}");

    // Step 2: Analyze frames using Python
    $pythonScriptPath = base_path('python/analyze_frames.py');
    $absoluteFramesDir = Storage::disk('public')->path($framesDir); // Absolute path for Python

    // Run the Python script
    $process = new Process(['python', $pythonScriptPath, $absoluteFramesDir, (string)$threshold]);
    $process->run();

    // Check if the Python script executed successfully
    if (!$process->isSuccessful()) {
        logger()->error("Python script failed with error: {$process->getErrorOutput()}");
        throw new ProcessFailedException($process);
    }

    // Parse the JSON output from the Python script
    $analysisResults = json_decode($process->getOutput(), true);

    if (empty($analysisResults)) {
        logger()->info("No significant changes detected.");
        return [];
    }

    // Filter frames with big changes
    $bigChanges = array_filter($analysisResults, function ($change) use ($threshold) {
        return $change['non_zero_count'] > $threshold;
    });

    if (empty($bigChanges)) {
        logger()->info("No big changes detected above the threshold of {$threshold}.");
        return [];
    }

    // Log and return all significant big changes
    logger()->info("All significant big changes detected:");
    foreach ($bigChanges as $index => $change) {
        logger()->info("Frame: {$change['frame']}, Changes: {$change['non_zero_count']}");
    }

    return $bigChanges;
}







// filepath = 'C:\xampp\htdocs\fragnant-social\storage\app/public/assets/clips/micheal.mp4'
public static function detectFrameChanges($filePath, $threshold = 0.3)
{
    // Define paths
    $ffmpegPath = env('FFMPEG_BINARIES', 'C:/xampp/htdocs/fragnant-social/ffmpeg/bin/ffmpeg.exe');

    $outputDir = dirname($filePath).'/';

    $fileParts = pathinfo($filePath);

    $outputFile = $outputDir . $fileParts['filename'] . '_detected_frame_change.txt';


    // Build the FFMpeg command to detect frame changes
    $cmd = sprintf(
        '%s -i %s -filter:v "select=\'gt(scene,%f)\',showinfo" -f null - 2> %s',
        escapeshellarg($ffmpegPath),
        escapeshellarg($filePath),
        $threshold,
        escapeshellarg($outputFile)
    );

    // Log the command for debugging
    logger()->info("FFMpeg Command: {$cmd}");

    // Execute the FFMpeg command
    $outputLog = [];
    $returnCode = 0;

    exec($cmd, $outputLog, $returnCode);

    // Check for errors
    if ($returnCode !== 0) {
        throw new \RuntimeException("FFMpeg failed with return code {$returnCode}. Command: {$cmd}");
    }


    return $outputFile;

}




public static function readFrameChanges($filePath){

    // Read the output file to get the frame change times
    $frameChanges = [];

    if (file_exists($filePath)) {
        $lines = file($filePath);
        foreach ($lines as $line) {
            if (preg_match('/pts_time:([\d.]+)/', $line, $matches)) {
                $frameChanges[] = floatval($matches[1]);
            }
        }
    }

    return $frameChanges;

}




protected static function getImageManager(): ImageManager
{
    // Create and return an ImageManager instance with the GD driver
    return new ImageManager(new Driver());
}


// input,output path = Storage::disk('public')->path($inputPath)
public static function convertAudioFormat($inputPath, $outputPath)
{
        try {

            $ffmpegPath = env('FFMPEG_BINARIES', 'C:/xampp/htdocs/fragnant-social/ffmpeg/bin/ffmpeg.exe');

            // Ensure the output format settings with fade in and fade out effects
            $process = new Process([
                $ffmpegPath, '-i', $inputPath,
                '-af', 'afade=t=in:ss=0:d=0.5,afade=t=out:st=4.5:d=0.5,silenceremove=1:0:-50dB', // Apply fade in for 0.5 seconds and fade out starting at 4.5 seconds and silence if any empty in the beginning
                '-t', '5', // Limit the duration to 5 seconds
                '-codec:a', 'libmp3lame',  // Use MP3 codec
                '-b:a', '160k',            // Bit rate
                '-ar', '24000',            // Sample rate
                '-ac', '1',                // Number of audio channels (mono)
                $outputPath
            ]);

            // Run the process
            $process->run();

        // Handle errors
        if (!$process->isSuccessful()) {
            throw new \Exception($process->getErrorOutput());
        }

        return $outputPath;
    } catch (\Exception $e) {
        Log::error('FFmpeg Conversion Error: ' . $e->getMessage());
        throw $e; // Re-throw for debugging purposes
    }
}





public static function extractAudio($filePath){

    try {

        // Generate a unique output path for the audio file
        $outputPath = Storage::disk('local')->path('temp/' . uniqid('audio_', false) . '.mp3');

        // Extract audio from the raw input file path
        $ffmpegPath = env('FFMPEG_BINARIES', 'C:/xampp/htdocs/fragnant-social/ffmpeg/bin/ffmpeg.exe');
        
        $extractAudioCommand = "\"$ffmpegPath\" -i \"$filePath\" -q:a 0 -map a \"$outputPath\"";

        exec($extractAudioCommand, $outputAudio, $resultCodeAudio);

        if ($resultCodeAudio !== 0) {
            throw new \Exception('Failed to extract audio: ' . implode("\n", $outputAudio));
        }

        return $outputPath; // Return the full path to the saved audio file

    } catch (\Exception $e) {
        throw new \Exception('Failed to extract audio: ' . $e->getMessage());
    }

}


public static function extractAudioRandomStart($filePath, $duration = 10)
{
    try {
        // Generate a unique output path for the audio file
        $outputPath = Storage::disk('local')->path('temp/' . uniqid('audio_random_st', false) . '.mp3');



        $details = AppHelper::extractFileDetails($filePath);

        // Initialize LaravelFFMpeg
        $ffmpeg = LaravelFFMpeg::fromDisk($details['storage_disk'])->open($details['relative_path']);

        // Get the total duration of the file in seconds
        $totalDuration = $ffmpeg->getDurationInSeconds();

        if (!$totalDuration || $totalDuration <= 10) {
            throw new \Exception('File is too short or duration could not be determined.');
        }

        // Ensure the random start time and clip duration fit within the total duration
        $randomStartTime = rand(10, max(10, $totalDuration - $duration));
        $startTimeFormatted = gmdate("H:i:s", $randomStartTime); // Convert to HH:MM:SS

        // Path to FFmpeg binary
        $ffmpegPath = env('FFMPEG_BINARIES', 'C:/xampp/htdocs/fragnant-social/ffmpeg/bin/ffmpeg.exe');

        // Build the FFmpeg command with the random start time and clip duration
        $extractAudioCommand = "\"$ffmpegPath\" -ss $startTimeFormatted -i \"$filePath\" -t $duration -q:a 0 -map a \"$outputPath\"";

        // Debugging: output the command being executed
        logger()->info("Executing FFmpeg command: $extractAudioCommand");

        // Execute the FFmpeg command
        exec($extractAudioCommand, $outputAudio, $resultCodeAudio);

        if ($resultCodeAudio !== 0) {
            throw new \Exception('Failed to extract audio: ' . implode("\n", $outputAudio));
        }

        return $outputPath; // Return the full path to the saved audio file
    } catch (\Exception $e) {
        throw new \Exception('Failed to extract audio: ' . $e->getMessage());
    }
}



//     public static function convertMp4ToMp3($inputPath, $outputPath, $bitrate = 128){

//         $audioFormat = new \FFMpeg\Format\Audio\Mp3();
//         $audioFormat->setAudioKiloBitrate($bitrate);

//         LaravelFFMpeg::fromDisk('public')
//             ->open($inputPath)
//                 ->export()
//                 ->toDisk('public')
//                 ->inFormat($audioFormat)
//                 ->save($outputPath);

//     }


public static function checkAudioInfo($filePath)
{


    try {
        // Use LaravelFFMpeg to get FFProbe
        $ffprobe = LaravelFFMpeg::getFFProbe();

        // Get the audio stream information using the full file path
        $audioStream = $ffprobe->streams(Storage::disk('public')->path($filePath))->audios()->first();
        
        if (!$audioStream) {
            throw new \Exception("No audio stream found in file: $fullFilePath");
        }

        return $audioStream;

    } catch (\Exception $e) {

        Log::error("Error probing file: {$e->getMessage()}");
        throw $e;
        
    }

}






public static function checkVideoInfo($filePath)
{
    try {
        // Use LaravelFFMpeg to get FFProbe
        $ffprobe = LaravelFFMpeg::getFFProbe();

        // Get the video stream information using the full file path
        $videoStream = $ffprobe->streams(Storage::disk('public')->path($filePath))->videos()->first();

        if (!$videoStream) {
            throw new \Exception("No video stream found in file: $filePath");
        }

        return $videoStream;

    } catch (\Exception $e) {
        Log::error("Error probing file: {$e->getMessage()}");
        throw $e;
    }
}






public static function convertVideoFormat($inputPath, $outputPath, $format = 'mp4')
{
    try {
        LaravelFFMpeg::fromDisk('public')
            ->open($inputPath)
            ->export()
            ->toDisk('public')
            ->inFormat(new \FFMpeg\Format\Video\X264('libmp3lame', 'libx264'))
            ->save($outputPath);

        Log::info("Video converted successfully: {$outputPath}");
    } catch (\Exception $e) {
        Log::error('FFmpeg Video Conversion Error: ' . $e->getMessage());
        throw $e;
    }
}








/**
* Generate a thumbnail from a video at a specific time.
*
* @param string $inputPath
* @param string $outputPath
* @param int $timeInSeconds
* @param int $width
* @throws \Exception
*/

public static function generateThumbnail($inputPath, $outputPath, $timeInSeconds, $width)
{
    try {


        
        // Extract the frame at the specified time
        LaravelFFMpeg::fromDisk('public')
            ->open($inputPath)
            ->getFrameFromSeconds($timeInSeconds)
            ->export()
            ->toDisk('public')
            ->save($outputPath);

        // Resize the thumbnail image while maintaining aspect ratio
        $manager = self::getImageManager();
        $image = $manager->read(Storage::disk('public')->path($outputPath)); // Read the image

        // Calculate the new height based on the aspect ratio
        $originalWidth = $image->width();
        $originalHeight = $image->height();
        $height = intval(($originalHeight / $originalWidth) * $width);

        // Resize the image
        $image->resize($width, $height) // Resize with calculated dimensions
              ->save(Storage::disk('public')->path($outputPath)); // Save the image

        Log::info("Thumbnail generated successfully: {$outputPath}");
    } catch (\Exception $e) {
        Log::error('FFmpeg Thumbnail Generation Error: ' . $e->getMessage());
        throw $e;
    }
}





    /**
     * Compress an image to a specified quality.
     *
     * @param string $inputPath
     * @param string $outputPath
     * @param int $quality
     * @throws \Exception
     */

    public static function compressImage($inputPath, $outputPath, $quality = 75)
    {
        try {
            $manager = self::getImageManager();
            $manager->read(Storage::disk('public')->path($inputPath)) // Read the image
                ->encode('jpg', $quality) // Compress the image
                ->save(Storage::disk('public')->path($outputPath)); // Save the image

            Log::info("Image compressed successfully: {$outputPath}");
        } catch (\Exception $e) {
            Log::error('Image Compression Error: ' . $e->getMessage());
            throw $e;
        }
    }





    /**
     * Compress a video to a lower bitrate.
     *
     * @param string $inputPath
     * @param string $outputPath
     * @param int $bitrate
     * @throws \Exception
     */
    public static function compressVideo($inputPath, $outputPath, $bitrate = 1000)
    {
        try {
            $lowBitrateFormat = new \FFMpeg\Format\Video\X264();
            $lowBitrateFormat->setKiloBitrate($bitrate);

            LaravelFFMpeg::fromDisk('public')
                ->open($inputPath)
                ->export()
                ->toDisk('public')
                ->inFormat($lowBitrateFormat)
                ->save($outputPath);

            Log::info("Video compressed successfully: {$outputPath}");
        } catch (\Exception $e) {
            Log::error('FFmpeg Video Compression Error: ' . $e->getMessage());
            throw $e;
        }
    }





}