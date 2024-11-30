<?php

namespace App\Helper\Media;

use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\Http;


class UnsplashHelper
{





    public static function images($string,$page){

        $response = Http::get('https://api.unsplash.com/search/photos',[
            'client_id'=>env('UNSPLASH_CLIENT'),
            'query' => $string,
            'per_page' => 30,
            'page'=>($page ? $page : 1),
            // 'orientation'=>'squarish'
        ]);

        return json_decode($response->body());

    }





    public static function collection($string,$page){

        $response = Http::get('https://api.unsplash.com/collections',[
            'client_id'=>env('UNSPLASH_CLIENT'),
            'query' => $string,
            'per_page' => 30,
            'page'=>($page ? $page : 1)
        ]);

        return json_decode($response->body());

    }







}