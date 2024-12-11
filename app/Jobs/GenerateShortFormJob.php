<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Arr;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;
use App\Models\UserMedia;
use App\Models\UserMediaSetting;
use App\Models\PublishedMedia;
use App\Models\MusicGenre;
use App\Models\User;
use App\Helper\AudiusHelper;
use App\Mail\Media\MailMediaLink;

class GenerateShortFormJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $ffmpegPath;
    protected $ffprobePath;
    protected $videoDuration = 2; // Duration of each trimmed video
    protected $totalVideo = 2;

    /**
     * Execute the job.
     */
    public function handle()
    {
        $this->ffmpegPath = env('FFMPEG_BINARIES', '/usr/bin/ffmpeg');
        $this->ffprobePath = env('FFPROBE_BINARIES', '/usr/bin/ffprobe');

        $users = UserMedia::where('type', 'video')->get()->unique('user_id');

        foreach ($users as $user) {
            $setting = UserMediaSetting::where('user_id', $user->user_id)->first();

            $localMusicPath = $this->getBackgroundMusicPath($setting, $user->user_id);

            if (!$localMusicPath) {
                continue;
            }

            $userMedia = UserMedia::where('user_id', $user->user_id)
                ->where('type', 'video')
                ->get()
                ->random($this->totalVideo);

            if ($userMedia->isEmpty()) {
                logger()->error("No videos found for user ID {$user->user_id}");
                continue;
            }

            $outputDir = storage_path('app/public/published/');
            if (!File::exists($outputDir)) {
                File::makeDirectory($outputDir, 0755, true);
            }

            $outputFileName = 'combined_reel_user_' . $user->user_id . '_' . now()->format('Ymd_His') . '.mp4';
            $outputPath = $outputDir . $outputFileName;

            $this->generateVideo($userMedia, $localMusicPath, $outputPath, $user->user_id);
        }
    }

    private function getBackgroundMusicPath($setting, $userId)
    {
        if ($setting->user_audio == true) {
            $userMedia = UserMedia::where('type', 'audio')
                ->where('user_id', $userId)
                ->inRandomOrder()
                ->first();

            return $userMedia
                ? Storage::disk($userMedia->storage)->path($userMedia->folder . '/' . $userMedia->filename)
                : null;
        } else {
            $randomGenre = is_array($setting->music_genre_id) && !empty($setting->music_genre_id)
                ? Arr::random($setting->music_genre_id)
                : null;

            $randomGenreName = MusicGenre::find($randomGenre)->value('name');
            $trendingTracks = AudiusHelper::getTrendingTracks($randomGenreName);
            $trackArray = $trendingTracks['data'] ?? [];
            $randomTrack = is_array($trackArray) && !empty($trackArray)
                ? Arr::random($trackArray)
                : null;

            $backgroundMusicPath = AudiusHelper::streamTrack($randomTrack['id']);
            $localMusicPath = Storage::disk('public')->path('music/' . uniqid('', true) . '.mp3');

            try {
                $musicContent = file_get_contents($backgroundMusicPath);
                file_put_contents($localMusicPath, $musicContent);
                return $localMusicPath;
            } catch (\Exception $e) {
                logger()->error("Failed to download background music: " . $e->getMessage());
                return null;
            }
        }
    }

    private function generateVideo($userMedia, $localMusicPath, $outputPath, $userId)
    {
        $inputFiles = [];
        $filterComplex = '';
        $concatParts = '';
        $validVideoCount = 0;

        foreach ($userMedia as $mediaFile) {
            $videoPath = Storage::disk($mediaFile->storage)->path("{$mediaFile->folder}/{$mediaFile->filename}");
            if (!file_exists($videoPath)) {
                logger()->error("Video file not found: $videoPath");
                continue;
            }

            $inputFiles[] = "-i " . escapeshellarg($videoPath);
            $filterComplex .= "[{$validVideoCount}:v]trim=duration={$this->videoDuration},setpts=PTS-STARTPTS,scale=1920:1080:force_original_aspect_ratio=decrease,pad=1920:1080:(ow-iw)/2:(oh-ih)/2[vid{$validVideoCount}];";
            $concatParts .= "[vid{$validVideoCount}]";
            $validVideoCount++;
        }

        if ($validVideoCount === 0) {
            logger()->error("No valid videos found for user ID {$userId}");
            return;
        }

        $totalVideoDuration = $validVideoCount * $this->videoDuration;

        $filterComplex .= "{$concatParts}concat=n={$validVideoCount}:v=1:a=0[outv];";
        $inputFiles[] = "-i " . escapeshellarg($localMusicPath);

        $audioDuration = 60;
        $randomStart = rand(0, $audioDuration - $totalVideoDuration);

        $filterComplex .= "[{$validVideoCount}:a]atrim=start={$randomStart}:duration={$totalVideoDuration},asetpts=PTS-STARTPTS,volume=1.9[finalaudio]";

        $ffmpegCmd = $this->ffmpegPath . ' ' . implode(' ', $inputFiles) .
            " -filter_complex \"" . $filterComplex . "\" " .
            " -map \"[outv]\" -map \"[finalaudio]\" -r 60 -y " . escapeshellarg($outputPath);

        exec($ffmpegCmd . ' 2>&1', $output, $returnVar);

        if ($returnVar === 0 && file_exists($outputPath)) {
            PublishedMedia::create([
                'url' => 'published/' . basename($outputPath),
                'user_id' => $userId,
            ]);

            $user = User::find($userId);
            if (!empty($user->email)) {
                $this->sendEmail($user->email, $outputPath);
            }
        } else {
            logger()->error("Failed to generate combined reel for user ID {$userId}. FFmpeg output: " . implode("\n", $output));
        }
    }

    private function sendEmail($email, $outputPath)
    {
        try {
            Mail::to($email)->send(new MailMediaLink($outputPath));
        } catch (\Exception $e) {
            logger()->error("Failed to send email: " . $e->getMessage());
        }
    }
}