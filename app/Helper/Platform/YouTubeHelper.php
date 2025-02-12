<?php


namespace App\Helper\Platform;

use Google_Client;
use Google_Service_YouTube;
use Google_Service_YouTube_Video;
use Google_Service_YouTube_VideoSnippet;
use Google_Service_YouTube_VideoStatus;
use Illuminate\Support\Facades\Http;
use App\Models\GoogleToken;
use Carbon\Carbon;

class YouTubeHelper
{
    protected static function getClient($token)
    {
        // Initialize the Google Client
        $client = new Google_Client();
        $client->setClientId(env('GOOGLE_CLIENT_ID'));
        $client->setClientSecret(env('GOOGLE_CLIENT_SECRET'));

        if ($token) {
            // Check if the token is expired or not
            if ($token->expires_at < Carbon::now()) {
                // If the token is expired, refresh it
                $client->refreshToken($token->refresh_token);
                $newToken = $client->getAccessToken();

                // Update the token in the database
                $token->update([
                    'access_token' => $newToken['access_token'],
                    'expires_at' => now()->addSeconds($newToken['expires_in']),
                ]);

                // Set the refreshed access token in the client
                $client->setAccessToken($newToken);
            } else {
                // Set the existing access token in the client
                $client->setAccessToken($token->access_token);
            }
        }

        return $client;
    }

    public static function getChannelInfo($token)
    {
        $client = self::getClient($token);
        $youtube = new Google_Service_YouTube($client);

        $channelsResponse = $youtube->channels->listChannels('snippet,contentDetails,statistics', [
            'mine' => 'true',
        ]);

        return $channelsResponse;
    }

    public static function getChannelVideos($token, $channelId)
    {
        $client = self::getClient($token);
        $youtube = new Google_Service_YouTube($client);

        $videosResponse = $youtube->search->listSearch('snippet', [
            'channelId' => $channelId,
            'maxResults' => 50,
            'order' => 'date',
        ]);

        return $videosResponse;
    }

    public static function getVideoDetails($token, $videoId)
    {
        $client = self::getClient($token);
        $youtube = new Google_Service_YouTube($client);

        $videoResponse = $youtube->videos->listVideos('snippet,contentDetails,statistics', [
            'id' => $videoId,
        ]);

        return $videoResponse;
    }

    public static function uploadVideo($token, $videoPath, $thumbnailPath = null, $title = 'Test Video', $description = 'This is a test video upload via YouTube API', $tags = ['test', 'API', 'YouTube'], $privacyStatus = 'private', $madeForKids = false)
    {    
        set_time_limit(300); // Increase the maximum execution time to 300 seconds (5 minutes)

        $client = self::getClient($token);
        $youtube = new Google_Service_YouTube($client);

        // Step 1: Upload the video
        $video = new Google_Service_YouTube_Video();
        $snippet = new Google_Service_YouTube_VideoSnippet();
        $snippet->setTitle($title);
        $snippet->setDescription($description);
        $snippet->setTags($tags);
        $video->setSnippet($snippet);

        $status = new Google_Service_YouTube_VideoStatus();
        $status->setPrivacyStatus($privacyStatus); // 'public', 'private', or 'unlisted'
        $status->setMadeForKids($madeForKids);
        $video->setStatus($status);

        $response = $youtube->videos->insert('snippet,status', $video, [
            'data' => file_get_contents($videoPath),
            'mimeType' => 'video/*',
            'uploadType' => 'multipart'
        ]);

        // Step 2: Upload the thumbnail if provided
        $thumbnailResponse = null;
        if ($thumbnailPath) {
            $videoId = $response['id'];
            $thumbnailResponse = $youtube->thumbnails->set($videoId, [
                'data' => file_get_contents($thumbnailPath),
                'mimeType' => 'image/*',
                'uploadType' => 'multipart'
            ]);
        }

        return [
            'videoResponse' => $response,
            'thumbnailResponse' => $thumbnailResponse
        ];
    }
}