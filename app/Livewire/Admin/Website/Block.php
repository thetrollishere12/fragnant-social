<?php

namespace App\Livewire\Admin\Website;

use Livewire\Component;
use App\Models\WebsiteBlock;
use Auth;
use Storage;
use WireUi\Traits\WireUiActions;
use Livewire\WithFileUploads;

class Block extends Component
{
    use WireUiActions;
     use WithFileUploads;

    public $key, $type, $value;
    public $editKey, $editType, $editValue, $editId;
    public $editModal = false;

    public function submit()
    {
        if ($this->type == "File") {
            $fileName = 'block/' . uniqid() . '.' . $this->value->getClientOriginalExtension();
            $this->value->storeAs('public', $fileName);
            $url = Storage::disk('public')->url($fileName);

            WebsiteBlock::create([
                'block_key' => strtoupper(str_replace(" ", "_", $this->key)),
                'block_value' => $url,
                'block_type' => $this->type,
            ]);
        } else {
            WebsiteBlock::create([
                'block_key' => strtoupper(str_replace(" ", "_", $this->key)),
                'block_value' => $this->value,
                'block_type' => $this->type,
            ]);
        }

        $this->notification([
            'title'       => 'Block Added',
            'description' => 'Block was added to the list',
            'icon'        => 'success'
        ]);

        $this->reset(['key', 'value']);
    }

    public function edit($id)
    {
        $block = WebsiteBlock::find($id);
        $this->editId = $block->id;
        $this->editKey = $block->block_key;
        $this->editType = $block->block_type;
        $this->editValue = $block->block_value;
        $this->editModal = true;
    }

    public function update()
    {
        $block = WebsiteBlock::find($this->editId);

        if ($this->editType == "File" && $this->editValue instanceof \Livewire\TemporaryUploadedFile) {
            $fileName = 'block/' . uniqid() . '.' . $this->editValue->getClientOriginalExtension();
            $this->editValue->storeAs('public', $fileName);
            $url = Storage::disk('public')->url($fileName);
            $block->update([
                'block_key' => strtoupper(str_replace(" ", "_", $this->editKey)),
                'block_value' => $url,
                'block_type' => $this->editType,
            ]);
        } else {
            $block->update([
                'block_key' => strtoupper(str_replace(" ", "_", $this->editKey)),
                'block_value' => $this->editValue,
                'block_type' => $this->editType,
            ]);
        }

        $this->notification([
            'title'       => 'Block Updated',
            'description' => 'Block was updated successfully',
            'icon'        => 'success'
        ]);

        $this->reset(['editKey', 'editValue', 'editType', 'editModal']);
    }

    public function delete($id)
    {
        $block = WebsiteBlock::find($id);

        if ($block) {
            if ($block->block_type == "File") {
                $url = $block->block_value;
                $relativePath = parse_url($url, PHP_URL_PATH);
                $relativePath = str_replace('/storage/', '', $relativePath);
                Storage::disk('public')->delete($relativePath);
            }

            $block->delete();

            $this->notification([
                'title'       => 'Block Deleted',
                'description' => 'Block was deleted from the list',
                'icon'        => 'error'
            ]);
        } else {
            $this->notification([
                'title'       => 'Block Not Found',
                'description' => 'The block you are trying to delete does not exist.',
                'icon'        => 'error'
            ]);
        }
    }

    public function render()
    {
        $webblocks = WebsiteBlock::all();
        $types = [
            'string',
            'boolean',
            'integer',
            'float',
            'array',
            'json',
            'date',
            'serialized',
            'File'
        ];

        return view('livewire.admin.website.block', compact('webblocks', 'types'));
    }
}