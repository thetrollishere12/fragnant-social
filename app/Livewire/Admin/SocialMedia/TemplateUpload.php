<?php

namespace App\Livewire\Admin\SocialMedia;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\MediaTemplate;
use Storage;

use App\Helper\FFmpegHelper;

use App\Models\MediaTemplateThumbnail;

class TemplateUpload extends Component
{

    use WithFileUploads;

    public $file;
    public $url;
    public $title;
    public $platform;
    public $type;

    protected $rules = [
        'file' => 'nullable|file|max:10240', // Limit file size to 10MB
        'url' => 'nullable|url',
        'title' => 'nullable|string|max:255',
        'platform' => 'nullable|string|max:255',
    ];

    public function save()
    {
        $this->validate();

        $storage = 'public';
        $folder = 'assets/templates';
        $filename = null;

        if ($this->file) {
            // Save the uploaded file
            $filename = $this->file->store($folder, $storage);
        }

        $media_template = MediaTemplate::create([
            'user_id' => auth()->id(),
            'title' => $this->title,
            'platform' => $this->platform,
            'url' => $this->url,
            'storage' => $storage,
            'folder' => $folder,
            'filename' => basename($filename),
            'tags' => '[]',
            'attributes' => '[]',
            'type' => $this->type
        ]);

        // Reset form fields
        $this->reset(['file', 'url', 'title', 'platform']);


        // Thumbnail Processing
        $thumbnailOutputPath = 'assets/templates-thumbnail/template_'.$media_template->id.'_'.\Str::uuid().'/';

        try {

            FFmpegHelper::generateFrames(
                inputPath: "{$media_template->folder}/{$media_template->filename}",
                outputPath: $thumbnailOutputPath,
                frameRate: 1,
                width:250
            );

            MediaTemplateThumbnail::create([
                'media_template_id' => $media_template->id,
                'storage' => $storage,
                'folder' => $thumbnailOutputPath,
                'filename' => 'frame_000000.jpg',
            ]);

        }catch(\Exception $e){
            dd($e);
        }

        session()->flash('success', 'Media Template added successfully!');
    }



    public function deleteTemplate($id)
    {
        $template = MediaTemplate::find($id);

        if ($template) {
            // Delete the file if it exists
            if ($template->filename) {
                Storage::disk($template->storage)->delete($template->folder . '/' . $template->filename);
            }

            // Delete the database record
            $template->delete();

            session()->flash('success', 'Media Template deleted successfully!');
        } else {
            session()->flash('error', 'Media Template not found.');
        }
    }



    public function render()
    {
        return view('livewire.admin.social-media.template-upload', [
            'mediaTemplates' => MediaTemplate::latest()->get(),
        ]);
    }
}
