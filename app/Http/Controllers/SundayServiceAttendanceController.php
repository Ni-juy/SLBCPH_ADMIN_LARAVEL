<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\SundayServiceAttendance;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\AccountInactiveMail;


class SundayServiceAttendanceController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $branchId = $user->branch_id;

        $now = Carbon::now('Asia/Manila');
        $today = $now->toDateString();

        $ongoingEvents = Event::where(function ($query) use ($branchId) {
            $query->where('is_global', true)
                ->orWhere('branch_id', $branchId);
        })
            ->where('status', 'ongoing')
            ->where('event_date', $today)
            ->orderBy('start_time', 'asc')
            ->get();

        $finishedEvents = Event::where('branch_id', $branchId)
            ->where('status', 'finished')
            ->where('event_date', $today)
            ->get();

        foreach ($finishedEvents as $event) {
            $memberIds = User::where('branch_id', $branchId)
                ->where('role', 'member')
                ->pluck('id');

            foreach ($memberIds as $memberId) {
                $alreadyHasAttendance = SundayServiceAttendance::where([
                    'event_id' => $event->id,
                    'member_id' => $memberId,
                ])->exists();

                if (!$alreadyHasAttendance) {
                    SundayServiceAttendance::create([
                        'event_id' => $event->id,
                        'member_id' => $memberId,
                        'branch_id' => $event->branch_id,
                        'service_date' => $event->event_date,
                        'status' => 'Missed',
                    ]);
                }
            }
        }

        $attendances = SundayServiceAttendance::where('member_id', $user->id)
            ->whereIn('event_id', $ongoingEvents->pluck('id'))
            ->pluck('status', 'event_id');

        $ongoingEvents = $ongoingEvents->map(function ($event) use ($attendances) {
            $event->attendance_status = $attendances[$event->id] ?? 'Not Recorded';
            $event->event_time = $event->start_time . ' - ' . $event->end_time;
            return $event;
        });

        return response()->json([
            'events' => $ongoingEvents,
            'total_events' => $ongoingEvents->count(),
        ]);
    }

    public function store(Request $request)
{
    $validatedData = $request->validate([
        'event_id' => 'required|exists:events,id',
        'status' => 'required|in:Attended,Missed',
    ]);

    $user = Auth::user();
    $event = Event::findOrFail($validatedData['event_id']);

    $attendance = SundayServiceAttendance::firstOrNew([
        'member_id' => $user->id,
        'event_id' => $event->id,
        'service_date' => $event->event_date,
    ]);

    $attendance->status = $validatedData['status'];
    $attendance->branch_id = $user->branch_id;
    $attendance->save();

    // ✅ Skip attendance check if flagged (e.g., just unarchived)
    if ($user->skip_attendance_check) {
        $user->skip_attendance_check = 0; // reset the flag
        $user->save();
    } else {
        // Evaluate recent attendances based on event date + start time
        $recent = SundayServiceAttendance::join('events', 'sunday_service_attendance.event_id', '=', 'events.id')
            ->where('sunday_service_attendance.member_id', $user->id)
            ->orderByDesc(DB::raw("CONCAT(events.event_date, ' ', events.start_time)"))
            ->select('sunday_service_attendance.*')
            ->take(10) // check last 10 attendances for consecutive misses
            ->get();

        $missedCount = 0;
        foreach ($recent as $attendance) {
            if ($attendance->status === 'Missed') {
                $missedCount++;
            } elseif ($attendance->status === 'Attended') {
                break; // reset missed count on attendance
            }
        }

        if ($missedCount >= 3) {
            $this->setInactive($user);
        } else {
            if (strtolower($user->status) === 'inactive') {
                $user->status = 'Active';
                $user->save();
            }
        }
    }

    return response()->json(['message' => 'Attendance recorded successfully!'], 201);
}


    protected function setInactive(User $user)
{
    // Do not set inactive if member is archived
    if (strtolower($user->status) === 'archived') {
        return;
    }

    if (strtolower($user->status) !== 'inactive') {
        $user->status = 'Inactive';
        $user->save();

        // ✅ Send inactive email once
        if (!empty($user->email)) {
            try {
                Mail::to($user->email)->send(new AccountInactiveMail($user));
                \Log::info("Inactive email sent to {$user->email}");
            } catch (\Exception $mailEx) {
                \Log::error("Failed to send inactive email to {$user->email}: " . $mailEx->getMessage());
            }
        }
    }
}


    public function userAttendance()
    {
        $attendances = SundayServiceAttendance::where('member_id', Auth::id())->get();
        return response()->json($attendances);
    }

    public function checkAttendance(Request $request)
    {
        Log::info('Check attendance request received', [
            'user_id' => Auth::id(),
            'request_data' => $request->all(),
        ]);

        $validatedData = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'event_id' => 'required|exists:events,id',
            'service_date' => 'required|date',
        ]);

        $attendance = SundayServiceAttendance::where([
            'member_id' => Auth::id(),
            'branch_id' => $validatedData['branch_id'],
            'event_id' => $validatedData['event_id'],
            'service_date' => $validatedData['service_date'],
        ])->first();

        return response()->json($attendance ? true : false);
    }

    public function adminIndex()
    {

        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $branchId = Auth::user()->branch_id;

        // Fetch events first
        $events = Event::where(function ($query) use ($branchId) {
            $query->where('is_global', true)
                ->orWhere('branch_id', $branchId);
        })
            ->where('status', 'finished')
            ->with(['sundayServiceAttendances.member'])
            ->orderBy('event_date', 'desc')
            ->orderBy('start_time', 'desc')
            ->paginate(5);

        // Determine if any event is global
        $hasGlobalEvent = $events->contains(fn($event) => $event->is_global);

        // Fetch members accordingly
        if ($hasGlobalEvent) {
            // Fetch all members for global events
            $members = User::where('role', 'member')->get();
        } else {
            // Fetch members only from the branch
            $members = User::where('role', 'member')
                ->where('branch_id', $branchId)
                ->get();
        }

        $attendanceData = [];

        foreach ($events as $event) {
            $eventMembers = $event->is_global
                ? $members // all members
                : $members->where('branch_id', $event->branch_id);

            // Build attendance data for all event members
            foreach ($eventMembers as $member) {
                $attendanceRecord = $event->sundayServiceAttendances->firstWhere('member_id', $member->id);
                $attendanceData[$event->id][] = [
                    'id' => $member->id,
                    'name' => $member->first_name . ' ' . $member->last_name ?? 'N/A',
                    'status' => $attendanceRecord ? ucfirst(trim($attendanceRecord->status)) : 'Not Recorded',
                ];
            }
        }


        $membersToNotify = [];

        foreach ($members as $member) {
            $recent = SundayServiceAttendance::join('events', 'sunday_service_attendance.event_id', '=', 'events.id')
                ->where('sunday_service_attendance.member_id', $member->id)
                ->orderByDesc(DB::raw("CONCAT(events.event_date, ' ', events.start_time)"))
                ->select('sunday_service_attendance.status')
                ->take(10)
                ->get();

            $consecutiveMissed = 0;
            foreach ($recent as $attendance) {
                if ($attendance->status === 'Missed') {
                    $consecutiveMissed++;
                } else {
                    break;
                }
            }

            if ($consecutiveMissed >= 3) {
                $membersToNotify[] = $member->id;
            }
        }

        $inactiveMembers = [];

     foreach ($members as $member) {
    // Skip if flagged
    if ($member->skip_attendance_check) {
        continue;
    }

    $recentAttendances = SundayServiceAttendance::join('events', 'sunday_service_attendance.event_id', '=', 'events.id')
        ->where('sunday_service_attendance.member_id', $member->id)
        ->orderByDesc(DB::raw("CONCAT(events.event_date, ' ', events.start_time)"))
        ->select('sunday_service_attendance.status')
        ->take(10)
        ->get();

    $consecutiveMissed = 0;
    foreach ($recentAttendances as $attendance) {
        if ($attendance->status === 'Missed') {
            $consecutiveMissed++;
        } else {
            break;
        }
    }

    if ($consecutiveMissed >= 3 && strtolower($member->status) !== 'inactive') {
        $this->setInactive($member);
        $inactiveMembers[] = $member;
    } elseif ($consecutiveMissed < 3 && strtolower($member->status) === 'inactive') {
        $member->status = 'Active';
        $member->save();
    }
}



        $membersToNotifyNames = User::whereIn('id', $membersToNotify)
            ->where('branch_id', $branchId)
            ->select('id', DB::raw("CONCAT(first_name, ' ', last_name) AS full_name"))
            ->pluck('full_name', 'id');

        
// Get all inactive members
$branchId = Auth::user()->branch_id; // or get from request/session

$inactiveMembers = User::where('status', 'Inactive')
    ->where('branch_id', $branchId)
    ->get();

$inactiveDetails = [];

foreach ($inactiveMembers as $member) {
    // Only get attendance records after the member joined
    $attendances = SundayServiceAttendance::where('member_id', $member->id)
        ->where('branch_id', $branchId)
        ->where('service_date', '>=', $member->created_at->toDateString())
        ->orderBy('service_date', 'desc')
        ->get();

    $consecutiveMissed = [];
    foreach ($attendances as $attendance) {
        if ($attendance->status === 'Missed') {
            $consecutiveMissed[] = $attendance->id;
        } else {
            break;
        }
    }

    if (count($consecutiveMissed) >= 3) {
        $inactiveDetails[] = [
            'name' => $member->first_name . ' ' . $member->last_name,
            'missed_count' => count($consecutiveMissed),
        ];
    }
}



       
return view('admin.sundayservice', [
    'events' => $events,
    'attendanceData' => $attendanceData,
    'membersToNotifyNames' => $membersToNotifyNames,
    'inactiveMembers' => $inactiveMembers,
    'inactiveDetails' => $inactiveDetails,
]);
    }

    public function getFinishedEvents()
    {
        $user = Auth::user();
        $branchId = $user->branch_id;

        $now = Carbon::now('Asia/Manila');
        $nowString = $now->format('Y-m-d H:i:s');

        $finishedEvents = Event::where(function ($query) use ($branchId) {
            $query->where('is_global', true)
                ->orWhere('branch_id', $branchId);
        })
            ->where('status', 'finished')
            ->whereRaw("STR_TO_DATE(CONCAT(event_date, ' ', end_time), '%Y-%m-%d %H:%i:%s') < ?", [$nowString])
            ->orderByDesc('event_date')
            ->orderByDesc('start_time')
            ->get();

        $attendances = SundayServiceAttendance::where('member_id', $user->id)
            ->whereIn('event_id', $finishedEvents->pluck('id'))
            ->pluck('status', 'event_id');

        $finishedEvents = $finishedEvents->map(function ($event) use ($attendances) {
            $event->attendance_status = $attendances[$event->id] ?? 'Not Recorded';
            $event->event_time = $event->start_time . ' - ' . $event->end_time;
            return $event;
        });

        return response()->json([
            'events' => $finishedEvents,
        ]);
    }

    public function bulkUpdate(Request $request)
    {
        $request->validate([
            'updates' => 'required|array',
            'updates.*.member_id' => 'required|exists:users,id',
            'updates.*.event_id' => 'required|exists:events,id',
            'updates.*.status' => 'required|in:Attended,Missed,Not Recorded',
        ]);

      foreach ($request->updates as $update) {
    $member = \App\Models\User::find($update['member_id']);
    $event = \App\Models\Event::find($update['event_id']);

    $statusToSave = $update['status'] === 'Not Recorded' ? 'Missed' : $update['status'];

    SundayServiceAttendance::updateOrCreate(
        [
            'event_id' => $update['event_id'],
            'member_id' => $update['member_id'],
        ],
        [
            'status' => $statusToSave,
            'branch_id' => $member ? $member->branch_id : ($event ? $event->branch_id : null),
            'service_date' => $event ? $event->event_date : null,
        ]
    );

    // Refresh member status after attendance update
    if ($member) {
        $member->refreshStatus(); // Make sure this method exists in your User model
    }
}

        return response()->json(['success' => true]);
    }


    
}