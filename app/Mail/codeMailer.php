<?php

namespace App\Mail;
 use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class codeMailer extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public $user;
    public $code;
    public function __construct(User $user, $code)
    {
        $this->user = $user;
        $this->code = $code;
    }

    /**
     * Get the message envelope.
     */
    /** 
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '2Fa Code Mailer',
        );
    }

    
    
    public function content(): Content
    {
        return new Content(
            view: 'view.name',
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
    public function build()
    {
        return $this->view('email.sendcode')
                    ->subject('Your 2FA Verification Code')
                    ->with([
                        'username' => $this->user->username,
                        'code' => $this->code,
                    ]);
    }
}
