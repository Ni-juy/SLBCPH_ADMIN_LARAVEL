<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class AdminVerifyEmailMail extends Mailable
{
    use Queueable, SerializesModels;

    public $admin;
    public $verifyUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(User $admin, string $verifyUrl)
    {
        $this->admin = $admin;
        $this->verifyUrl = $verifyUrl;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Verify Your Admin Account')
                    ->view('emails.verify_admin') // points to verify_admin.blade.php
                    ->with([
                        'admin'     => $this->admin,
                        'verifyUrl' => $this->verifyUrl,
                    ]);
    }
}
