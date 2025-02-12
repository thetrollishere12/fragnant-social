<?php

namespace App\Livewire\Profile\DigitalAssets;

use Livewire\Component;
use App\Models\Product\ProductImportFeed;



class Product extends Component
{

    public $digital_asset_id;

    public function render()
    {

        $feeds = ProductImportFeed::where('digital_asset_id', $this->digital_asset_id)->get();

        return view('livewire.profile.digital-assets.product',[
            'productImportFeeds' => $feeds
        ]);
    }



}
