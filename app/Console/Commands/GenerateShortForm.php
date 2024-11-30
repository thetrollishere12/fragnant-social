<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Storage;
use Carbon\Carbon;

use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;

use App\Models\UserMedia;
use App\Models\UserMediaSetting;
use App\Models\PublishedMedia;
use App\Models\MusicGenre;


use Illuminate\Support\Arr;

use Illuminate\Support\Facades\File;

use App\Helper\AudiusHelper;



use App\Mail\Media\MailMediaLink;
use Illuminate\Support\Facades\Mail;


use App\Models\User;



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
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    





public function handle()
{
    $this->ffmpegPath = env('FFMPEG_BINARIES', '/usr/bin/ffmpeg');
    $this->ffprobePath = env('FFPROBE_BINARIES', '/usr/bin/ffprobe');

    $videoDuration = 2; // Duration of each trimmed video

    $totalVideo = 9;

    $users = UserMedia::where('type', 'video')->get()->unique('user_id');

    foreach ($users as $user) {
        $setting = UserMediaSetting::where('user_id', $user->user_id)->first();

        if($setting->user_audio == true){

            $user_media = UserMedia::where('type', 'audio')
            ->where('user_id', $user->user_id)
            ->inRandomOrder() // Fetch random records safely
            ->first();

            $localMusicPath = Storage::disk($user_media->storage)->path($user_media->folder.'/'.$user_media->filename);

            $this->info("Background music selected from user media: $localMusicPath");

        }else{

            $randomGenre = is_array($setting->music_genre_id) && !empty($setting->music_genre_id)
                ? Arr::random($setting->music_genre_id)
                : null;

            $randomGenre = MusicGenre::find($randomGenre)->value('name');
            $trendingTracks = AudiusHelper::getTrendingTracks($randomGenre);
            $trackArray = $trendingTracks['data'] ?? [];
            $randomTrack = is_array($trackArray) && !empty($trackArray)
                ? Arr::random($trackArray)
                : null;

            $backgroundMusicPath = AudiusHelper::streamTrack($randomTrack['id']);
            $localMusicPath = Storage::disk('public')->path('music/' . uniqid('', true) . '.mp3');

            try {
                $musicContent = file_get_contents($backgroundMusicPath);
                file_put_contents($localMusicPath, $musicContent);
                $this->info("Background music downloaded to: $localMusicPath");
            } catch (\Exception $e) {
                $this->error("Failed to download background music: " . $e->getMessage());
                return;
            }

        }

        $userMedia = UserMedia::where('user_id', $user->user_id)
            ->where('type', 'video')
            ->get()
            ->random($totalVideo);

        if ($userMedia->isEmpty()) {
            $this->error("No videos found for user ID {$user->user_id}");
            continue;
        }

        $outputDir = storage_path('app/public/published/');
        if (!File::exists($outputDir)) {
            File::makeDirectory($outputDir, 0755, true);
            $this->info("Path - {$outputDir} doesn't exist and created");
        }

        $outputFileName = 'combined_reel_user_' . $user->user_id . '_' . now()->format('Ymd_His') . '.mp4';
        $outputPath = $outputDir . $outputFileName;

        if (!file_exists($localMusicPath)) {
            $this->error("Background music file not found: $localMusicPath");
            continue;
        }

        $inputFiles = [];
        $filterComplex = '';
        $concatParts = '';
        $validVideoCount = 0;

        foreach ($userMedia as $index => $mediaFile) {
            $videoPath = Storage::disk($mediaFile->storage)->path("{$mediaFile->folder}/{$mediaFile->filename}");
            if (!file_exists($videoPath)) {
                $this->error("Video file not found: $videoPath");
                continue;
            }

            $inputFiles[] = "-i " . escapeshellarg($videoPath);
            $filterComplex .= "[{$validVideoCount}:v]trim=duration={$videoDuration},setpts=PTS-STARTPTS,scale=1920:1080:force_original_aspect_ratio=decrease,pad=1920:1080:(ow-iw)/2:(oh-ih)/2[vid{$validVideoCount}];";
            $concatParts .= "[vid{$validVideoCount}]";
            $validVideoCount++;
        }

        if ($validVideoCount === 0) {
            $this->error("No valid videos found for user ID {$user->user_id}");
            continue;
        }

        // Total duration of the concatenated video
        $totalVideoDuration = $validVideoCount * $videoDuration;

        $filterComplex .= "{$concatParts}concat=n={$validVideoCount}:v=1:a=0[outv];";
        $inputFiles[] = "-i " . escapeshellarg($localMusicPath);

        // Random audio start time
        $audioDuration = 60; // Replace with actual duration
        $randomStart = rand(0, $audioDuration - $totalVideoDuration);

        $filterComplex .= "[{$validVideoCount}:a]atrim=start={$randomStart}:duration={$totalVideoDuration},asetpts=PTS-STARTPTS,volume=1.9[finalaudio]";

        // FFmpeg command
        $ffmpegCmd = $this->ffmpegPath . ' ' . implode(' ', $inputFiles) .
            " -filter_complex \"" . $filterComplex . "\" " .
            " -map \"[outv]\" -map \"[finalaudio]\" -r 60 -y " . escapeshellarg($outputPath);

        exec($ffmpegCmd . ' 2>&1', $output, $returnVar);

        if ($returnVar === 0 && file_exists($outputPath)) {
            $this->info("Combined reel generated for user ID {$user->user_id}: $outputPath");

            $published = PublishedMedia::create([
                'url' => 'published/' . $outputFileName,
                'user_id' => $user->user_id,
            ]);

            $user = User::find($user->user_id);
            if (!empty($user->email)) {
                $this->sendEmail($user->email, $published);
            }
        } else {
            $this->error("Failed to generate combined reel for user ID {$user->user_id}. FFmpeg output: " . implode("\n", $output));
        }
    }
}






    private function sendEmail($email,$publishedMedia)
    {

        try {
            Mail::to($email)->send(new MailMediaLink($publishedMedia));
            $this->info("Email sent successfully with the reel links.");
        } catch (\Exception $e) {
            $this->error("Failed to send email: " . $e->getMessage());
        }

    }





    
}
