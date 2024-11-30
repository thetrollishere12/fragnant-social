<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;
use App\Models\Webpage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;

class WebpageExtractor extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:webpage-extractor';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {

        $routes = Route::getRoutes();

        foreach ($routes as $route) {
            if (in_array('GET', $route->methods()) && !in_array('POST', $route->methods())
                && !Str::contains($route->uri(), ['wireui', 'livewire', 'admin'])) {

                // Simulate a GET request to the route
                $response = Http::get(url($route->uri()));

                // Check if the response contains the noindex, nofollow meta tag
                $hasIndexFollow = Str::contains($response->body(), '<meta name="robots" content="index, follow">');

                // Update or create the webpage record
                Webpage::updateOrCreate(
                    ['uri' => $route->uri()],
                    [
                        'uri' => $route->uri(),
                        'name' => $route->getName(),
                        'indexable' => $hasIndexFollow
                    ]
                );
            }
        }

        $this->info('Webpage extraction and meta tag checking completed.');

    }
}
