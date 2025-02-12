<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\UserMedia;
use App\Jobs\GenerateShortFormJob;

use App\Helper\ShortFormGeneratorHelper;
use App\Helper\FFmpegHelper;
use App\Helper\EssentiaHelper;
use App\Helper\Editor\ImageHelper;


use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Storage;



use Illuminate\Support\Facades\Mail;
use App\Mail\Media\MailMediaLink;

use App\Models\PublishedDetail;


use App\Models\Product\ProductImportFeed;

use Illuminate\Support\Facades\Http;
use App\Models\Product\Product;
use App\Models\Product\ProductDetail;
use App\Models\Product\ProductMedia;

use App\Helper\Editor\StructureHelper;

use Session;

use App\Helper\AppHelper;

class GenerateShortForm extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-short-form';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dispatch jobs to generate short-form videos for users';

    /**
     * Execute the console command.
     */
    public function handle()
    {


$productMedia = Product::find(1)->media->random(1)->first();

$content = AppHelper::extractFileDetails(AppHelper::downloadContent(url:$productMedia->url, disk:'local'));

$image = ImageHelper::transparent_background($content['full_path']);

$overlay = ImageHelper::imageOverlay($image);

dd($overlay);



// Store it into the session
Session::put('video_project', $test);
return 1;
dd(StructureHelper::generateFFmpegCommand($test));



    $feed = ProductImportFeed::first();

    if (!$feed || !filter_var($feed->url, FILTER_VALIDATE_URL)) {
        return dd("Invalid or missing URL.");
    }

    $fileType = pathinfo(parse_url($feed->url, PHP_URL_PATH), PATHINFO_EXTENSION);

    // Fetch the file contents
    $response = Http::get($feed->url);

    if (!$response->successful()) {
        return dd("Failed to retrieve the file. Status: " . $response->status());
    }

    $fileContents = $response->body();
    $processedData = [];

    switch (strtolower($fileType)) {
        case 'csv':
            $processedData = $this->parseCsv($fileContents);
            break;
        case 'json':
            $processedData = $this->parseJson($fileContents);
            break;
        case 'xml':
            $processedData = $this->parseXml($fileContents);
            break;
        case 'xlsx':
        case 'xls':
            $processedData = $this->parseExcel($feed->url);
            break;
        default:
            return dd("Unsupported file type: " . $fileType);
    }

    // Preview the first few entries

    foreach($processedData as $data){

        $product = Product::updateOrCreate([
            'digital_asset_id'=>$feed->digital_asset_id,
            'code_id'=>$data['id'],
            'platform' =>'Import Feed',
            'platform_id' =>$feed->id
        ]);

        $detail = ProductDetail::updateOrCreate([
            'product_id' => $product->id,
        ],[
            'name' =>$data['title'],
            'description' => $data['description'],
            'price' => $data['price'],
            'sale_price' => $data['sale_price'],
        ]);

        $image = ProductMedia::updateOrCreate([
            'product_id' => $product->id,
            'url' => $data['image_link']
        ]);

        $this->line("<fg=blue>Added to product</>");

    }



                
    }





    public function parseCsv($fileContents)
{
    $lines = explode("\n", $fileContents);
    $header = str_getcsv(array_shift($lines)); // Get column names

    $data = [];
    foreach ($lines as $line) {
        $row = str_getcsv($line);
        if (count($row) == count($header)) {
            $data[] = array_combine($header, $row);
        }
    }

    return $data;
}




public function parseJson($fileContents)
{
    return json_decode($fileContents, true) ?? [];
}

public function parseXml($fileContents)
{
    $xml = simplexml_load_string($fileContents, "SimpleXMLElement", LIBXML_NOCDATA);
    $json = json_encode($xml);
    return json_decode($json, true);
}



}
