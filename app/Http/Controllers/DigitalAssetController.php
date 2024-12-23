<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\DigitalAsset;

use App\Helper\SubscriptionHelper;

class DigitalAssetController extends Controller
{
    

    public function show($id)
    {

        // Fetch the digital asset by ID
        $asset = DigitalAsset::findOrFail($id);

        // Optional: Check if the authenticated user owns the asset
        if ($asset->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }


        // Return the view with the asset, storage used, and video count
        return view('profile.digital-assets.show', [
            'digital_asset_id' => $asset->id
        ]);

    }

}
