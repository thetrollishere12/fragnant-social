<?php

namespace App\Helper;

use Illuminate\Support\Facades\Http;

class HeygenHelper
{
    // Fetch the list of avatars
    public static function listAvatars()
    {
        $response = Http::withHeaders([
            'X-Api-Key' => env('HEYGEN_API_KEY')
        ])->get("https://api.heygen.com/v1/avatars");

        return json_decode($response->getBody(), true);
    }

    // Create an avatar video
    public static function createAvatarVideo($avatarId, $voiceId, $text, $backgroundColor = "#FFFFFF", $width = 1280, $height = 720)
    {
        $response = Http::withHeaders([
            'X-Api-Key' => env('HEYGEN_API_KEY'),
            'Content-Type' => 'application/json'
        ])->post("https://api.heygen.com/v2/video/generate", [
            "video_inputs" => [
                [
                    "character" => [
                        "type" => "avatar",
                        "avatar_id" => $avatarId,
                        "avatar_style" => "normal"
                    ],
                    "voice" => [
                        "type" => "text",
                        "input_text" => $text,
                        "voice_id" => $voiceId
                    ],
                    "background" => [
                        "type" => "color",
                        "value" => $backgroundColor
                    ]
                ]
            ],
            "dimension" => [
                "width" => $width,
                "height" => $height
            ],
            "aspect_ratio" => "16:9",
            "test" => true
        ]);

        return json_decode($response->getBody(), true);
    }

    // Check the status of a video
    public static function getVideoStatus($videoId)
    {
        $response = Http::withHeaders([
            'X-Api-Key' => env('HEYGEN_API_KEY')
        ])->get("https://api.heygen.com/v1/video_status.get", [
            'video_id' => $videoId
        ]);

        return json_decode($response->getBody(), true);
    }
} 