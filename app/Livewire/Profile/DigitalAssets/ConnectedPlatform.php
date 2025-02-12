<?php

namespace App\Livewire\Profile\DigitalAssets;

use Livewire\Component;

use App\Models\Product\ProductImportFeed;
use WireUi\Traits\WireUiActions;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

use App\Models\Platform\Account\EbayAccount;

class ConnectedPlatform extends Component
{

    use WireUiActions;

    public $digital_asset_id;
    public $url,$name;
    public $url_modal = false;

    public static function getFileTypeFromUrl($url)
{
    $extension = Str::lower(pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION));

    $fileTypes = [
        'csv' => 'CSV',
        'xls' => 'Excel',
        'xlsx' => 'Excel',
        'json' => 'JSON',
        'xml' => 'XML',
        'txt' => 'Text',
        'zip' => 'ZIP',
    ];

    return $fileTypes[$extension] ?? 'Unknown';
}

    public function save_url()
    {

        // Validate input fields
        $validator = Validator::make([
            'url' => $this->url,
            'name' => $this->name,
        ], [
            'url' => ['required', 'url', 'ends_with:.csv,.xls,.xlsx,.json,.xml,.txt,.zip'], // Ensures valid file extensions
            'name' => ['string', 'max:255'],
        ]);

        // Throw validation error if it fails
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        // Extract file type
        $fileType = self::getFileTypeFromUrl($this->url);

        ProductImportFeed::updateOrCreate(
            [
                'url' => $this->url,
                'digital_asset_id' => $this->digital_asset_id,
            ],
            [
                'name' => $this->name,
                'file_type' => $fileType,
            ]
        );

        $this->notification()->send([
                'title' => 'Url Added!',
                'description' => 'Successfully added & will processed shortly',
                'icon' => 'success',
            ]);

        $this->url_modal = false;

    }

    public function render()
    {

        $ebay = EbayAccount::where('digital_asset_id',$this->digital_asset_id)->get();

        $import = ProductImportFeed::where('digital_asset_id',$this->digital_asset_id)->get();

        return view('livewire.profile.digital-assets.connected-platform',[
            'ebay'=>$ebay,
            'import'=>$import
        ]);

    }
}
