<?php

namespace App\Helper;

use Illuminate\Support\Facades\Http;

class AudiusHelper
{


    // Fetch a track's details by ID
    public static function getTrackDetails($trackId)
    {
        $response = Http::get("https://dn1.nodeoperator.io/v1/tracks/{$trackId}");

        return json_decode($response->getBody(), true);
    }

    // Get trending tracks
    public static function getTrendingTracks($genre = null, $timeRange = null)
    {
        $query = [];
        if ($genre) {
            $query['genre'] = $genre;
        }
        if ($timeRange) {
            $query['time'] = $timeRange;
        }

        $response = Http::get("https://dn1.nodeoperator.io/v1/tracks/trending", $query);

        return json_decode($response->getBody(), true);
    }

    // Stream a track by CID
    public static function streamTrack($trackCID)
    {
        return "https://dn1.nodeoperator.io/v1/tracks/{$trackCID}/stream";
    }

    // Search for tracks or users
    public static function search($query, $type = 'all')
    {
        $response = Http::get("https://dn1.nodeoperator.io/v1/tracks/search", [
            'query' => $query,
            'type' => $type
        ]);

        return json_decode($response->getBody(), true);
    }


}