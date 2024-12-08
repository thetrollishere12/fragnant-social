<?php

namespace App\Orchid\Layouts\Webpage;

use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;
use Orchid\Screen\Actions\Link;
use App\Models\Webpage;
use Storage;


class WebpageListLayout extends Table
{
    /**
     * Data source.
     *
     * The name of the key to fetch it from the query.
     * The results of which will be elements of the table.
     *
     * @var string
     */
    protected $target = 'webpages';

    /**
     * Get the table cells to be displayed.
     *
     * @return TD[]
     */
    protected function columns(): iterable
    {
        return [

            TD::make('id', 'ID'),
            TD::make('uri', 'URI')
            ->render(function ($webpage) {
                return Link::make($webpage->uri)
                    ->href(url($webpage->uri))
                    ->target('_blank');
            }),
            TD::make('name', 'Route Name')->render(function ($webpage) {
                return $webpage->name ? "->route('".$webpage->name."')" : '';
            }),
            TD::make('indexable', 'Indexable')
            ->render(function ($webpage) {
                return $webpage->indexable ? 'Indexed' : '';
            }),
            TD::make('title', 'Title'),
            TD::make('description', 'Description'),
            TD::make('edit')
                ->render(function (Webpage $webpage) {
                    return Link::make('Edit')
                        ->route('platform.webpage.edit', $webpage);
                }),
        ];
    }
}
