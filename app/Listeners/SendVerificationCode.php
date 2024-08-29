<?php

namespace App\Listeners;

use App\Events\SignupEvent;
use App\Mail\VerificationCodeMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;
use App\Models\User;

class SendVerificationCode
{
    /**
     * Create the event listener.
     */
    public $user;
    public function __construct()
    {
        
    }

    /**
     * Handle the event.
     */
    public function handle(SignupEvent $event): void
    {
        $user = $event->user;
        $verificationCode = $user->email_verification_code;
        Mail::to($user->email)->send(new VerificationCodeMail($verificationCode));

        //Mail::to($event->user->email)->send(new VerificationCodeMail($event->user->email->email_verification_code));
    }
}
