<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB; 

class VerifyEmailMail extends Mailable
{
    use Queueable, SerializesModels;

    public $member;
    public $password;
      public $verifyUrl;

    /**
     * Create a new message instance.
     */
  public function __construct(User $member, string $verifyUrl)
{
    $this->member    = $member;
    $this->verifyUrl = $verifyUrl;
}

public function build()
{
    return $this->subject('Verify Your Account')
        ->view('emails.verify')
        ->with([
            'member'    => $this->member,
            'verifyUrl' => $this->verifyUrl,
        ]);
}


}
