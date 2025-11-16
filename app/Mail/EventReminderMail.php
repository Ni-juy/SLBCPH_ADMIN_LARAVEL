<?php

namespace App\Mail;

use App\Models\Event;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EventReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public $event;
    public $member;

    public function __construct(Event $event, User $member)
    {
        $this->event = $event;
        $this->member = $member;
    }

    public function build()
    {
        return $this->subject('Event Reminder: ' . $this->event->title)
                    ->view('emails.event_reminder');
    }
}
