<!DOCTYPE html>
<html>
<head>
    <title>Event Reminder</title>
</head>
<body>
    <p>Dear {{ $member->first_name ?? 'Member' }},</p>

    <p>This is a reminder for the upcoming event:</p>

    <ul>
        <li><strong>Title:</strong> {{ $event->title }}</li>
        <li><strong>Date:</strong> {{ \Carbon\Carbon::parse($event->event_date)->format('F d, Y') }}</li>
        <li><strong>Time:</strong> {{ \Carbon\Carbon::parse($event->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($event->end_time)->format('h:i A') }}</li>
        <li><strong>Location:</strong> {{ $event->location }}</li>
    </ul>

    <p>Please be guided accordingly.</p>

    <p>God bless!</p>
</body>
</html>
