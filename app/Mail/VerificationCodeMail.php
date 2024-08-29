<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Http\Controllers\AuthController;

class VerificationCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public $email_verification_code;

    public function __construct($email_verification_code)
    {
        $this->email_verification_code = $email_verification_code;
    }

    /**
     * Get the message envelope.
     */
    /*
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Verification Code Mail',
        );
    }

    
    public function content(): Content
    {
        return new Content(
            view: 'email.verification-code',
        );
    }
      */
    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        dd($this->email_verification_code);
        return $this->subject('Your Email Verification Code')
            ->view('email.verification-code')
            ->with([
                'verificationCode' => $this->email_verification_code,
            ]);
    }
}
