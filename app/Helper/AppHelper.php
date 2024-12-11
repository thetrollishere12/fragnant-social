<?php

namespace App\Helper;

use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\SearchQuery;
use Illuminate\Support\Str;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\Subset;

use Illuminate\Contracts\Encryption\Encrypter;
use App\Models\SystemScriptElement;
use Storage;

use Http;

use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;


use App\Models\WebsiteBlock;
use Illuminate\Support\Facades\Cache;


class AppHelper
{

    public static function random_id($prefix = null){

        return $prefix.Str::random(4).uniqid().Auth::id();

    }





    public static function global_time(){

        $ip = file_get_contents("http://ipecho.net/plain");
        $url = 'http://ip-api.com/json/'.$ip;
        $tz = file_get_contents($url);
        $tz = json_decode($tz,true)['timezone'];
        return $tz;
    }




    public static function getWebsiteBlocks(){

            return Cache::remember('website_blocks', 600, function () {
                $blocksCollection = WebsiteBlock::all();
                $blocks = [];

                foreach ($blocksCollection as $block) {
                    switch ($block->block_type) {
                        case 'string':
                            $blocks[$block->block_key] = (string) $block->block_value;
                            break;
                        case 'boolean':
                            $blocks[$block->block_key] = filter_var($block->block_value, FILTER_VALIDATE_BOOLEAN);
                            break;
                        case 'integer':
                            $blocks[$block->block_key] = (int) $block->block_value;
                            break;
                        case 'float':
                            $blocks[$block->block_key] = (float) $block->block_value;
                            break;
                        case 'array':
                            $blocks[$block->block_key] = json_decode($block->block_value, true);
                            break;
                        case 'json':
                            $blocks[$block->block_key] = json_decode($block->block_value, true);
                            break;
                        case 'date':
                            $blocks[$block->block_key] = Carbon::parse($block->block_value);
                            break;
                        case 'serialized':
                            $blocks[$block->block_key] = unserialize($block->block_value);
                            break;
                        default:
                            $blocks[$block->block_key] = $block->block_value;
                            break;
                    }
                }

                return $blocks;
            });

    }




    public static function block_color($value){

        $element = SystemScriptElement::where('label',$value)->first();

        if($element){
            return $element->color;
        }else{
            return 'pink';
        }

    }



    public static function downloadContent($url,$disk = 'public',$path = true)
    {

        $response = Http::get($url);

        $extension = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION);

        $fileName = 'livewire-tmp/temp_' . uniqid() . '.' . $extension;
     
        Storage::disk($disk)->put($fileName, $response->body());

        if ($path) {
           return Storage::disk($disk)->path($fileName);
        }else{
            return $fileName;
        }

        

    }



    public static function urlToPath($url)
    {
        // Ensure the URL is a valid storage URL
        if (!preg_match('/storage\/(.*)$/', $url, $matches)) {
            throw new \InvalidArgumentException('Invalid storage URL.');
        }

        // Extract the relative path from the URL
        $relativePath = $matches[1];
        // Construct the full storage path
        $storagePath = storage_path('app/public/' . $relativePath);

        return $storagePath;
    }

    /**
     * Convert a file path to a storage URL.
     *
     * @param string $path
     * @param string $disk
     * @return string
     */
    public static function pathToUrl($path, $disk = 'public')
    {
        
        return Storage::disk($disk)->url(AppHelper::extractRelativePath($path));

    }




public static function extractRelativePath($input) {
    
    // Normalize input to use forward slashes
    $input = str_replace('\\', '/', $input);

    

    // Define the base storage path
    $storageRoot = str_replace('\\', '/', storage_path('app/public/'));

    // Check if the input is a local path within the storage directory
    if (strpos($input, $storageRoot) === 0) {
        return substr($input, strlen($storageRoot));
    }



    // Check if the input is a URL with "storage/"
    if (preg_match('/storage\/(.*)$/', $input, $matches)) {
        return $matches[1];
    }
    

}





public static function generateRedditPost($body = "Reddit Body", $username = "Reddit User") {
    $badges = Storage::disk('public')->files('image/reddit-reward-badge');

    $badges = array_map(function ($badge) {
        return Storage::disk('public')->path($badge); // Convert to local path
    }, $badges);

    // Encode badge paths as a JSON string with properly escaped double quotes
    $badgesJson = json_encode($badges, JSON_UNESCAPED_SLASHES);

    // Output path for the screenshot
    $output = Storage::disk('public')->path('screenshot/reddit_' . AppHelper::random_id('sc') . '.png');

    // Command to execute the Node.js script with the parameters
    $command = '"C:\\Program Files\\nodejs\\node.exe" "C:\\xampp\\htdocs\\contentplanner\\resources\\js\\media-post-template\\reddit.js" '
        . escapeshellarg($badgesJson) . " "
        . escapeshellarg($output) . " "
        . escapeshellarg($body) . " "
        . escapeshellarg($username);

    // Execute the command
    exec($command, $ffmpeg_output, $return_var);

    // Check if the process ran successfully
    if ($return_var !== 0) {
        throw new ProcessFailedException($command);
    }

    // Path to the generated image
  
    if (!file_exists($output)) {
        return response()->json(['error' => 'Image not generated'], 500);
    }

    // Return the image as a response
    return $output;
    
}










    public static function generateDiscordPost(){

        // output
        $output = storage::disk('public')->path('screenshot/discord_'.AppHelper::random_id('sc').'.png');


        $command = '"C:\\Program Files\\nodejs\\node.exe" "C:\\xampp\\htdocs\\contentplanner\\resources\\js\\media-post-template\\discord.js" '
        .escapeshellarg("[]")." "
        .escapeshellarg($output);

        // Output the command to check formatting

        exec($command, $output_val, $return_var);


        // Check if the process ran successfully
        if ($return_var !== 0) {
            throw new ProcessFailedException($command);
        }

        // Path to the generated image
        $imagePath = $output;
        if (!file_exists($imagePath)) {
            return response()->json(['error' => 'Image not generated'], 500);
        }

        // Return the image as a response
        return response()->file($imagePath);

    }






}