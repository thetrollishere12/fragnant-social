<?php

namespace App\Jobs\Admin;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class TriggerEventButtonJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $recipientEmail;
    public $subject;
    public $message;

    /**
     * Create a new job instance.
     *
     * @param string $recipientEmail
     * @param string $subject
     * @param string $message
     */
    public function __construct(string $recipientEmail, string $subject, string $message)
    {
        $this->recipientEmail = $recipientEmail;
        $this->subject = $subject;
        $this->message = $message;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Mail::raw($this->message, function ($mail) {
                $mail->to($this->recipientEmail)
                     ->subject($this->subject);
            });
        } catch (\Exception $e) {
            // Handle email sending failure, e.g., log the error
            \Log::error('Failed to send email: ' . $e->getMessage());
        }
    }
}