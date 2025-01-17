<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\UserMedia;
use App\Jobs\GenerateShortFormJob;

use App\Helper\ShortFormGeneratorHelper;
use App\Helper\FFmpegHelper;
use App\Helper\EssentiaHelper;


use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Storage;



use Illuminate\Support\Facades\Mail;
use App\Mail\Media\MailMediaLink;

use App\Models\PublishedDetail;


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


        $media = PublishedDetail::find(25);

try{
         Mail::to('brandonsanghuynh123@gmail.com')->send(new MailMediaLink($media));
dd(31);
}catch(\Exception $e){
    dd(1111);
}
         // dd(EssentiaHelper::processAudio(storage_path('app/public/assets/music/67603f450b03a5.64014798.mp3')));



        dd(ShortFormGeneratorHelper::textOverlayVideo(2));



        dd(ShortFormGeneratorHelper::OneFrameSnippet(digital_asset_id:1, template_id:2));
            
        // dd(FFmpegHelper::generateFrames('digital-assets/1/published/combined_reel_user_1_20241215_143619.mp4', $frameRate = 1, $width = 512, $outputPath = 'test/', $height = null));

        // dd(GenerateShortFormJob::dispatch(1));

        dd(ShortFormGeneratorHelper::slideShow(1));

        dd(FFmpegHelper::detectFrameChanges('assets/clips/micheal.mp4'));
                
    }
}
