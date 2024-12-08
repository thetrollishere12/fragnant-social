<?php

namespace App\Orchid\Screens\Webpage;

use Orchid\Screen\Screen;
use App\Orchid\Layouts\Webpage\WebpageListLayout;
use App\Models\Webpage;
use Orchid\Screen\Actions\Link;


class WebpageListScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        return [
            'webpages' => Webpage::orderByRaw('indexable IS NULL, indexable DESC')->paginate()
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Webpage List Screen';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            Link::make('Create new')
                ->icon('pencil')
                ->route('platform.webpage.create')
        ];
    }

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): iterable
    {
        return [
            WebpageListLayout::class
        ];
    }
}
