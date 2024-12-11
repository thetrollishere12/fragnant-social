<?php

namespace App\Livewire;
use Livewire\Component;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendMail;
use ReCaptcha\ReCaptcha;

class Contact extends Component
{
    public $email;
    public $title;
    public $message;
    public $gRecaptchaResponse;

    protected $rules = [
        'email' => 'required|email',
        'title' => 'required|string',
        'message' => 'required|string|min:10',
        'gRecaptchaResponse' => 'required|string',
    ];

    protected $listeners = ['recaptchaValidated'];

    public function recaptchaValidated($token)
    {
        $this->gRecaptchaResponse = $token;
    }

    public function send()
    {
        $this->validate();

        // ReCaptcha validation
        $recaptcha = new ReCaptcha(env('GOOGLE_RECAPTCHA_SECRET_KEY'));
        $response = $recaptcha->verify($this->gRecaptchaResponse, request()->ip());

        if (!$response->isSuccess()) {
            $this->addError('gRecaptchaResponse', 'Please complete the ReCaptcha verification.');
            return;
        }

        // Email sending logic
        $data = [
            'email' => $this->email,
            'title' => $this->title,
            'message' => $this->message,
        ];

        Mail::to(env('ALTERNATIVE_MAIL_CONTACT_ADDRESS'))->send(new SendMail($data));

        // Reset fields
        $this->reset(['email', 'title', 'message', 'gRecaptchaResponse']);

        // Show success message
        session()->flash('success', 'Thank you for contacting us.');
    }

    public function render()
    {
        return view('livewire.contact');
    }
}