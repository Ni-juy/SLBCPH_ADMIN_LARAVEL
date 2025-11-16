<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ChurchService;
use App\Models\Event;
use Carbon\Carbon;

class ChurchServiceController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $branchId = $user->branch_id;

        $service = ChurchService::where('branch_id', $branchId)->first();

        // Permanent QR code URL for Sunday service attendance
        $qrUrl = url("/attendance/checkin?branch={$branchId}");

        return view('admin.churchservice', compact('service', 'qrUrl'));
    }

    public function storeOrUpdate(Request $request)
    {
        $user = auth()->user();

        if ($user->role !== 'Admin') {
            abort(403, 'Only admins can update the church service schedule.');
        }

        $request->validate([
            'title' => 'nullable|string|max:255',
            'day_of_week' => 'required|string',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
        ]);

        // Update or create the church service schedule
        ChurchService::updateOrCreate(
            ['branch_id' => $user->branch_id],
            [
                'title' => $request->title,
                'day_of_week' => $request->day_of_week,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
            ]
        );

        // Optional: Immediately generate the next week's event
        $this->createUpcomingServiceEvent($user->branch_id);

        return redirect()->route('churchservice.index')
            ->with('success', 'Church service schedule updated!');
    }

    /**
     * Generate next occurrence of service as event if it doesn’t exist yet.
     */
    protected function createUpcomingServiceEvent($branchId)
    {
        $service = ChurchService::where('branch_id', $branchId)->first();
        if (!$service)
            return;

        $title = $service->title ?? 'Sunday Service';

        // Get next date matching the configured day_of_week
        // Fix: Use startOfWeek(Carbon::SUNDAY) to ensure week starts on Sunday
        $nextDate = Carbon::now()->startOfWeek(Carbon::SUNDAY)->addDays($this->getDayIndex($service->day_of_week));
        if ($nextDate->isToday() || $nextDate->isPast()) {
            $nextDate->addWeek();
        }

        // Avoid duplicates
        $alreadyExists = Event::where('branch_id', $branchId)
            ->whereDate('event_date', $nextDate->toDateString())
            ->where('title', $title)
            ->exists();

        if ($alreadyExists)
            return;

        // Create the event
        Event::create([
            'branch_id' => $branchId,
            'title' => $title,
            'description' => 'Auto-generated church service',
            'location' => 'Main Sanctuary',
            'event_date' => $nextDate->toDateString(),
            'start_time' => $service->start_time,
            'end_time' => $service->end_time,
            'created_by' => auth()->id(),
            'status' => 'upcoming',
        ]);
    }

    /**
     * Convert day name to index for Carbon (Sunday = 0, Saturday = 6).
     */
    protected function getDayIndex($day)
    {
        $days = [
            'Sunday' => 0,
            'Monday' => 1,
            'Tuesday' => 2,
            'Wednesday' => 3,
            'Thursday' => 4,
            'Friday' => 5,
            'Saturday' => 6,
        ];

        return $days[$day] ?? 0;
    }
}
