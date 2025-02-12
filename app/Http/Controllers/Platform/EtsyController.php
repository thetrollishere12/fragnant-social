<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Socialite;
use Auth;
use Exception;
use Carbon\Carbon;

use App\Models\User;

use App\Models\DigitalAsset;

use App\Models\SocialMedia\PlatformToken;
use App\Models\SocialMedia\EtsyAccount;

class EtsyController extends Controller
{
    

    public function redirect(Request $req){

        if (isset($req->link)) {
            session()->put('redirect_link',$req->link);
        }

        if (isset($req->project_id)) {
            session()->put('project_id',$req->project_id);
        }

        return Socialite::driver('etsy')
        ->redirect();

    }


    public function success(){

        try {


            $platformUser = Socialite::driver('etsy')->user();

            dd($platformUser);

            $project = DigitalAsset::where('id',session('project_id'))->where('user_id',Auth::id())->first();

            if(!$project){

                dd('No project');
                return redirect()->intended('/')->with('error', 'There was an error connecting your YouTube account.');

            }















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
