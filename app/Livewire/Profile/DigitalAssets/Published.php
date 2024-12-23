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



public $stageStatus = 'Idle';
public $stageMessage = 'Waiting for processing...';
public $progress = 0; // Progress percentage
public $isProcessing = false; // Boolean to toggle UI

protected $listeners = [
    'updateStage' => 'updateStageStatus',
];

/**
 * Update stage status dynamically.
 *
 * @param string $stage
 * @param string $message
 * @param int $progress
 */
public function updateStageStatus($stage, $message, $progress)
{
    $this->stageStatus = $stage;
    $this->stageMessage = $message;
    $this->progress = $progress;

    // Determine if processing is complete
    $this->isProcessing = $stage !== 'Completed';
}


    public function reloadPublished()
    {
        $this->published = PublishedMedia::where('digital_asset_id',$this->digital_asset_id)->latest()->get();
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

        $this->isProcessing = true;
        $this->stageStatus = 'Idle';
        $this->stageMessage = 'Waiting for processing...';
        $this->progress = 0;

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
            'published' => PublishedMedia::where('digital_asset_id',$this->digital_asset_id)->latest()->get()
        ]);
    }
}
