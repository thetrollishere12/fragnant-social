<?php

namespace App\Livewire\Admin;

use Livewire\Component;

use App\Jobs\Admin\TriggerEventButtonJob;

class TriggerEventButton extends Component
{
    public $className;
    public $message;
    public $recipientEmail;
    public $subject;
    public $text;

    public function mount($text = 'Click Here', $className = 'default-container', $subject = 'Default Title', $message = 'Default message')
    {
        $this->text = $text;
        $this->className = $className;
        $this->recipientEmail = env('RECEIPT_EMAIL', 'branondonsanghuynh123@gmail.com'); // Replace with your default email
        $this->subject = $subject;
        $this->message = $message;
    }

    public function sendEmail()
    {
        $userId = auth()->check() ? 'User ID: ' . auth()->id() : 'guest';
        $this->message .= ' - This email was triggered by ' . $userId . '.';

        TriggerEventButtonJob::dispatch($this->recipientEmail, $this->subject, $this->message);
    }

    public function render()
    {
        return view('livewire.admin.trigger-event-button');
    }
}
