<?php

namespace App\Helper\Editor;

use Storage;



class StructureHelper
{

  

    




    public static function init($projectName = "Untitled Project", $resolution = "1080x1920", $frameRate = 24, $outputPath = "./output", $customData = [])
    {

      $outputPath = Storage::disk('local')->path('temp');

        return [
            "project" => [
                "name" => $projectName,
                "resolution" => $resolution,
                "frame_rate" => $frameRate,
                "duration" => "00:00:00", // No duration yet
                "metadata" => [
                    "author" => "Unknown",
                    "created_at" => date("Y-m-d H:i:s"),
                    "last_modified" => date("Y-m-d H:i:s")
                ],
                "timeline" => [
                    "tracks" => [
                        "video" => [], // Empty video tracks
                        "audio" => [], // Empty audio tracks
                        "image" => [], // Empty image tracks
                        "text" => [] // Empty text tracks (including subtitles)
                    ]
                ],
                "markers" => [], // No markers yet
                "effects" => [], // Global project-wide effects
                "export_settings" => [
                    "format" => "MP4",
                    "bitrate" => "10Mbps",
                    "audio_codec" => "AAC",
                    "video_codec" => "H.264",
                    "output_path" => $outputPath
                ],
                "custom_data" => $customData // Custom user-defined array
            ]
        ];
    }

    public static function addTrack(&$project, $type, $trackId)
    {
        if (!isset($project['project']['timeline']['tracks'][$type])) {
            return false;
        }
        
        $project['project']['timeline']['tracks'][$type][] = [
            "track_id" => $trackId,
            "clips" => []
        ];
        return true;
    }

    public static function addClip(&$project, $type, $trackId, $clipId, $filePath, $startTime, $endTime, $position, $effects = [], $opacity = 1.0, $scale = 1.0, $position_x = 0, $position_y = 0, $customData = [])
    {
        foreach ($project['project']['timeline']['tracks'][$type] as &$track) {
            if ($track['track_id'] == $trackId) {
                $track['clips'][] = [
                    "clip_id" => $clipId,
                    "file_path" => $filePath,
                    "start_time" => $startTime,
                    "end_time" => $endTime,
                    "position" => $position,
                    "opacity" => $opacity,
                    "scale" => $scale,
                    "position_x" => $position_x,
                    "position_y" => $position_y,
                    "effects" => $effects,
                    "custom_data" => $customData
                ];
                return true;
            }
        }
        return false;
    }

