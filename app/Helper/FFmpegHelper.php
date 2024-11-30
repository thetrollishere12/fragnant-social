<?php

namespace App\Helper;


use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg as LaravelFFMpeg;
use Storage;
use Illuminate\Support\Facades\Log;

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

use Illuminate\Support\Str;
use Symfony\Component\Process\Process;


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



// public static function convertAudioFormat($inputPath, $outputPath)
// {
//         try {
//         $ffmpegPath = 'C:/xampp/htdocs/contentplanner/ffmpeg/bin/ffmpeg.exe';

//         // Ensure the output format settings with fade in and fade out effects
//         $process = new Process([
//             $ffmpegPath, '-i', Storage::disk('public')->path($inputPath),
//             '-af', 'afade=t=in:ss=0:d=0.5,afade=t=out:st=4.5:d=0.5,silenceremove=1:0:-50dB', // Apply fade in for 0.5 seconds and fade out starting at 4.5 seconds and silence if any empty in the beginning
//             '-t', '5', // Limit the duration to 5 seconds
//             '-codec:a', 'libmp3lame',  // Use MP3 codec
//             '-b:a', '160k',            // Bit rate
//             '-ar', '24000',            // Sample rate
//             '-ac', '1',                // Number of audio channels (mono)
//             Storage::disk('public')->path($outputPath)
//         ]);

//         // Run the process
//         $process->run();

//         // Handle errors
//         if (!$process->isSuccessful()) {
//             throw new \Exception($process->getErrorOutput());
//         }

//         return $outputPath;
//     } catch (\Exception $e) {
//         Log::error('FFmpeg Conversion Error: ' . $e->getMessage());
//         throw $e; // Re-throw for debugging purposes
//     }
// }







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



//     public static function generateFrames($inputPath, $frameRate = 1, $width = 512)
//     {
//         try {
//             $ffmpeg = LaravelFFMpeg::fromDisk('public')->open($inputPath);
//             $duration = $ffmpeg->getDurationInSeconds();
//             $framesDirectory = 'frames/'.Str::uuid().'/';
//             Storage::disk('public')->makeDirectory($framesDirectory);

//             for ($seconds = 0; $seconds < $duration; $seconds += $frameRate) {
//                 $outputPath = $framesDirectory . 'frame_' . $seconds . '.jpg';
//                 self::generateFrame($inputPath, $outputPath, $seconds, $width);
//             }

//             return $framesDirectory;

//         } catch (\Exception $e) {
//             Log::error('FFmpeg Error: ' . $e->getMessage());
//             throw $e; // Re-throw for debugging purposes
//         }
//     }




//     public static function generateFrame($inputPath, $outputPath, $timeInSeconds, $width)
//     {





//         try {
//             $ffmpeg = LaravelFFMpeg::fromDisk('public')
//                 ->open($inputPath)
//                 ->getFrameFromSeconds($timeInSeconds)
//                 ->export()
//                 ->toDisk('public')
//                 // ->resize($width, null, function ($constraint) {
//                 //         $constraint->aspectRatio();
//                 // })
//                 ->save($outputPath);

//             // Resize the thumbnail while keeping the aspect ratio
//             $image = Image::make(Storage::disk('public')->get($outputPath))
//                 ->resize($width, null, function ($constraint) {
//                     $constraint->aspectRatio();
//                 })
//                 ->save(Storage::disk('public')->path($outputPath));




//         } catch (\Exception $e) {

//             Log::error('FFmpeg Error: ' . $e->getMessage());
//             throw $e; // Re-throw for debugging purposes
//         }
//     }



//     public static function compressVideo($inputPath, $outputPath, $bitrate = 1000)
//     {
//         $lowBitrateFormat = new \FFMpeg\Format\Video\X264();
//         $lowBitrateFormat->setKiloBitrate($bitrate);

