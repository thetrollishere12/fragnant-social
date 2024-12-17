<?php

namespace App\View\Components\Custom;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

use App\Models\DigitalAsset;
use Auth;

class DigitalAssetLayout extends Component
{
    /**
     * The digital asset ID.
     */
    public string $digitalAssetId;
    public $digitalAsset;

    /**
     * Create a new component instance.
     */
    public function __construct(string $digitalAssetId)
    {
        $this->digitalAssetId = $digitalAssetId;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {


        // Ensure the digital asset belongs to the authenticated user
        $this->digitalAsset = DigitalAsset::where('user_id', Auth::id())
            ->where('id', $this->digitalAssetId)
            ->first();

        if (!$this->digitalAsset) {
            abort(403, 'Unauthorized access to the digital asset.');
        }

        return view('components.custom.digital-asset-layout');

    }
}