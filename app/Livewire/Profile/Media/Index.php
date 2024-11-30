<?php

namespace App\Livewire\Profile\Media;

use Livewire\Component;
use Livewire\WithFileUploads;
use WireUi\Traits\WireUiActions;
use App\Models\UserMedia;
use App\Models\MediaDetail;
use App\Models\MediaThumbnail;
use Auth;
use Storage;
use Str;
use ZipArchive;
use App\Helper\OpenAiHelper;
use App\Helper\FFmpegHelper;

use App\Jobs\ProcessMedia;


use App\Events\MediaProcessed;

class Index extends Component
{
    use WireUiActions;
    use WithFileUploads;

    public $selectedMedia = [];
    public $role;

    public $showPreviewModal = false;
    public $previewMedia = '';

    public $showEditModal = false;
    public $editMedia = null;

    public $mediaTitle;
    public $mediaDescription;

    public $image;

    protected $listeners = [
        'mediaProcessed' => 'reloadMedia',
    ];




    public function reloadMedia()
    {

        $this->userMedia = UserMedia::where('user_id', Auth::id())->get();
    }

    public function updatedImage()
{

    set_time_limit(0);

    if (!$this->image) {
        return;
    }

    foreach ($this->image as $uploadedFile) {
        // Save the file temporarily in storage
        $tempPath = $uploadedFile->store('temp', 'local');

        // Dispatch the job with the file path
        ProcessMedia::dispatch($tempPath, Auth::id());
    }

    $this->notification([
        'title' => 'Media Uploaded!',
        'description' => 'Your media has been uploaded and is being processed in the background.',
        'icon' => 'success',
    ]);


}

// #[On('echo-private:user.*.MediaProcessed')]
// public function onMediaProcessed($event, $channel)
// {
//     dd(3);
//     // Extract user ID from the channel
//     $userId = str_replace('echo-private:user.', '', explode(',', $channel)[0]);

//     // Ensure this event belongs to the authenticated user
//     if ((int) $userId === Auth::id()) {
//         $this->packageStatuses[] = $event;
//         dd(31); // Debugging output
//     }
// }

    public function select_media($code_id)
    {
        if (in_array($code_id, $this->selectedMedia)) {
            $this->selectedMedia = array_diff($this->selectedMedia, [$code_id]);
        } else {
            $this->selectedMedia[] = $code_id;
        }
    }

    public function download()
    {
        $mediaFiles = UserMedia::whereIn('code_id', $this->selectedMedia)->get();

        try {
            if ($mediaFiles->count() > 1) {
                $zip = new ZipArchive();
                $zipFileName = 'livewire-tmp/' . Str::uuid() . '.zip';
                $zipPath = Storage::disk('public')->path($zipFileName);

                if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
                    foreach ($mediaFiles as $file) {
                        $filePath = Storage::disk($file->storage)->path($file->folder . '/' . $file->filename);
                        if (file_exists($filePath)) {
                            $zip->addFile($filePath, basename($filePath));
                        }
                    }
                    $zip->close();
                } else {
                    $this->notification([
                        'title' => 'Cannot Download!',
                        'description' => 'Please try again later',
                        'icon' => 'error',
                    ]);
                }

                return response()->download($zipPath)->deleteFileAfterSend(true);
            } else {
                foreach ($mediaFiles as $file) {
                    return Storage::disk($file->storage)->download($file->folder . '/' . $file->filename);
                }
            }
        } catch (\Exception $e) {
            $this->notification([
                'title' => 'Cannot Download!',
                'description' => 'Please try again later',
                'icon' => 'error',
            ]);
        }
    }

    public function delete()
    {
        $mediaFiles = UserMedia::whereIn('code_id', $this->selectedMedia)->get();

        foreach ($mediaFiles as $media) {
            $thumbnail = MediaThumbnail::where('media_id', $media->id)->first();
            if ($thumbnail) {
                Storage::disk($thumbnail->storage)->delete($thumbnail->folder . '/' . $thumbnail->filename);
                $thumbnail->delete();
            }
            Storage::disk($media->storage)->delete($media->folder . '/' . $media->filename);
            $media->delete();
        }

        $this->selectedMedia = [];

        $this->notification([
            'title' => 'Deleted!',
            'description' => 'Media was deleted',
            'icon' => 'success',
        ]);
    }

    public function preview($code_id)
    {
        $mediaItem = UserMedia::where('code_id', $code_id)->first();

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

    public function ai_description()
    {
        $this->mediaDescription = OpenAiHelper::AiAnalyzeImage(Storage::disk($this->editMedia->storage)->url($this->editMedia->folder . '/' . $this->editMedia->filename));
    }

    public function edit($code_id)
    {
        $mediaItem = UserMedia::where('code_id', $code_id)->first();

        if ($mediaItem) {
            $detail = MediaDetail::where('media_id', $mediaItem->id)->first();
            $this->editMedia = $mediaItem;
            $this->mediaDescription = $detail ? $detail->description : '';
            $this->showEditModal = true;
        }
    }

    public function updateMedia()
    {
        if ($this->editMedia) {
            MediaDetail::updateOrCreate(
                ['media_id' => $this->editMedia->id],
                ['description' => $this->mediaDescription]
            );

            $this->notification([
                'title' => 'Media Updated!',
                'description' => 'The media details have been updated successfully.',
                'icon' => 'success',
            ]);

            $this->showEditModal = false;
        }
    }

    public function render()
    {
        $userMedia = UserMedia::where('user_id', Auth::user()->id)->get();

        return view('livewire.profile.media.index', compact('userMedia'));
    }
}