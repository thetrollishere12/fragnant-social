<?php

namespace App\Http\Controllers\Platform;

use Illuminate\Http\Request;
use Socialite;
use Auth;
use Exception;
use Carbon\Carbon;

use App\Models\User;
use App\Models\Platform\Account\TiktokToken;
use App\Models\Platform\Account\TiktokAccount;

use App\Models\DigitalAsset;

class TiktokController extends Controller
{
    



    public function redirect(Request $req){

        if (isset($req->link)) {
            session()->put('redirect_link',$req->link);
        }

        if (isset($req->project_id)) {
            session()->put('project_id',$req->project_id);
        }

        return Socialite::driver('tiktok')
        ->redirect();

    }

    public function success(){

        try {


            $platformUser = Socialite::driver('tiktok')
            ->user();

            $project = DigitalAsset::where('id',session('project_id'))->where('user_id',Auth::id())->first();

            if(!$project){

                dd('No project');
                return redirect()->intended('/')->with('error', 'There was an error connecting your YouTube account.');

            }



           // Save the access token and refresh token for later use
            TiktokToken::updateOrCreate(
                [
                    'digital_asset_id' => $project->id,
                    'platform_id' => $platformUser->getId(),
                ],
                [
                    'platform' => 'tiktok',
                    'access_token' => $platformUser->token,
                    'refresh_token' => $platformUser->refreshToken,
                    'expires_at' => Carbon::now()->addSeconds($platformUser->expiresIn),
                    'scopes' => implode(',', $platformUser->approvedScopes)
                ]
            );

            // Save TikTok account info
            TiktokAccount::firstOrCreate(
                ['account_id' => $platformUser->getId()],
                [
                    'digital_asset_id' => $project->id,
                    'display_name' => $platformUser->user['display_name'],
                    'profile_url' => "https://www.tiktok.com/@{$platformUser->user['display_name']}",
                    'avatar_url' => $platformUser->user['avatar_large_url'] ?? $platformUser->getAvatar(),
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
