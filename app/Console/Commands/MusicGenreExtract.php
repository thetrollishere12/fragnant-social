<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Helper\AudiusHelper;
use App\Models\MusicGenre;


class MusicGenreExtract extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:music-genre-extract';

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
        

        // Get the trending tracks from the AudiusHelper
        $tracks = AudiusHelper::getTrendingTracks();

        // Ensure data exists
        if (!isset($tracks['data']) || !is_array($tracks['data'])) {
            dd('No data available or data is invalid');
        }

        // Extract unique genres efficiently
        $uniqueGenres = array_unique(array_filter(array_column($tracks['data'], 'genre')));

        // Insert unique genres into the database
        foreach ($uniqueGenres as $genre) {
            // Check if the genre already exists
            MusicGenre::firstOrCreate(['name' => $genre], [
                'slug' => \Str::slug($genre), // Create a slug for the genre
                'description' => null, // Optional: Add a description if available
                'is_active' => true,   // Set as active by default
            ]);
        }


        $this->info("Done Extracting");


    }
}
