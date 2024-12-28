<?php

namespace App\Livewire\Profile\DigitalAssets;

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


use App\Events\MediaProcessed;
use App\Models\DigitalAsset;



use App\Jobs\BatchProcessMediaToDb;

class Media extends Component
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

    public $digital_asset_id;

    protected $listeners = [
        'mediaProcessed' => 'reloadMedia',
    ];


    public $rules = [
        'image.*' => 'required|file|max:51200', // 50 MB per file
    ];



    public function reloadMedia()
    {
        $this->userMedia = UserMedia::where('digital_asset_id', $this->digital_asset_id)->get();
    }



    public function updatedImage()
    {


        set_time_limit(0);


        if (!$this->image) {
            return $this->dialog()->show([
                'icon' => 'error',
                'title' => 'Image Error!',
                'description' => 'Woops, There was an issue. Please <a href="'.url('contact Us').'" style="color: #007bff; text-decoration: underline;">contact us</a> if the issue persist.',
            ]);
        }


        $maxFileSize = 50 * 1024 * 1024; // 50 MB in bytes
        $arrayPath = [];
        $skippedFiles = [];


        foreach ($this->image as $uploadedFile) {

            if ($uploadedFile->getSize() > $maxFileSize) {
                // Add the skipped file name to the array
                $skippedFiles[] = $uploadedFile->getClientOriginalName();
                continue; // Skip this file
            }

            // Save the file temporarily in storage
            $tempPath = $uploadedFile->store('temp', 'local');

            // Insert media information into the database
            $originalName = $uploadedFile->getClientOriginalName();

            $arrayPath[] = [
                'temporary_path' => $tempPath,
                'originalName' => $originalName,
            ];
            
        }

        if (!empty($skippedFiles)) {
            // Notify user about skipped files
            $this->notification()->send([
                'icon' => 'warning',
                'title' => 'Some Files Skipped',
                'description' => 'The following files were too large and were skipped: ' . implode(', ', $skippedFiles),
            ]);
        }

        BatchProcessMediaToDb::dispatch($arrayPath,$this->digital_asset_id);

        $this->notification()->send([
            'title' => 'Media Uploaded!',
            'description' => 'Your media has been uploaded and is being processed in the background.',
            'icon' => 'success',
        ]);


    }

    

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
        $mediaFiles = UserMedia::whereIn('code_id', $this->selectedMedia)->where('digital_asset_id',$this->digital_asset_id)->get();

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
                    $this->notification()->send([
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
            $this->notification()->send([
                'title' => 'Cannot Download!',
                'description' => 'Please try again later',
                'icon' => 'error',
            ]);
        }
    }

    public function delete()
    {
        $mediaFiles = UserMedia::whereIn('code_id', $this->selectedMedia)->where('digital_asset_id',$this->digital_asset_id)->get();

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

        $this->notification()->send([
            'title' => 'Deleted!',
            'description' => 'Media was deleted',
            'icon' => 'success',
        ]);
    }

    public function preview($code_id)
    {
        $mediaItem = UserMedia::where('code_id', $code_id)->where('digital_asset_id',$this->digital_asset_id)->first();

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
        $mediaItem = UserMedia::where('code_id', $code_id)->where('digital_asset_id',$this->digital_asset_id)->first();

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

            $this->notification()->send([
                'title' => 'Media Updated!',
                'description' => 'The media details have been updated successfully.',
                'icon' => 'success',
            ]);

            $this->showEditModal = false;
        }
    }

    public function render()
    {
        $userMedia = UserMedia::where('digital_asset_id',$this->digital_asset_id)->get();

        return view('livewire.profile.digital-assets.media', compact('userMedia'));
    }
}
