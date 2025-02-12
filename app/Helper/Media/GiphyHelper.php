<?php

namespace App\Helper\Media;

use Illuminate\Support\Facades\Http;

class GiphyHelper
{
    /**
     * Search for GIFs on Giphy
     *
     * @param string $query
     * @param int $limit
     * @param int $offset
     * @param string|null $rating
     * @return object
     */
    public static function gifs($query, $limit = 25, $offset = 0, $rating = 'g')
    {
        $response = Http::get('https://api.giphy.com/v1/gifs/search', [
            'api_key' => env('GIPHY_KEY'),
            'q' => $query,
            'limit' => $limit,
            'offset' => $offset,
            'rating' => $rating,
            'lang' => 'en',
        ]);

        return json_decode($response->body());
    }

    /**
     * Search for Stickers on Giphy
     *
     * @param string $query
     * @param int $limit
     * @param int $offset
     * @param string|null $rating
     * @return object
     */
    public static function stickers($query, $limit = 25, $offset = 0, $rating = 'g')
    {
        $response = Http::get('https://api.giphy.com/v1/stickers/search', [
            'api_key' => env('GIPHY_KEY'),
            'q' => $query,
            'limit' => $limit,
            'offset' => $offset,
            'rating' => $rating,
            'lang' => 'en',
        ]);

        return json_decode($response->body());
    }

    /**
     * Get trending GIFs from Giphy
     *
     * @param int $limit
     * @param int $offset
     * @param string|null $rating
     * @return object
     */
    public static function trendingGifs($limit = 25, $offset = 0, $rating = 'g')
    {
        $response = Http::get('https://api.giphy.com/v1/gifs/trending', [
            'api_key' => env('GIPHY_KEY'),
            'limit' => $limit,
            'offset' => $offset,
            'rating' => $rating,
        ]);

        return json_decode($response->body());
    }

    /**
     * Get trending Stickers from Giphy
     *
     * @param int $limit
     * @param int $offset
     * @param string|null $rating
     * @return object
     */
    public static function trendingStickers($limit = 25, $offset = 0, $rating = 'g')
    {
        $response = Http::get('https://api.giphy.com/v1/stickers/trending', [
            'api_key' => env('GIPHY_KEY'),
            'limit' => $limit,
            'offset' => $offset,
            'rating' => $rating,
        ]);

        return json_decode($response->body());
    }
}