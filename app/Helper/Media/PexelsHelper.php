<?php

namespace App\Helper\Media;

use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\Http;


class PexelsHelper
{





    public static function images($string,$page = 1){

        $response = Http::withHeaders([
            'Authorization'=>env('PEXELS_KEY'),
        ])->get('https://api.pexels.com/v1/search',[
            'query'=>$string,
            'per_page'=>80,
            'page'=>$page,
            // 'orientation'=>'square'
        ]);

        return json_decode($response->body());

    }





    public static function videos($string,$page = 1,$orientation = null){

        $response = Http::withHeaders([
            'Authorization'=>env('PEXELS_KEY'),
        ])->get('https://api.pexels.com/v1/videos/search',[
            'query'=>$string,
            'per_page'=>5,
            'page'=>$page,
        ]);

        return json_decode($response->body());

    }







}