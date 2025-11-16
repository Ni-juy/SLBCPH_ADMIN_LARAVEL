<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Event;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\EventReminderMail;
use Illuminate\Support\Facades\Log;

class SendEventReminderEmails extends Command
{
    protected $signature = 'email:send-event-reminders';
    protected $description = 'Send reminder emails to members 1 day before an event';

   public function handle()
{
    $tomorrow = Carbon::tomorrow('Asia/Manila')->toDateString();

    $events = Event::where('event_date', $tomorrow)->get();

    if ($events->isEmpty()) {
        Log::info("[Event Reminder] No events scheduled for {$tomorrow}.");
        return 0;
    }

    foreach ($events as $event) {
        $members = User::where('branch_id', $event->branch_id)
                       ->where('role', 'Member')
                       ->whereNotNull('email')
                       ->get();

        if ($members->isEmpty()) {
            Log::info("[Event Reminder] No members found for branch ID {$event->branch_id} (Event: {$event->title}).");
            continue;
        }

        foreach ($members as $member) {
            Mail::to($member->email)->queue(new EventReminderMail($event, $member));
            Log::info("[Event Reminder] Email queued to {$member->email} for event '{$event->title}' on {$event->event_date}.");
        }
    }

    return 0;
}
}
