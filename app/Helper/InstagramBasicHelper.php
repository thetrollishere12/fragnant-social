<?php

namespace App\Helper;
use Illuminate\Support\Facades\Http;

class InstagramBasicHelper
{
   




    public static function getAccessToken($code){

        $response = Http::get('https://graph.instagram.com/access_token',[
            'grant_type'=>'ig_exchange_token',
            'client_secret'=>env('INSTAGRAMBASIC_CLIENT_SECRET'),
            'access_token'=>$code
        ]);

        return $response->json();

    }


    public static function media($media_id,$token){

        $response = Http::get('https://graph.instagram.com/'.$media_id,[
            'access_token'=>$token,
            'fields'=>'caption,id,is_shared_to_feed,media_type,media_url,permalink,thumbnail_url,timestamp,username',
            'edge'=>'children'
        ]);

        return $response->json();

    }

    public static function media_children($media_id,$token){

        $response = Http::get('https://graph.instagram.com/'.$media_id.'/children',[
            'access_token'=>$token,
            'fields'=>'caption,id,is_shared_to_feed,media_type,media_url,permalink,thumbnail_url,timestamp,username'
        ]);

        return $response->json();

    }

    public static function user_media($id,$token){

        $response = Http::get('https://graph.instagram.com/'.$id.'/media',[
            'access_token'=>$token,
            'permission'=>'instagram_graph_user_media, instagram_graph_user_profile',
            'fields'=>'caption,id,is_shared_to_feed,media_type,media_url,permalink,thumbnail_url,timestamp,username'
        ]);

        return $response->json();

    }






}