//         LaravelFFMpeg::fromDisk('public')
//             ->open($inputPath)
//             ->export()
//             ->toDisk('public')
//             ->inFormat($lowBitrateFormat)
//             ->save($outputPath);
//     }


//     public static function compressImage($inputPath, $outputPath, $quality = 75)
//     {

//         $image = Image::make(Storage::disk('public')->get($inputPath))
//         ->save(Storage::disk('public')->path($outputPath), $quality);

//     }


//     // Test out



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









//     public static function splitMediaByTxt($audioPath, $logPath)
//     {
//     $outputDir = 'public/splitted_silence_media/';
//     $fileParts = pathinfo($audioPath);
//     $outputPattern = $outputDir . $fileParts['filename'] . '_%03d.' . $fileParts['extension'];

//     // Make sure the output directory exists
//     if (!Storage::exists($outputDir)) {
//         Storage::makeDirectory($outputDir);
//     }

//     // Build full paths for input and output files
//     $ffmpegPath = base_path('ffmpeg/bin/ffmpeg');
//     $inputFile = Storage::disk('public')->path($audioPath);

//     // Parse the log file to get silence end times
//     $logContents = Storage::disk('public')->get($logPath);
//     preg_match_all('/silence_end: (\d+(\.\d+)?)/', $logContents, $matches);
//     $silenceEndTimes = $matches[1];

//     // Generate the segments from silence end times
//     $segments = [];
//     $start_time = 0;
//     foreach ($silenceEndTimes as $end_time) {
//         $segments[] = ['start' => $start_time, 'end' => $end_time];
//         $start_time = $end_time;
//     }
//     $segments[] = ['start' => $start_time, 'end' => null];

//     // Split the audio based on the determined segments
//     foreach ($segments as $index => $segment) {
//         $start = $segment['start'];
//         $end = $segment['end'];
//         $outputFile = sprintf("%s%s_%03d.%s", Storage::path($outputDir), $fileParts['filename'], $index, $fileParts['extension']);
//         $endOption = $end ? "-to " . escapeshellarg($end) : "";

//         // Specify codecs explicitly
//         $splitCommand = sprintf(
//             '%s -i %s -ss %s %s -c:v libx264 -c:a aac %s',
//             escapeshellarg($ffmpegPath),
//             escapeshellarg($inputFile),
//             escapeshellarg($start),
//             $endOption,
//             escapeshellarg($outputFile)
//         );

//         // Execute the command
//         exec($splitCommand, $output, $return_var);
//         if ($return_var !== 0) {
//             dd('false');
//         }
//     }

//     // Helper function to convert seconds to 00:00 format
//     $convertToTimeFormat = function ($seconds) {
//         $minutes = floor($seconds / 60);
//         $seconds = $seconds % 60;
//         return sprintf('%02d:%02d', $minutes, $seconds);
//     };

//     // Return the list of split audio files and their timestamps
//     $files = glob(Storage::path($outputDir . $fileParts['filename'] . '_*.' . $fileParts['extension']));
//     $fileData = array_map(function ($file, $index) use ($segments, $convertToTimeFormat) {
//         $start = $convertToTimeFormat($segments[$index]['start']);
//         $end = $segments[$index]['end'] ? $convertToTimeFormat($segments[$index]['end']) : $convertToTimeFormat($segments[$index]['start']);
//         return [
//             'url' => str_replace(Storage::path('public'), '', $file),
//             'start_time' => $start,
//             'end_time' => $end,
//         ];
//     }, $files, array_keys($files));

//     // Filter out segments where start_time is equal to end_time or duration is 1 second or less
//     $fileData = array_filter($fileData, function ($segment) {
//         $start_time = strtotime($segment['start_time']);
//         $end_time = strtotime($segment['end_time']);
//         return $start_time !== $end_time && ($end_time - $start_time) > 1;
//     });

//     return [
//         'status' => 'success',
//         'files' => array_values($fileData), // Reset array keys
//     ];
//     }