    public static function addText(&$project, $trackId, $clipId, $content, $startTime, $endTime, $position, $effects = [], $isSubtitle = false, $customData = [])
    {
        foreach ($project['project']['timeline']['tracks']['text'] as &$track) {
            if ($track['track_id'] == $trackId) {
                $track['clips'][] = [
                    "clip_id" => $clipId,
                    "content" => $content,
                    "start_time" => $startTime,
                    "end_time" => $endTime,
                    "position" => $position,
                    "effects" => $effects,
                    "is_subtitle" => $isSubtitle, // Differentiates subtitles from regular text
                    "custom_data" => $customData
                ];
                return true;
            }
        }
        return false;
    }












public static function generateFFmpegCommand($project)
{
    $outputPath = rtrim($project['project']['export_settings']['output_path'], "/\\") . "\\generated_video.mp4";
    $ffmpegPath = env('FFMPEG_BINARIES') ?: 'C:\\xampp\\htdocs\\prixsel\\ffmpeg\\bin\\ffmpeg.exe';

    $videoInputs = [];
    $audioInputs = [];
    $filterComplex = [];
    $mapCommands = [];
    $inputIndex = 0;

    // Get Video Tracks
    $videoIndex = 0;
    foreach ($project['project']['timeline']['tracks']['video'] as $track) {
        foreach ($track['clips'] as $clip) {
            $filePath = str_replace('/', '\\', $clip['file_path']); // Fix Windows Path Issues
            $startTime = $clip['start_time'];
            $endTime = $clip['end_time'];
            $duration = strtotime($endTime) - strtotime($startTime);

            $videoInputs[] = "-i \"$filePath\"";

            // Handle image clips properly by overlaying them onto the video
            if (preg_match('/\.(jpg|jpeg|png)$/i', $clip['file_path'])) {
                $filterComplex[] = "[{$inputIndex}:v]scale=1080:1920[scaled$videoIndex];";
                $filterComplex[] = "[0:v][scaled$videoIndex] overlay=0:0:enable='between(t,{$clip['position']},{$endTime})'[video_out$videoIndex];";
                $mapCommands[] = "-map [video_out$videoIndex]";
            } else {
                $mapCommands[] = "-map $inputIndex:v";
            }

            $videoIndex++;
            $inputIndex++;
        }
    }

    // Get Audio Tracks
    foreach ($project['project']['timeline']['tracks']['audio'] as $track) {
        foreach ($track['clips'] as $clip) {
            $filePath = str_replace('/', '\\', $clip['file_path']); // Fix Windows Path Issues
            $audioInputs[] = "-i \"$filePath\"";
            $mapCommands[] = "-map $inputIndex:a"; 
            $inputIndex++;
        }
    }

    // Build Final FFmpeg Command
    $command = "$ffmpegPath ";
    $command .= implode(" ", $videoInputs) . " ";
    $command .= implode(" ", $audioInputs) . " ";
    
    if (!empty($filterComplex)) {
        $command .= "-filter_complex \"" . implode(" ", $filterComplex) . "\" ";
    }
    
    $command .= implode(" ", $mapCommands) . " ";
    $command .= "-c:v libx264 -preset fast -crf 23 -c:a aac -b:a 192k -shortest ";
    $command .= "\"$outputPath\"";

    return $command;

    // Execute FFmpeg Command
    exec($command, $output, $returnCode);

    if ($returnCode !== 0) {
        return "Error processing FFmpeg: " . implode("\n", $output);
    }

    return "Video generated successfully at $outputPath";
}





// {
//   "project": {
//     "name": "Multi-Layer Video Project",
//     "resolution": "1920x1080",
//     "frame_rate": 30.0,
//     "duration": 10.29,
//     "metadata": {
//       "author": "Unknown",
//       "created_at": "2025-01-19 12:00:00",
//       "last_modified": "2025-01-19 12:05:00"
//     },
//     "timeline": {
//       "tracks": {
//         "video": [
//           {
//             "track_id": 1,
//             "clips": [
//               {
//                 "clip_id": "v1",
//                 "file_path": "/mnt/data/base_video.mp4",
//                 "start_time": "00:00:00",
//                 "end_time": "00:05:14",
//                 "position": "00:00:00",
//                 "effects": []
//               }
//             ]
//           },
//           {
//             "track_id": 2,
//             "clips": [
//               {
//                 "clip_id": "overlay1",
//                 "file_path": "/mnt/data/overlay_video.mp4",
//                 "start_time": "00:00:02",
//                 "end_time": "00:04:00",
//                 "position": "00:00:02",
//                 "opacity": 0.7,
//                 "scale": 0.4,
//                 "position_x": 1500,
//                 "position_y": 900,
//                 "effects": [
//                   {
//                     "type": "fade_in",
//                     "duration": "00:00:01"
//                   }
//                 ]
//               }
//             ]
//           },
//           {
//             "track_id": 3,
//             "clips": [
//               {
//                 "clip_id": "v2",
//                 "file_path": "/mnt/data/second_part.mp4",
//                 "start_time": "00:05:14",
//                 "end_time": "00:10:29",
//                 "position": "00:05:14",
//                 "effects": []
//               }
//             ]
//           }
//         ],
//         "audio": [
//           {
//             "track_id": 1,
//             "clips": [
//               {
//                 "clip_id": "a1",
//                 "file_path": "/mnt/data/background_audio.mp3",
//                 "start_time": "00:00:00",
//                 "end_time": "00:10:29",
//                 "position": "00:00:00",
//                 "effects": []
//               }
//             ]
//           }
//         ],
//         "text": [
//           {
//             "track_id": 4,
//             "clips": [
//               {
//                 "clip_id": "txt1",
//                 "content": "Overlay Video Demo",
//                 "start_time": "00:00:02",
//                 "end_time": "00:00:10",
//                 "position": "00:00:02",
//                 "effects": [
//                   {
//                     "type": "fade_in",
//                     "duration": "00:00:01"
//                   }
//                 ]
//               }
//             ]
//           }
//         ]
//       }
//     },
//     "export_settings": {
//       "format": "MP4",
//       "bitrate": "10Mbps",
//       "audio_codec": "AAC",
//       "video_codec": "H.264"
//     }
//   }
// }






}








