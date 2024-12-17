<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\UserMedia;
use App\Jobs\GenerateShortFormJob;

use App\Helper\ShortFormGeneratorHelper;
use App\Helper\FfmpegHelper;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Storage;

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

        dd(FfmpegHelper::generateFrames('digital-assets/1/published/combined_reel_user_1_20241215_143619.mp4', $frameRate = 1, $width = 512, $outputPath = 'test/', $height = null));

        dd(GenerateShortFormJob::dispatch(1));

        dd(ShortFormGeneratorHelper::slideShow(1));

        dd(FfmpegHelper::detectFrameChanges('assets/clips/micheal.mp4'));
                
    }
}
