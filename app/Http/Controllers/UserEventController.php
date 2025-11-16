<?php

namespace App\Http\Controllers;
use App\Models\SundayServiceAttendance;
use Illuminate\Http\Request;
use App\Models\Event;
use Carbon\Carbon;

class UserEventController extends Controller
{

    public function getUpcomingEvents(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $now = Carbon::now();

        // Filter for upcoming events only
        $events = Event::where(function ($query) use ($user) {
            $query->where('is_global', true)
                ->orWhere('branch_id', $user->branch_id);
        })
            ->where(function ($query) use ($now) {
                $query->where('event_date', '>', $now->toDateString())
                    ->orWhere(function ($query) use ($now) {
                        $query->where('event_date', $now->toDateString())
                            ->whereTime('start_time', '>', $now->toTimeString());
                    });
            })
            ->where('status', '!=', 'finished') // Exclude finished events
            ->where('status', '!=', 'ongoing')  // Exclude ongoing events
            ->orderBy('event_date', 'asc')
            ->orderBy('start_time', 'asc')
            ->get();

        // Attach attendance status for each event
        $attendances = SundayServiceAttendance::where('member_id', $user->id)
            ->pluck('status', 'event_id');

        $events = $events->map(function ($event) use ($attendances) {
            $event->attendance_status = $attendances[$event->id] ?? 'Not Recorded';
            $event->event_time = $event->start_time . ' - ' . $event->end_time;
            return $event;
        });

        return response()->json([
            'total_events' => $events->count(),
            'events' => $events,
        ]);
    }


}


