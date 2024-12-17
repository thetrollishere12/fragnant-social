<?php

namespace App\Livewire\Profile\DigitalAssets;

use Livewire\Component;
use App\Helper\AudiusHelper;
use App\Models\MusicGenre;

use App\Models\UserMediaSetting;
use Illuminate\Support\Facades\Auth;
use App\Models\UserMedia;
use App\Models\DigitalAsset;
class Setting extends Component
{





    public $videoTypes = []; // Array to hold video type IDs
    public $musicGenres = []; // Array to hold selected genre IDs
    public $frequency;
    public $frequencyType;
    public $quantity;
    public $user_audio;
    public $own_music = false;


    public $digital_asset_id;


    public function mount()
    {


        // Load existing user settings if they exist
        $settings = UserMediaSetting::where('digital_asset_id',$this->digital_asset_id)->first();

        if ($settings) {
            $this->videoTypes = $settings->video_type_id ? $settings->video_type_id : [];
            $this->musicGenres = $settings->music_genre_id ? $settings->music_genre_id : [];
            $this->frequency = $settings->frequency;
            $this->frequencyType = $settings->frequency_type;
            $this->quantity = $settings->quantity;
            $this->user_audio = $settings->user_audio;
        }

        if (UserMedia::where('digital_asset_id',$this->digital_asset_id)->where('type','audio')->count() > 0) {
            $this->own_music = true;
        }

    }

    public function saveSettings()
    {
        // Validate inputs
        // $this->validate([
        //     'videoTypes' => 'array',
        //     'musicGenres' => 'array',
        //     'frequency' => 'nullable|integer',
        //     'frequencyType' => 'nullable|string',
        //     'quantity' => 'required|integer|min:1',
        // ]);

        // Save or update user settings
        UserMediaSetting::updateOrCreate(
            [
                'digital_asset_id' => $this->digital_asset_id
            ],
            [
                // 'video_type_id' => $this->videoTypes,
                'music_genre_id' => $this->musicGenres,
                'frequency' => $this->frequency,
                'frequency_type' => $this->frequencyType,
                'quantity' => $this->quantity,
                'user_audio' => $this->user_audio,
            ]
        );

        // Notify user of success
        session()->flash('message', 'Settings saved successfully.');


    }







    public function render()
    {

        return view('livewire.profile.digital-assets.setting', [
            'genres' => MusicGenre::pluck('name', 'id')
        ]);

    }
}