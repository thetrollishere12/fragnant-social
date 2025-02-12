<?php

namespace App\Console\Commands\Product;

use Illuminate\Console\Command;
use Http;
use App\Models\Product\ProductMedia;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class DetectBackground extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:detect-background';

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
        // Fetch media that haven't been checked
        $medias = ProductMedia::whereNull('transparent')->get();

        if ($medias->isEmpty()) {
            $this->info("No images to process.");
            return;
        }

        foreach ($medias as $media) {
            $this->info("Processing image: {$media->url}");

            try {
                // Step 1: Download the image locally
                $localPath = $this->downloadImage($media->url);

                if (!$localPath) {
                    $this->error("Failed to download image: {$media->url}");
                    continue;
                }

                // Step 2: Run the Python script
                $isTransparent = $this->detectBackground($localPath);

                // Step 3: Update the database
                $media->update(['transparent' => $isTransparent]);

                $this->info("Updated media ID {$media->id} with transparency: " . ($isTransparent ? 'True' : 'False'));

                // Step 4: Delete the downloaded image
                unlink($localPath);
            } catch (\Exception $e) {
                $this->error("Error processing image {$media->id}: " . $e->getMessage());
            }
        }

        $this->info("Background detection process completed.");
    }

    /**
     * Downloads an image from a URL and saves it locally.
     * 
     * @param string $url
     * @return string|false The local file path or false on failure.
     */
    private function downloadImage($url)
    {
        try {
            $imageContent = Http::get($url)->body();
            $fileName = 'temp_' . md5($url) . '.jpg';
            $localPath = storage_path("app/temp/{$fileName}");

            // Ensure the temp directory exists
            if (!file_exists(storage_path('app/temp'))) {
                mkdir(storage_path('app/temp'), 0755, true);
            }

            file_put_contents($localPath, $imageContent);
            return $localPath;
        } catch (\Exception $e) {
            logger()->error("Failed to download image: {$e->getMessage()}");
            return false;
        }
    }

    /**
     * Runs the Python script to detect background transparency.
     * 
     * @param string $imagePath
     * @return bool True if background is transparent, false otherwise.
     */
    private function detectBackground($imagePath)
    {
        $pythonScriptPath = base_path('python/background_image/detect.py');
        $process = new Process(['python', $pythonScriptPath, $imagePath]);

        $process->run();

        if (!$process->isSuccessful()) {
            logger()->error("Python detection script failed: {$process->getErrorOutput()}");
            throw new ProcessFailedException($process);
        }

        return trim($process->getOutput()) === "True";
    }









}
