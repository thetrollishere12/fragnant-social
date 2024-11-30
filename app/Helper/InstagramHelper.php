<?php

namespace App\Helper;

use Illuminate\Support\Facades\Http;

class InstagramHelper
{
    /**
     * Get Instagram access token.
     * Exchanges the authorization code for an access token.
     */
    public static function getAccessToken($code)
    {
        $response = Http::asForm()->post('https://api.instagram.com/oauth/access_token', [
            'client_id' => env('INSTAGRAM_CLIENT_ID'),
            'client_secret' => env('INSTAGRAM_CLIENT_SECRET'),
            'grant_type' => 'authorization_code',
            'redirect_uri' => env('INSTAGRAM_REDIRECT_URI'),
            'code' => $code,
        ]);

        return $response->json();
    }

    /**
     * Exchange short-lived token for a long-lived token.
     */
    public static function getLongLivedToken($shortLivedToken)
    {
        $response = Http::get('https://graph.instagram.com/access_token', [
            'grant_type' => 'ig_exchange_token',
            'client_secret' => env('INSTAGRAM_CLIENT_SECRET'),
            'access_token' => $shortLivedToken,
        ]);

        return $response->json();
    }

    /**
     * Refresh long-lived token to extend its expiration.
     */
    public static function refreshLongLivedToken($longLivedToken)
    {
        $response = Http::get('https://graph.instagram.com/refresh_access_token', [
            'grant_type' => 'ig_refresh_token',
            'access_token' => $longLivedToken,
        ]);

        return $response->json();
    }

    /**
     * Get Instagram User Profile.
     */
    public static function getUserProfile($accessToken)
    {
        $response = Http::get("https://graph.instagram.com/me", [
            'fields' => 'id,username,account_type,media_count',
            'access_token' => $accessToken,
        ]);

        return $response->json();
    }

    /**
     * Get User Media.
     */
    public static function getUserMedia($accessToken)
    {
        $response = Http::get("https://graph.instagram.com/me/media", [
            'fields' => 'id,caption,media_type,media_url,permalink,thumbnail_url,timestamp',
            'access_token' => $accessToken,
        ]);

        return $response->json();
    }

    /**
     * Get Media Details by Media ID.
     */
    public static function getMediaDetails($mediaId, $accessToken)
    {
        $response = Http::get("https://graph.instagram.com/{$mediaId}", [
            'fields' => 'id,caption,media_type,media_url,permalink,thumbnail_url,timestamp',
            'access_token' => $accessToken,
        ]);

        return $response->json();
    }

    /**
     * Publish a Photo Post.
     */
    public static function publishMedia($accessToken, $imageUrl, $caption)
    {
        $response = Http::post("https://graph.facebook.com/v21.0/me/media", [
            'image_url' => $imageUrl,
            'caption' => $caption,
            'access_token' => $accessToken,
        ]);

        $mediaId = $response->json()['id'] ?? null;

        if ($mediaId) {
            return self::publishContent($accessToken, $mediaId);
        }

        return $response->json();
    }

    /**
     * Publish Content by Media ID.
     */
    private static function publishContent($accessToken, $mediaId)
    {
        $response = Http::post("https://graph.facebook.com/v21.0/me/media_publish", [
            'creation_id' => $mediaId,
            'access_token' => $accessToken,
        ]);

        return $response->json();
    }

    /**
     * Fetch Comments for a Media.
     */
    public static function getComments($mediaId, $accessToken)
    {
        $response = Http::get("https://graph.facebook.com/v21.0/{$mediaId}/comments", [
            'access_token' => $accessToken,
        ]);

        return $response->json();
    }

    /**
     * Delete a Comment by Comment ID.
     */
    public static function deleteComment($commentId, $accessToken)
    {
        $response = Http::delete("https://graph.facebook.com/v21.0/{$commentId}", [
            'access_token' => $accessToken,
        ]);

        return $response->json();
    }

    /**
     * Reply to a Comment.
     */
    public static function replyToComment($commentId, $message, $accessToken)
    {
        $response = Http::post("https://graph.facebook.com/v21.0/{$commentId}/replies", [
            'message' => $message,
            'access_token' => $accessToken,
        ]);

        return $response->json();
    }

    /**
     * Send a Direct Message.
     */
    public static function sendMessage($recipientId, $message, $accessToken)
    {
        $response = Http::post("https://graph.facebook.com/v21.0/me/messages", [
            'recipient' => ['id' => $recipientId],
            'message' => ['text' => $message],
            'access_token' => $accessToken,
        ]);

        return $response->json();
    }

    /**
     * Get Conversations.
     */
    public static function getConversations($accessToken)
    {
        $response = Http::get("https://graph.facebook.com/v21.0/me/conversations", [
            'access_token' => $accessToken,
        ]);

        return $response->json();
    }

    /**
     * Get Messages from a Conversation.
     */
    public static function getMessages($conversationId, $accessToken)
    {
        $response = Http::get("https://graph.facebook.com/v21.0/{$conversationId}/messages", [
            'access_token' => $accessToken,
        ]);

        return $response->json();
    }
}