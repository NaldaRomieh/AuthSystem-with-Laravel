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

    /**
     * Handle the event.
     */
    public function handle(SignupEvent $event): void
    {

        $user = $event->user;
        Mail::to($user->email)->send(new VerificationCodeMail($user->email_verification_code));

        
    }
}
