<?php

namespace App\Http\Controllers\Platform;

use Illuminate\Http\Request;
use Socialite;
use Auth;
use Exception;
use Carbon\Carbon;

use App\Models\User;

use App\Models\SocialMedia\InstagramToken;



use App\Helper\Platform\InstagramHelper;

use App\Helper\Platform\InstagramBasicHelper;

use App\Models\DigitalAsset;


class InstagramController extends Controller
{


    public function redirect(Request $req)
    {


        if (isset($req->link)) {
            session()->put('redirect_link', $req->link);
        }

        if (isset($req->project_id)) {
            session()->put('project_id', $req->project_id);
        }

        return Socialite::driver('instagram')
            ->scopes([
                'user_profile',       // Basic profile information
                'user_media',         // Access to media
                'instagram_graph_user_media', // Additional media permissions
                'instagram_graph_user_profile', // Profile information
            ])
            ->with([
                'enable_fb_login' => '0',
                'force_authentication' => '1',
            ])
            ->redirect();

    }



    public function success(Request $request){



        $code = $request->input('code');

        if (!$code) {
            return redirect('/login')->withErrors(['login' => 'Instagram login failed']);
        }

        // try {
            // Step 1: Exchange the code for an access token
            $tokenData = InstagramHelper::getAccessToken($code);


            if (!isset($tokenData['access_token'])) {
                throw new Exception("Failed to obtain access token.");
            }

            $accessToken = $tokenData['access_token'];
            $instagramUserId = $tokenData['user_id']; // Instagram User ID

            // Get long live token
            $new_token = InstagramHelper::getLongLivedToken($accessToken);

            // dd($tokenData,$new_token);


            // Save the access token and refresh token for later use
            InstagramToken::updateOrCreate(
                [
                    'user_id' => Auth::id(),
                    'platform_id' => $tokenData['user_id']
                ],
                [
                    'platform' => 'instagram',
                    'access_token' => $new_token['access_token'],
                    'expires_at' => Carbon::now()->addSeconds($new_token['expires_in']),
                    'scopes' => implode(',', $tokenData['permissions'])
                ]
            );



            if (session('redirect_link')) {
                return redirect()->intended(session('redirect_link'));
            } else {
                return redirect()->intended('/');
            }

        //     // Redirect to intended page after login
        //     return redirect()->intended(session('redirect_link', '/'));
        // } catch (Exception $e) {
        //     return redirect('/login')->withErrors(['login' => 'Instagram login failed: ' . $e->getMessage()]);
        // }






    }



}
