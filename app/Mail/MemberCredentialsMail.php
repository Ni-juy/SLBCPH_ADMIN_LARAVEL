<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MemberCredentialsMail extends Mailable
{
    use Queueable, SerializesModels;

    public $member;
    public $password;

    public function __construct($member, $password)
    {
        $this->member = $member;
        $this->password = $password;
    }

    public function build()
    {
        return $this->subject('Your Church Member Account')
                    ->view('emails.member_credentials');
    }
}
