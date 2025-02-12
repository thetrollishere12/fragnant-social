<?php

namespace App\Helper\Platform;

use Illuminate\Support\Facades\Http;

class EbayHelper
{




public static function getAccessToken($code)
{
    $response = Http::withHeaders([
        'Content-Type'  => 'application/x-www-form-urlencoded',
        'Authorization' => 'Basic ' . base64_encode(env('EBAY_CLIENT_APP_ID') . ':' . env('EBAY_CLIENT_APP_SECRET')),
    ])->asForm()->post('https://api.ebay.com/identity/v1/oauth2/token', [
        'grant_type'   => 'authorization_code',
        'code'         => $code,
        'redirect_uri' => env('EBAY_REDIRECT_URI'),
    ]);

    return $response->json();
}








public static function refreshToken($id)
{

    $account = EbayAccount::where('parent_email', auth()->user()->email)
                          ->where('ebay_userID', $id)
                          ->first();

    if ($account) {
        $scopes = implode(' ', [
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
            "https://api.ebay.com/oauth/api_scope/commerce.notification.subscription.readonly",
        ]);

        $response = Http::withHeaders([
            'Content-Type'  => 'application/x-www-form-urlencoded',
            'Authorization' => 'Basic ' . base64_encode(env('EBAY_CLIENT_APP_ID') . ':' . env('EBAY_CLIENT_APP_SECRET')),
        ])->asForm()->post('https://api.ebay.com/identity/v1/oauth2/token', [
            'grant_type'    => 'refresh_token',
            'refresh_token' => $account->refresh_token,
            'scope'         => $scopes,
        ]);

        $new_oauth = $response->json();

        if (isset($new_oauth['access_token'])) {
            $account->update([
                'access_token' => $new_oauth['access_token']
            ]);

            return $new_oauth;

        }

        return "Error refreshing token: " . json_encode($new_oauth);
    } else {
        return "Account doesn't exist";
    }

}




public static function user($token)
{
    $response = Http::withHeaders([
        'Accept'        => 'application/json',
        'Authorization' => 'Bearer ' . $token,
    ])->get('https://apiz.ebay.com/commerce/identity/v1/user/');

    return $response->json();
}



    public static function transaction_summary($token,$min,$max,$limit,$offset){

    	$response = Http::withHeaders([
            'authorization'=>'Bearer '.$token
        ])->get('https://apiz.ebay.com/sell/finances/v1/transaction?limit='.$limit.'&offset='.$offset);

        return json_decode($response->getBody());

    }


    public static function order($token,$min,$max,$limit,$offset){

    	$response = Http::withHeaders([
            'authorization'=>'Bearer '.$token
        ])->get('https://api.ebay.com/sell/fulfillment/v1/order?limit='.$limit.'&offset='.$offset);

        return json_decode($response->getBody());

    }

    public static function legacy_image($token,$legacy_id){

    	$response = Http::withHeaders([
            'authorization'=>'Bearer '.$token
        ])->get('https://api.ebay.com/buy/browse/v1/item/get_item_by_legacy_id?legacy_item_id='.$legacy_id);

        return json_decode($response->getBody())->image->imageUrl;

    }

    public static function active($token,$limit,$offset){

        $response = Http::withHeaders([
            'authorization'=>'Bearer '.$token
        ])->get('https://api.ebay.com/sell/inventory/v1/inventory_item');
        
        return json_decode($response->getBody());

    }

    public static function search($token,$marketplace,$param){

        $response = Http::withHeaders([
            'authorization'=>'Bearer '.$token,
            'X-EBAY-C-MARKETPLACE-ID'=>$marketplace
        ])->get('https://api.ebay.com/buy/browse/v1/item_summary/search?limit=200&sort=price&filter=buyingOptions:{FIXED_PRICE|AUCTION|BEST_OFFER},price:[..'.$param['price'].'],priceCurrency:USD&q='.$param['search']);

        return json_decode($response->getBody());

    }

    public static function item_search($token,$marketplace,$id){

        $response = Http::withHeaders([
            'authorization'=>'Bearer '.$token,
            'X-EBAY-C-MARKETPLACE-ID'=>$marketplace
        ])->get('https://api.ebay.com/buy/browse/v1/item/'.$id);

        return json_decode($response->getBody());

    }

}