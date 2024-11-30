<?php

namespace App\Helper;

use GuzzleHttp\Client;

class SpotifyHelper
{




    
    /**
     * Get Spotify Bearer Token
     *
     * @return string
     */
    public static function spotifyBearerToken()
    {
        $client = new Client();

        $response = $client->request(
            'POST',
            'https://accounts.spotify.com/api/token',
            [
                'auth' => [
                    env('SPOTIFY_CLIENT_ID'), 
                    env('SPOTIFY_CLIENT_SECRET')
                ],
                'form_params' => [
                    'grant_type' => 'client_credentials',
                ],
            ]
        );

        return json_decode($response->getBody())->access_token;
    }

    /**
     * Search for Spotify Tracks
     *
     * @param string $query
     * @param string $type
     * @param int $limit
     * @return object
     */
    public static function searchTracks($query, $type = 'track', $limit = 10)
    {
        $client = new Client();

        $response = $client->request(
            'GET',
            'https://api.spotify.com/v1/search',
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . self::spotifyBearerToken(),
                ],
                'query' => [
                    'q' => $query,
                    'type' => $type,
                    'limit' => $limit,
                ],
            ]
        );

        return json_decode($response->getBody());
    }

    /**
     * Get Spotify Track Details
     *
     * @param string $trackId
     * @return object
     */
    public static function getTrackDetails($trackId)
    {
        $client = new Client();

        $response = $client->request(
            'GET',
            "https://api.spotify.com/v1/tracks/{$trackId}",
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . self::spotifyBearerToken(),
                ],
            ]
        );

        return json_decode($response->getBody());
    }












}