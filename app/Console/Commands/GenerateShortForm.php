<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\UserMedia;
use App\Jobs\GenerateShortFormJob;

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
        $users = UserMedia::where('type', 'video')->get()->unique('user_id');

        foreach ($users as $user) {
            GenerateShortFormJob::dispatch($user->user_id);
            $this->info("Dispatched job for user ID {$user->user_id}");
        }
    }
}
