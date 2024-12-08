<?php

namespace App\Orchid\Screens\Webpage;





use App\Models\Webpage;
use Illuminate\Http\Request;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Quill;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Fields\Upload;
use Orchid\Screen\Fields\Picture;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Actions\Button;

use Orchid\Screen\Screen;
use Orchid\Support\Facades\Alert;
use Illuminate\Support\Facades\Storage;
use Orchid\Attachment\Attachable;

use Orchid\Screen\Fields\Cropper;
use Orchid\Screen\Fields\Select;

use Auth;
use Orchid\Screen\Fields\Matrix;

use Orchid\Screen\Fields\SimpleMDE;
use Illuminate\Support\Facades\Artisan;

use Orchid\Screen\TD;



class WebpageEditScreen extends Screen
{
    /**
     * @var faq
     */
    public $webpage;

    /**
     * Query data.
     *
     * @return array
     */
    public function query(Webpage $webpage): array
    {

        // Check if the webpage exists and if the title is empty
        if ($webpage->exists && empty($webpage->title)) {
            $webpage->title = config('app.name') . ' | ';
        }

        return [
            'webpage' => $webpage
        ];
    }

    /**
     * Display header name.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return $this->webpage->exists ? 'Edit A Webpage' : 'Creating A New Webpage';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            Button::make('Post new Webpage')
                ->icon('pencil')
                ->method('createOrUpdate')
                ->canSee(!$this->webpage->exists),

            Button::make('Update')
                ->icon('note')
                ->method('createOrUpdate')
                ->canSee($this->webpage->exists),

            Button::make('Remove')
                ->icon('trash')
                ->method('remove')
                ->canSee($this->webpage->exists),
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
            Layout::rows([
              
                TextArea::make('webpage.uri')
                    ->title('Page Uri')
                    ->rows(3)
                    ->maxlength(60)
                    ->placeholder('Page Uri'),

                    TextArea::make('webpage.name')
                    ->title('Route Name')
                    ->rows(3)
                    ->maxlength(60)
                    ->placeholder('Route Name'),

              TextArea::make('webpage.title')
                    ->title('Page Title')
                    ->rows(3)
                    ->maxlength(60)
                    ->placeholder('Title'),

                TextArea::make('webpage.description')
                    ->title('Description')
                    ->rows(10)
                    ->maxlength(155)
                    ->placeholder('Description'),

            ])
        ];
    }






    public function createOrUpdate(Webpage $webpage, Request $request)
    {


        $webpage->fill($request->get('webpage'))->save();

        Alert::info('You have successfully created a webpage.');

        return redirect()->route('platform.webpage.list');
    }

    /**
     * @param webpage $webpage
     *
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Exception
     */
    public function remove(Webpage $webpage)
    {
        $webpage->delete();

        Alert::info('You have successfully deleted the webpage.');

        return redirect()->route('platform.webpage.list');
    }





}
