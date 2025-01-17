<?php

namespace App\Livewire\Profile\DigitalAssets;

use Livewire\Component;
use App\Models\SocialMedia\YoutubeChannel;
use App\Models\SocialMedia\TiktokAccount;

class SocialMedia extends Component
{   

    public $digital_asset_id;

    public function mount(){

    }

    public function render()
    {   
        return view('livewire.profile.digital-assets.social-media',[
            'YoutubeChannels' => YoutubeChannel::where('digital_asset_id',$this->digital_asset_id)->get(),
            'TiktokAccounts' => TiktokAccount::where('digital_asset_id',$this->digital_asset_id)->get(),
        ]);
    }
}