//     public static function mergeMedia($inputFiles, $outputPath)
//     {
//         try {
//             $temporaryListFile = 'temp_merge_list.txt';

//             // Create the file list for FFmpeg
//             $fileListContent = '';
//             foreach ($inputFiles as $inputFile) {
//                 $fileListContent .= "file '" . $inputFile . "'\n";
//             }

//             // Save the list to a temporary file
//             Storage::disk('local')->put($temporaryListFile, $fileListContent);

//             // Build the FFmpeg command to concatenate files
//             $ffmpegPath = base_path('ffmpeg/bin/ffmpeg');
//             $fileListPath = Storage::disk('local')->path($temporaryListFile);
//             $outputFilePath = Storage::disk('public')->path($outputPath);

//             $cmd = sprintf(
//                 '%s -f concat -safe 0 -i %s -c copy %s',
//                 escapeshellarg($ffmpegPath),
//                 escapeshellarg($fileListPath),
//                 escapeshellarg($outputFilePath)
//             );

//             // Execute the command
//             exec($cmd, $outputLog, $returnCode);

//             // Log the output and return code
//             Log::info('FFmpeg Command Output: ' . implode("\n", $outputLog));
//             Log::info('FFmpeg Command Return Code: ' . $returnCode);

//             // Clean up the temporary file
//             // Storage::disk('local')->delete($temporaryListFile);

//             if ($returnCode === 0) {
//                 dd(Storage::url($outputPath)); // Return the URL to the merged file
//             } else {
//                 throw new \Exception('FFmpeg merge failed.');
//             }
//         } catch (\Exception $e) {
//             dd($e->getMessage());
//             Log::error('FFmpeg Error: ' . $e->getMessage());
//             throw $e; // Re-throw for debugging purposes
//         }
//     }






// public static function mergeMediaWithAdditions($audioSections, $outputPath)
// {
//     try {

//         $processedFiles = [];
//         $ffmpegPath = base_path('ffmpeg/bin/ffmpeg');

//         // foreach ($audioSections as $index => $mediaPaths) {

//         //     $audioFile = null;
//         //     $videoFile = null;

//         //     // Iterate through mediaPaths to find audio and video files
//         //     foreach ($mediaPaths as $mediaPath) {
//         //         if (preg_match('/\.(mp3|wav|m4a)$/i', $mediaPath)) {
//         //             $audioFile = $mediaPath;
//         //         } elseif (preg_match('/\.(mp4|mov|mkv|avi)$/i', $mediaPath)) {
//         //             $videoFile = $mediaPath;
//         //         }
//         //     }

//         //     if ($audioFile && $videoFile) {
//         //         $audioFilePath = $audioFile;
//         //         $videoFilePath = $videoFile;
//         //         $sectionOutputFilePath = Storage::disk('local')->path('section_' . $index . '.mp4');

//         //         $cmd = sprintf(
//         //             '%s -i %s -i %s -c:v libx264 -c:a aac -b:a 192k -map 0:v:0 -map 1:a:0 -shortest %s',
//         //             escapeshellarg($ffmpegPath),
//         //             escapeshellarg($videoFilePath),
//         //             escapeshellarg($audioFilePath),
//         //             escapeshellarg($sectionOutputFilePath)
//         //         );



//         //         exec($cmd, $outputLog, $returnCode);

//         //         if ($returnCode !== 0) {
//         //             throw new \Exception('FFmpeg merge failed for section: ' . $index);
//         //         }

//         //         $processedFiles[] = $sectionOutputFilePath;
//         //     } elseif ($audioFile) {
//         //         $audioFilePath = $audioFile;
//         //         $videoFilePath = "https://videos.pexels.com/video-files/5544312/5544312-sd_640_360_24fps.mp4"; // Assuming the video file is a URL
//         //         $sectionOutputFilePath = Storage::disk('local')->path('section_' . $index . '.mp4');

