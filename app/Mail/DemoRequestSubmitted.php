<?php

namespace App\Mail;

use App\Models\RequestDemo;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DemoRequestSubmitted extends Mailable
{
    use Queueable, SerializesModels;

    public $requestDemo;

    public function __construct(RequestDemo $requestDemo)
    {
        $this->requestDemo = $requestDemo;
    }

    public function build()
    {
        return $this->subject('New Demo Request Submitted')
                    ->view('emails.demo_request_submitted');
    }
}
