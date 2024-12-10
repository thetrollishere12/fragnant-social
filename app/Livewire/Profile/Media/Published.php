<?php

namespace App\Livewire\Profile\Media;

use Livewire\Component;

use App\Models\PublishedMedia;
use App\Jobs\GenerateShortFormJob;
use Auth;
use Livewire\WithFileUploads;
use WireUi\Traits\WireUiActions;

class Published extends Component
{

    use WireUiActions;

    public function generate(){

        try{

        GenerateShortFormJob::dispatch(Auth::user()->id);

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
        return view('livewire.profile.media.published',[
            'published' => PublishedMedia::where('user_id',Auth::user()->id)->get()
        ]);
    }
}