//         //         $cmd = sprintf(
//         //             '%s -i %s -i %s -c:v libx264 -c:a aac -b:a 192k -map 0:v:0 -map 1:a:0 -shortest %s',
//         //             escapeshellarg($ffmpegPath),
//         //             escapeshellarg($videoFilePath),
//         //             escapeshellarg($audioFilePath),
//         //             escapeshellarg($sectionOutputFilePath)
//         //         );

//         //         exec($cmd, $outputLog, $returnCode);

//         //         if ($returnCode !== 0) {
//         //             throw new \Exception('FFmpeg processing failed for audio section: ' . $index);
//         //         }

//         //         $processedFiles[] = $sectionOutputFilePath;
//         //     } elseif ($videoFile) {
//         //         $processedFiles[] = $videoFile;
//         //     }

//         // }

//         // Combine all sections into the final output


//         $finalListFile = 'final_merge_list.txt';
//         // $finalFileListContent = '';
//         // foreach ($processedFiles as $processedFile) {
//         //     $finalFileListContent .= "file '" . $processedFile . "'\n";
//         // }

//         // Storage::disk('local')->put($finalListFile, $finalFileListContent);

//         $finalListPath = Storage::disk('local')->path($finalListFile);
//         $finalOutputFilePath = Storage::disk('public')->path($outputPath);

//         $cmd = sprintf(
//             '%s -f concat -safe 0 -i %s -c copy %s',
//             escapeshellarg($ffmpegPath),
//             escapeshellarg($finalListPath),
//             escapeshellarg($finalOutputFilePath)
//         );

//         dd($cmd);

//         exec($cmd, $outputLog, $returnCode);

//         // Storage::disk('local')->delete($finalListFile);
//         foreach ($processedFiles as $processedFile) {
//             Storage::disk('local')->delete($processedFile);
//         }

//         if ($returnCode === 0) {
//             return Storage::url($outputPath);
//         } else {
//             throw new \Exception('FFmpeg merge failed.');
//         }
//     } catch (\Exception $e) {
//         Log::error('FFmpeg Error: ' . $e->getMessage());
//         throw $e;
//     }
// }












// // public static function detectFrameChanges($filePath, $threshold = 0.4)
// // {
// //     $ffmpegPath = base_path('ffmpeg/bin/ffmpeg');
// //     $outputDir = 'public/splitted_frames/';
// //     $fileParts = pathinfo($filePath);
// //     $outputFile = $outputDir . $fileParts['filename'] . '_changes.txt';

// //     // Make sure the output directory exists
// //         if (!Storage::exists($outputDir)) {
// //             Storage::makeDirectory($outputDir);
// //         }

// //     // Build the FFMpeg command to detect frame changes
// //     $cmd = sprintf(
// //         '%s -i %s -filter:v "select=\'gt(scene,%f)\',showinfo" -f null - 2> %s',
// //         escapeshellarg($ffmpegPath),
// //         escapeshellarg(storage_path('app/public/' . $filePath)),
// //         $threshold,
// //         escapeshellarg(public_path($outputFile))
// //     );

// //     $outputLog = [];
// //     $returnCode = 0;

// //     // Execute the FFMpeg command
// //     exec($cmd, $outputLog, $returnCode);


// //     // Read the output file to get the frame change times
// //     $frameChanges = [];
// //     if (file_exists(public_path($outputFile))) {
// //         $lines = file(public_path($outputFile));
// //         foreach ($lines as $line) {
// //             if (preg_match('/pts_time:([\d.]+)/', $line, $matches)) {
// //                 $frameChanges[] = floatval($matches[1]);
// //             }
// //         }
// //     }

// //     return $frameChanges;
// // }



    protected static function getImageManager(): ImageManager
    {
        // Create and return an ImageManager instance with the GD driver
        return new ImageManager(new Driver());
    }


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