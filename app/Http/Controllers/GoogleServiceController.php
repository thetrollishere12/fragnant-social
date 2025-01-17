<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Socialite;
use Auth;
use Exception;
use Carbon\Carbon;
use App\Models\User;
use App\Models\SocialMedia\GoogleToken;
use App\Models\SocialMedia\YoutubeChannel;



use Google_Client;
use Google_Service_YouTube;


use App\Models\DigitalAsset;


class GoogleServiceController extends Controller
{
    






    public function redirectToGoogleForChannel(Request $req)
    {

        if (isset($req->link)) {
            session()->put('redirect_link',$req->link);
        }

        if (isset($req->project_id)) {
            session()->put('project_id',$req->project_id);
        }

        return Socialite::driver('google')
        ->scopes([
            'https://www.googleapis.com/auth/youtube.readonly',
            'https://www.googleapis.com/auth/youtube.upload',
            'https://www.googleapis.com/auth/youtube'
        ])
        ->with(['access_type' => 'offline'])
        ->redirectUrl(env('YOUTUBE_REDIRECT_URI'))
        ->stateless()
        ->redirect();


    }



    public function handleGoogleChannelSuccess()
    {

        try {


            $platformUser = Socialite::driver('google')
            ->redirectUrl(env('YOUTUBE_REDIRECT_URI'))
            ->stateless()->user();

            $client = new Google_Client();
            $client->setAccessToken($platformUser->token);

            $service = new Google_Service_YouTube($client);
            $response = $service->channels->listChannels('snippet', ['mine' => true]);

            $channelInfo = $response->getItems()[0];

            $project = DigitalAsset::where('id',session('project_id'))->where('user_id',Auth::id())->first();

            if(!$project){

                dd('No project');
                return redirect()->intended('/')->with('error', 'There was an error connecting your YouTube account.');

            }

            // Save the access token and refresh token for later use
            GoogleToken::updateOrCreate(
                [
                    'digital_asset_id' => $project->id,
                    'platform_id' => $channelInfo->id
                ],
                [
                    'platform' => 'youtube',
                    'access_token' => $platformUser->token,
                    'refresh_token' => $platformUser->refreshToken,
                    'expires_at' => Carbon::now()->addSeconds($platformUser->expiresIn),
                    'scopes' => implode(',', $platformUser->approvedScopes)
                ]
            );

            YoutubeChannel::firstOrCreate(
                ['channel_id' => $channelInfo->id],
                [
                    'digital_asset_id' => $project->id,
                    'channel_name' => $channelInfo->snippet->title,
                    'channel_url' => "https://www.youtube.com/channel/{$channelInfo->id}",
                    'channel_image' => $channelInfo->snippet->thumbnails->default->url,
                ]
            );

            if (session('redirect_link')) {
                return redirect()->intended(session('redirect_link'));
            } else {
                return redirect()->intended('/');
            }





        } catch (\Exception $e) {

            dd($e->getMessage());
            return redirect()->intended('/')->with('error', 'There was an error connecting your YouTube account.');
            
        }



    }





}
