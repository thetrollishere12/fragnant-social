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

        // Fetch the storage used for this specific asset
        $storageUsed = SubscriptionHelper::getCurrentStorageUsed($asset->user_id , $id) ?? 0;
        $storageUsedGB = round($storageUsed / 1024 / 1024 / 1024, 2);

        // Fetch the monthly video count for this specific asset
        $monthlyVideoCount = SubscriptionHelper::getMonthlyVideoCountByDate(auth()->id(), now()->year, now()->month, $id);

        // Return the view with the asset, storage used, and video count
        return view('profile.digital-assets.show', compact('asset', 'storageUsedGB', 'monthlyVideoCount'));
    }

}
