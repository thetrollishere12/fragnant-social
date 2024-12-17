<?php

namespace App\Livewire\Profile\DigitalAssets;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\DigitalAsset;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{
    use WithFileUploads;

    public $name;
    public $image;
    public $description;
    public $assetId; // For editing records
    public $showModal = false; // Modal visibility state

    protected $rules = [
        'name' => 'required|string|max:255',
        'image' => 'nullable|image|max:1024', // Max 1MB for image upload
        'description' => 'nullable|string|max:500',
    ];

    /**
     * Save or update a digital asset.
     */
    public function saveAsset()
{
    $this->validate();

    $asset = $this->assetId ? DigitalAsset::find($this->assetId) : new DigitalAsset();

    // Ensure asset belongs to the authenticated user during edit
    if ($this->assetId && $asset->user_id !== Auth::id()) {
        abort(403, 'Unauthorized action.');
    }

    $asset->user_id = Auth::id(); // Assign to the authenticated user
    $asset->name = $this->name;
    $asset->description = $this->description;

    if ($this->image) {
        $asset->image = $this->image->store('digital-assets/'.Auth::id().'/profile/', 'public'); // Store the image
    }

    $asset->save();

    $this->resetForm();
    $this->showModal = false;

    session()->flash('success', $this->assetId ? 'Digital asset updated successfully.' : 'Digital asset created successfully.');
}

    /**
     * Load the asset details into the form for editing.
     */
    public function editAsset($id)
    {
        $asset = DigitalAsset::findOrFail($id);

        // Ensure the asset belongs to the authenticated user
        if ($asset->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $this->assetId = $asset->id;
        $this->name = $asset->name;
        $this->description = $asset->description;
        $this->showModal = true;
    }

    /**
     * Delete a digital asset.
     */
    public function deleteAsset($id)
    {
        $asset = DigitalAsset::findOrFail($id);

        // Ensure the asset belongs to the authenticated user
        if ($asset->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $asset->delete();

        session()->flash('success', 'Digital asset deleted successfully.');
    }

    /**
     * Reset the form fields and state.
     */
    public function resetForm()
    {
        $this->name = '';
        $this->image = null;
        $this->description = '';
        $this->assetId = null;
        $this->showModal = false;
    }

    /**
     * Render the Livewire view.
     */
    public function render()
    {
        return view('livewire.profile.digital-assets.index', [
            'assets' => DigitalAsset::where('user_id', Auth::id())->latest()->paginate(10),
        ]);
    }
}