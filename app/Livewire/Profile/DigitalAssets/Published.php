<?php

namespace App\Livewire\Profile\DigitalAssets;

use Livewire\Component;

use App\Models\PublishedMedia;
use App\Jobs\GenerateShortFormJob;
use Auth;
use Livewire\WithFileUploads;
use WireUi\Traits\WireUiActions;
use App\Models\DigitalAsset;
class Published extends Component
{

    use WireUiActions;

    public $digital_asset_id;
    public $showPreviewModal = false;
    public $previewMedia = '';



    protected $listeners = [
        'mediaPublished' => 'reloadPublished',
    ];


    public function reloadPublished()
    {
        $this->published = PublishedMedia::where('digital_asset_id',$this->digital_asset_id)->get();
    }

    public function preview($p_id)
    {
        $mediaItem = PublishedMedia::where('id', $p_id)->where('digital_asset_id',$this->digital_asset_id)->first();

        if ($mediaItem) {
            $this->previewMedia = $mediaItem;
            $this->showPreviewModal = true;
        }
    }

    public function closePreview()
    {
        $this->showPreviewModal = false;
        $this->previewMedia = '';
    }


    public function generate(){

        try{

        GenerateShortFormJob::dispatch($this->digital_asset_id);

        $this->notification()->send([
            'title' => 'Generating!',
            'description' => 'Started Generating',
            'icon' => 'success',
        ]);

        }catch(\Exception $e){

        $this->notification()->send([
            'title' => 'Error!',
            'description' => 'There was an error please try again.',
            'icon' => 'error',
        ]);

        }

    }

    public function render()
    {
        return view('livewire.profile.digital-assets.published',[
            'published' => PublishedMedia::where('digital_asset_id',$this->digital_asset_id)->get()
        ]);
    }
}
