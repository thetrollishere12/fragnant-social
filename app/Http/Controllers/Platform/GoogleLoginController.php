<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller; // Add this line
use Illuminate\Http\Request;
use Socialite;
use Auth;
use Exception;
use Carbon\Carbon;
use App\Models\User;

class GoogleLoginController extends Controller
{
    /**
     * Where to redirect admins after login.
     *
     * @var string
     */
    protected $redirectTo = "/";

    /**
     * Create a new controller instance.
     *
     * @return void
     */


    public function redirect(Request $req){

        if (isset($req->link)) {
            session()->put('redirect_link',$req->link);
        }

        return Socialite::driver('google')
        ->redirect();

    }

    public function success(){

        try {

            $platformUser = Socialite::driver('google')->user();
            
        } catch (Exception $e) {
   
            return redirect('/register');
        }

        $existUser = User::where('email', $platformUser->email)->first();

        if ($existUser) {

            if ($existUser->google_id) {
                Auth::loginUsingId($existUser->id);
            }else{

                return redirect()->intended('google-link')->with(['id'=>$platformUser->id,'user'=>$existUser->email]);

            }

        }else{
            $user = new User;
            $user->name = $platformUser->name;
            $user->email = $platformUser->email;
            $user->google_id = $platformUser->id;
            $user->email_verified_at = Carbon::now()->toDateTimeString();
            $user->password = bcrypt(request('password'));
            $user->save();

            Auth::loginUsingId($user->id);
        }


        if (session('redirect_link')) {
            return redirect()->intended(session('redirect_link'));
        }else{
            return redirect()->intended('/');
        }
    }

    public function link(Request $request){

        if (session()->get('id') && session()->get('user')) {
            return view('auth.medialogin.google',['userId'=>session()->get('id'),'email'=>session()->get('user')]);
        }else{
            return redirect()->intended('login');
        }

    }

    public function linking(Request $request){

        $input = $request->all();
     
        $this->validate($request, [
            'email' => 'required|email',
            'password' => 'required',
        ]);
     
        if(auth()->attempt(array('email' => $input['email'], 'password' => $input['password']))){
            
            User::where('email', $request->email)->update([
                'google_id'=>$request->id
            ]);

            if (session('redirect_link')) {
                return redirect()->intended(session('redirect_link'));
            }else{
                return redirect()->intended('/');
            }

        }else{
            return redirect()->back()->with(['id'=>$request->id,'user'=>$request->email])->withErrors(['These credentials do not match our records.']);
        }

    }

}
