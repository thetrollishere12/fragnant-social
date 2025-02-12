<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helper\Platform\EbayHelper;
use App\Models\PlatformToken;
use App\Models\DigitalAsset;
use App\Models\Platform\Account\EbayAccount;

use Carbon\Carbon;
use Auth;

class EbayController extends Controller
{
    public $scopes;

    public function __construct()
    {
        // Define eBay OAuth scopes globally
        $this->scopes = [
            "https://api.ebay.com/oauth/api_scope",
            "https://api.ebay.com/oauth/api_scope/sell.marketing.readonly",
            "https://api.ebay.com/oauth/api_scope/sell.marketing",
            "https://api.ebay.com/oauth/api_scope/sell.inventory.readonly",
            "https://api.ebay.com/oauth/api_scope/sell.inventory",
            "https://api.ebay.com/oauth/api_scope/sell.account.readonly",
            "https://api.ebay.com/oauth/api_scope/sell.account",
            "https://api.ebay.com/oauth/api_scope/sell.fulfillment.readonly",
            "https://api.ebay.com/oauth/api_scope/sell.fulfillment",
            "https://api.ebay.com/oauth/api_scope/sell.analytics.readonly",
            "https://api.ebay.com/oauth/api_scope/sell.finances",
            "https://api.ebay.com/oauth/api_scope/sell.payment.dispute",
            "https://api.ebay.com/oauth/api_scope/commerce.identity.readonly",
            "https://api.ebay.com/oauth/api_scope/commerce.notification.subscription",
            "https://api.ebay.com/oauth/api_scope/commerce.notification.subscription.readonly"
        ];
    }

    public function redirect(Request $req)
    {
        if ($req->has('link')) {
            session()->put('redirect_link', $req->link);
        }

        if ($req->has('project_id')) {
            session()->put('project_id', $req->project_id);
        }

        $clientId = env('EBAY_CLIENT_APP_ID');
        $redirectUri = env('EBAY_REDIRECT_URI');

        // Build the eBay OAuth URL
        $ebayAuthUrl = "https://auth.ebay.com/oauth2/authorize?" . http_build_query([
            'client_id' => $clientId,
            'response_type' => 'code',
            'redirect_uri' => $redirectUri,
            'scope' => implode(' ', $this->scopes),
        ]);

        return redirect($ebayAuthUrl);
    }

    public function success(Request $req)
    {
      
        try {
            
            $platformUser = EbayHelper::getAccessToken($req->code);
 
            $project = DigitalAsset::where('id',session('project_id'))->where('user_id',Auth::id())->first();

            if(!$project){

                dd('No project');
                return redirect()->intended('/')->with('error', 'There was an error connecting your YouTube account.');

            }

            $user = EbayHelper::user($platformUser['access_token']);

            PlatformToken::updateOrCreate(
                [   
                    'user_id' => Auth::id(),
                    'digital_asset_id' => $project->id,
                    'platform_id' => $user['userId'],
                    'platform' => 'ebay'
                ],
                [
                    'access_token' => $platformUser['access_token'],
                    'refresh_token' => $platformUser['refresh_token'],
                    'expires_at' => Carbon::now()->addSeconds($platformUser['expires_in']),
                    'scopes' => implode(',', $this->scopes)
                ]
            );

            // Save eBay account info
            EbayAccount::updateOrCreate(
                [
                    'account_id' => $user['userId']
                ],
                [
                    'digital_asset_id' => $project->id,
                    'name' => $user['username'] ?? null, // Avoid errors if username is missing
                    'accountType' => $user['accountType'] ?? null,
                    'registrationMarketplaceId' => $user['registrationMarketplaceId'] ?? null,
                    'url' => "https://www.ebay.com/usr/{$user['userId']}", // Fix incorrect TikTok URL
                    'avatar_url' => "https://securepics.ebaystatic.com/aw/pics/community/user/profile/img-" . $user['userId'] . ".png",
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
