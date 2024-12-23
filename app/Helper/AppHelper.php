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



    public static function downloadContent($url, $disk = 'public', $path = true)
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





public static function extractFileDetails($input)
{
    // Normalize the input to use forward slashes
    $input = str_replace('\\', '/', $input);

    // Initialize details array
    $details = [
        'storage_disk' => null,
        'full_path' => null,
        'relative_path' => null,
        'folder_name' => null,
        'file_name' => null,
        'file_name_with_extension' => null,
        'file_extension' => null,
        'url' => null,
    ];

    // Define storage paths for public and local disks
    $publicRoot = str_replace('\\', '/', storage_path('app/public/'));
    $localRoot = str_replace('\\', '/', storage_path('app/'));

    // Helper function to extract relative path
    $extractRelativePath = function ($path, $root) {
        if (strpos($path, $root) === 0) {
            return substr($path, strlen($root));
        } elseif (preg_match('/storage\/(.*)$/', $path, $matches)) {
            return $matches[1];
        }
        return null;
    };

    // Determine which disk the file exists on
    if (strpos($input, $publicRoot) === 0 || Storage::disk('public')->exists($extractRelativePath($input, $publicRoot))) {
        $details['storage_disk'] = 'public';
        $details['relative_path'] = $extractRelativePath($input, $publicRoot);
        $details['full_path'] = storage_path('app/public/' . $details['relative_path']);
    } elseif (strpos($input, $localRoot) === 0 || Storage::disk('local')->exists($extractRelativePath($input, $localRoot))) {
        $details['storage_disk'] = 'local';
        $details['relative_path'] = $extractRelativePath($input, $localRoot);
        $details['full_path'] = storage_path('app/' . $details['relative_path']);
    } else {
        throw new \InvalidArgumentException('File not found on either public or local disk.');
    }

    // Extract folder name and file details
    if ($details['relative_path']) {
        $pathInfo = pathinfo($details['relative_path']);
        $details['folder_name'] = $pathInfo['dirname'] !== '.' ? $pathInfo['dirname'] : null;
        $details['file_name'] = $pathInfo['filename'];
        $details['file_name_with_extension'] = $pathInfo['basename'];
        $details['file_extension'] = $pathInfo['extension'] ?? null;
    }

    // Generate the storage URL
    $details['url'] = Storage::disk($details['storage_disk'])->url($details['relative_path']);

    return $details;

// Array
// (
//     [storage_disk] => public
//     [full_path] => /path/to/project/storage/app/public/uploads/images/sample.jpg
//     [relative_path] => uploads/images/sample.jpg
//     [folder_name] => uploads/images
//     [file_name] => sample
//     [file_name_with_extension] => sample.jpg
//     [file_extension] => jpg
//     [url] => http://example.com/storage/uploads/images/sample.jpg
// )
    
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