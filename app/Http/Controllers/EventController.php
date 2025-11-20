<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Models\SundayServiceAttendance;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class EventController extends Controller
{

    public function stream()
    {
        $branchId = Auth::user()->branch_id;

        return response()->stream(function () use ($branchId) {
            while (true) {
                $now = Carbon::now('Asia/Manila');

                $finishedEvents = Event::where(function ($query) use ($now) {
                    $query->where('event_date', '<', $now->toDateString())
                        ->orWhere(function ($q) use ($now) {
                            $q->where('event_date', $now->toDateString())
                                ->whereRaw("STR_TO_DATE(end_time, '%H:%i:%s') < ?", [$now->format('H:i:s')]);
                        });
                });
                $finishedEvents->update(['status' => 'finished']);

                $finishedEvents->get()->each(function ($event) {
                    $branchMembers = User::where('branch_id', $event->branch_id)
                        ->where('role', 'member')
                        ->get();

                    foreach ($branchMembers as $member) {
                        $alreadyMarked = SundayServiceAttendance::where('event_id', $event->id)
                            ->where('member_id', $member->id)
                            ->exists();

                        if (!$alreadyMarked) {
                            SundayServiceAttendance::create([
                                'event_id' => $event->id,
                                'member_id' => $member->id,
                                'branch_id' => $event->branch_id,
                                'service_date' => $event->event_date,
                                'status' => 'missed',
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        }
                    }
                });

                $ongoingEvents = Event::where('event_date', $now->toDateString())
                    ->whereRaw("STR_TO_DATE(start_time, '%H:%i:%s') <= ?", [$now->format('H:i:s')])
                    ->whereRaw("STR_TO_DATE(end_time, '%H:%i:%s') >= ?", [$now->format('H:i:s')]);

                $ongoingEvents->update(['status' => 'ongoing']);

                $upcomingEvents = Event::where(function ($query) use ($now) {
                    $query->where('event_date', '>', $now->toDateString())
                        ->orWhere(function ($q) use ($now) {
                            $q->where('event_date', $now->toDateString())
                                ->whereRaw("STR_TO_DATE(start_time, '%H:%i:%s') > ?", [$now->format('H:i:s')]);
                        });
                });

                $upcomingEvents->update(['status' => 'upcoming']);

                $events = Event::where(function ($query) use ($branchId) {
                    $query->where('is_global', true)
                        ->orWhere('branch_id', $branchId);
                })
                ->orderBy('event_date', 'desc')
                ->get();

                echo "data: " . json_encode([
                    'events' => $events,
                    'timestamp' => $now->format('Y-m-d H:i:s')
                ]) . "\n\n";

                ob_flush();
                flush();
                sleep(30);
            }


        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
        ]);
    }

    public function index()
    {
        $branchId = Auth::user()->branch_id;
        $now = Carbon::now('Asia/Manila'); // Use the correct timezone

        \Log::info('Current Time: ' . $now);

        // Mark finished events
        $finishedEvents = Event::where(function ($query) use ($now) {
            $query->where('event_date', '<', $now->toDateString())
                ->orWhere(function ($q) use ($now) {
                    $q->where('event_date', $now->toDateString())
                        ->whereRaw("STR_TO_DATE(end_time, '%H:%i:%s') < ?", [$now->format('H:i:s')]);
                });
        });

        // Log finished events before updating
        foreach ($finishedEvents->get() as $event) {
            \Log::info('Marking Event ID ' . $event->id . ' as finished. Event Date: ' . $event->event_date . ', End Time: ' . $event->end_time);
        }

        $finishedCount = $finishedEvents->update(['status' => 'finished']);
        \Log::info('Number of Finished Events Updated: ' . $finishedCount);

        $finishedEvents->get()->each(function ($event) {
            // 🔧 Filter to only 'member' role users
            $branchMembers = User::where('branch_id', $event->branch_id)
                ->where('role', 'member')
                ->get();

            foreach ($branchMembers as $member) {
                $alreadyMarked = SundayServiceAttendance::where('event_id', $event->id)
                    ->where('member_id', $member->id)
                    ->exists();

                if (!$alreadyMarked) {
                    SundayServiceAttendance::create([
                        'event_id' => $event->id,
                        'member_id' => $member->id,
                        'branch_id' => $event->branch_id,
                        'service_date' => $event->event_date,
                        'status' => 'missed',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        });


        // Mark ongoing events
        $ongoingEvents = Event::where('event_date', $now->toDateString())
            ->whereRaw("STR_TO_DATE(start_time, '%H:%i:%s') <= ?", [$now->format('H:i:s')])
            ->whereRaw("STR_TO_DATE(end_time, '%H:%i:%s') >= ?", [$now->format('H:i:s')]);

        // Log ongoing events before updating
        foreach ($ongoingEvents->get() as $event) {
            \Log::info('Marking Event ID ' . $event->id . ' as ongoing. Event Date: ' . $event->event_date . ', Start Time: ' . $event->start_time . ', End Time: ' . $event->end_time);
        }

        $ongoingCount = $ongoingEvents->update(['status' => 'ongoing']);
        \Log::info('Number of Ongoing Events Updated: ' . $ongoingCount);

        // Mark upcoming events
        // Mark upcoming events
        $upcomingEvents = Event::where(function ($query) use ($now) {
            $query->where('event_date', '>', $now->toDateString())
                ->orWhere(function ($q) use ($now) {
                    $q->where('event_date', $now->toDateString())
                        ->whereRaw("STR_TO_DATE(start_time, '%H:%i:%s') > ?", [$now->format('H:i:s')]);
                });
        });

        // Log upcoming events before updating
        foreach ($upcomingEvents->get() as $event) {
            \Log::info('Marking Event ID ' . $event->id . ' as upcoming. Event Date: ' . $event->event_date . ', Start Time: ' . $event->start_time);
        }

        $upcomingCount = $upcomingEvents->update(['status' => 'upcoming']);
        \Log::info('Number of Upcoming Events Updated: ' . $upcomingCount);

        // Log current statuses of events before updating
        $upcomingEventsToLog = Event::where(function ($query) use ($now) {
            $query->where('event_date', '>', $now->toDateString())
                ->orWhere(function ($q) use ($now) {
                    $q->where('event_date', $now->toDateString())
                        ->whereRaw("STR_TO_DATE(start_time, '%H:%i:%s') > ?", [$now->format('H:i:s')]);
                });
        })->get();

        foreach ($upcomingEventsToLog as $event) {
            \Log::info('Current Status of Event ID ' . $event->id . ': ' . $event->status);
        }



        // Get all events for calendar including global events
        $allEvents = Event::where(function ($query) use ($branchId) {
            $query->where('is_global', true)
                ->orWhere('branch_id', $branchId);
        })
            ->orderBy('created_at', 'desc')
            ->get();

        // Paginate for the table including global events
        $events = Event::where(function ($query) use ($branchId) {
            $query->where('is_global', true)
                ->orWhere('branch_id', $branchId);
        })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Log the total number of events processed
        \Log::info('Total Events Processed: ' . $events->count());

        return view('admin.manageevent', compact('events', 'allEvents'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'event_date' => 'required|date',
            'start_time' => 'required',

            'end_time' => 'required',
            'location' => 'required|string|max:255',
            'is_global' => 'nullable|boolean',
        ]);

        // Check for time conflict
        $conflictQuery = Event::where('event_date', $request->event_date)
            ->where(function ($query) use ($request) {
                $query->where(function ($q) use ($request) {
                    $q->where('start_time', '<', $request->end_time)
                        ->where('end_time', '>', $request->start_time);
                });
            });

        // If event is global, check conflicts only for global events
        if ($request->is_global) {
            $conflictQuery->where('is_global', true);
        } else {
            // For branch-specific events, check conflicts only within the user's branch and non-global events
            $conflictQuery->where('branch_id', Auth::user()->branch_id)
                ->where(function ($q) {
                    $q->where('is_global', false)->orWhereNull('is_global');
                });
        }

        $conflict = $conflictQuery->exists();

        if ($conflict) {
            return back()->withErrors(['start_time' => 'The selected time slot is already taken.'])->withInput();
        }

        // Determine the status based on date and time
        $now = Carbon::now('Asia/Manila'); // Use the correct timezone
        $eventStartTime = Carbon::parse($request->event_date . ' ' . $request->start_time, 'Asia/Manila');
        $eventEndTime = Carbon::parse($request->event_date . ' ' . $request->end_time, 'Asia/Manila');

        if ($eventEndTime->isPast()) {
            $status = 'finished';
        } elseif ($eventStartTime->isFuture()) {
            $status = 'upcoming';
        } else {
            $status = 'ongoing';
        }

        Event::create([
            'branch_id' => $request->is_global ? null : Auth::user()->branch_id,
            'title' => $request->title,
            'description' => $request->description,
            'event_date' => $request->event_date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'location' => $request->location,
            'status' => $status,
            'is_global' => $request->is_global ?? false,
            'created_by' => Auth::user()->id,
        ]);

        // Log the action
        $user = Auth::user();
        $userName = $user ? ($user->name ?? trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''))) : 'Guest';
        $userRole = $user->role ?? 'Guest';

        file_put_contents(
            storage_path('logs/system.log'),
            '[' . now() . '] User: ' . $userName .
            ' | Role: ' . $userRole .
            ' | Action: Add Event | Details: Added event "' . $request->title . '" on ' . $request->event_date . PHP_EOL,
            FILE_APPEND
        );

        return redirect()->route('events.index')->with('event_created', 'Event added successfully!');
    }

    public function edit(Event $event)
    {
        return response()->json($event);
    }

    public function update(Request $request, Event $event)
    {
        $request->validate([
            'title' => 'required',
            'event_date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'required',
            'location' => 'required',
            'description' => 'nullable',
        ]);

        // Determine the status based on date and time
        $now = Carbon::now('Asia/Manila'); // Use the correct timezone
        $eventStartTime = Carbon::parse($request->event_date . ' ' . $request->start_time, 'Asia/Manila');
        $eventEndTime = Carbon::parse($request->event_date . ' ' . $request->end_time, 'Asia/Manila');

        if ($eventEndTime->isPast()) {
            $status = 'finished';
        } elseif ($eventStartTime->isFuture()) {
            $status = 'upcoming';
        } else {
            $status = 'ongoing';
        }

        $event->update([
            'title' => $request->title,
            'event_date' => $request->event_date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'location' => $request->location,
            'description' => $request->description,
            'status' => $status,
        ]);

        // Log the action
        $user = Auth::user();
        $userName = $user ? ($user->name ?? trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''))) : 'Guest';
        $userRole = $user->role ?? 'Guest';

        file_put_contents(
            storage_path('logs/system.log'),
            '[' . now() . '] User: ' . $userName .
            ' | Role: ' . $userRole .
            ' | Action: Update Event | Details: Updated event "' . $request->title . '" (ID: ' . $event->id . ')' . PHP_EOL,
            FILE_APPEND
        );

        return redirect()->route('events.index')->with('success', 'Event updated successfully!');
    }

    public function destroy(Event $event)
    {
        $event->delete();
        return redirect()->route('events.index')->with('success', 'Event deleted successfully.');
    }

    public function getUpcomingEvents()
    {
        $branchId = Auth::user()->branch_id;
        // Fetch upcoming events for the user's branch or global events
        $events = Event::where(function ($query) use ($branchId) {
            $query->where('is_global', true)
                ->orWhere('branch_id', $branchId);
        })
            ->where('status', '!=', 'ongoing')
            ->where('event_date', '>=', Carbon::today())
            ->orderBy('created_at', 'asc')
            ->paginate(10); // 5 per page
        // Return the paginated events as a JSON response
        return response()->json($events);
    }

    public function getTakenTimes(Request $request)
    {
        $branchId = Auth::user()->branch_id;
        $events = Event::where('event_date', $request->date)
            ->where(function ($query) use ($branchId) {
                $query->where('is_global', true)
                      ->orWhere('branch_id', $branchId);
            })->where('status', 'upcoming')
            ->get(['start_time', 'end_time']);
        return response()->json($events);
    }

    public function delete(Event $event)
    {
        $event->delete();
        return response()->json(['success' => true]);
    }

    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids', []);
        $user = Auth::user();
        $userName = $user ? ($user->name ?? trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''))) : 'Guest';
        $userRole = $user->role ?? 'Guest';

        if (!empty($ids)) {
            // Fetch events before deleting
            $events = Event::whereIn('id', $ids)->get(['title', 'event_date']);
            $eventDetails = $events->map(function ($event) {
                return '"' . $event->title . '" on ' . $event->event_date;
            })->implode(', ');

            Event::whereIn('id', $ids)->delete();

            // Log the deletion with title and date
            file_put_contents(
                storage_path('logs/system.log'),
                '[' . now() . '] User: ' . $userName .
                ' | Role: ' . $userRole .
                ' | Action: Delete Event(s) | Details: Deleted event(s): ' . $eventDetails . PHP_EOL,
                FILE_APPEND
            );

            return response()->json(['success' => true]);
        }

        // Optionally log failed attempt
        file_put_contents(
            storage_path('logs/system.log'),
            '[' . now() . '] User: ' . $userName .
            ' | Role: ' . $userRole .
            ' | Action: Delete Event(s) | Details: Attempted to delete events but no IDs provided.' . PHP_EOL,
            FILE_APPEND
        );

        return response()->json(['success' => false]);
    }


    public function getOngoingEvents()
    {
        $user = Auth::user();
        $branchId = $user->branch_id;

        $events = Event::where('branch_id', $branchId)
            ->where('status', 'ongoing')
            ->where('event_date', Carbon::today('Asia/Manila'))
            ->orderBy('event_date', 'asc')
            ->get();

        // Fetch user's attendance for the ongoing events
        $attendances = \App\Models\SundayServiceAttendance::where('member_id', $user->id)
            ->pluck('status', 'event_id');

        // Attach attendance status and event_time to each event
        $events = $events->map(function ($event) use ($attendances) {
            $event->status = $attendances[$event->id] ?? 'Not Recorded';
            $event->event_time = $event->start_time . ' - ' . $event->end_time;
            return $event;
        });

        return response()->json([
            'events' => $events,
            'total_events' => $events->count()
        ]);
    }

    public function batchUpload(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls',
        ]);

        $file = $request->file('excel_file');
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        unset($rows[0]); // Skip header

        $successCount = 0;
        $skipped = [];

        foreach ($rows as $index => $row) {
            if (!$row[0])
                continue; // Skip empty rows

            try {
                $eventDate = Carbon::parse($row[1])->format('Y-m-d');
                $startTimeRaw = Carbon::parse($row[2]);
                $endTimeRaw = Carbon::parse($row[3]);
                $startTime = $startTimeRaw->format('H:i:s');
                $endTime = $endTimeRaw->format('H:i:s');

                $today = Carbon::today('Asia/Manila');
                if (Carbon::parse($eventDate)->lt($today)) {
                    $skipped[] = "Row " . ($index + 2) . " skipped: Event date $eventDate is in the past.";
                    continue;
                }

                // Ensure start time is before end time
                if ($startTimeRaw->gte($endTimeRaw)) {
                    $skipped[] = "Row " . ($index + 2) . " skipped: Start time must be before end time.";
                    continue;
                }

                // Check for conflicting event on same date & branch
                $conflict = Event::where('event_date', $eventDate)
                    ->where('branch_id', Auth::user()->branch_id)
                    ->where(function ($query) use ($startTime, $endTime) {
                        $query->where(function ($q) use ($startTime, $endTime) {
                            $q->where('start_time', '<', $endTime)
                                ->where('end_time', '>', $startTime);
                        });
                    })
                    ->exists();

                if ($conflict) {
                    $skipped[] = "Row " . ($index + 2) . " skipped: Conflict detected on $eventDate from {$row[2]} to {$row[3]}.";
                    continue;
                }

                Event::create([
                    'title' => $row[0],
                    'event_date' => $eventDate,
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'location' => $row[4],
                    'description' => $row[5] ?? '',
                    'status' => 'upcoming',
                    'branch_id' => Auth::user()->branch_id,
                    'created_by' => Auth::id(),
                ]);

                $successCount++;
            } catch (\Exception $e) {
                $skipped[] = "Row " . ($index + 2) . " error: " . $e->getMessage();
            }
        }

        return redirect()->back()->with([
            'success' => "$successCount event(s) uploaded successfully.",
            'errors' => $skipped
        ]);
    }



    public function downloadTemplate()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Event Template');

        // Set column headers
        $headers = [
            'Title',
            'Date (YYYY-MM-DD)',
            'Start Time (HH:MM AM/PM)',
            'End Time (HH:MM AM/PM)',
            'Location',
            'Description (optional)'
        ];
        $sheet->fromArray($headers, null, 'A1');

        // Sample data row
        $sample = [
            'Sunday Worship',
            '2025-08-04',
            '08:00 AM',
            '10:00 AM',
            'Main Hall',
            'Weekly worship service'
        ];
        $sheet->fromArray($sample, null, 'A2');

        // Freeze the header row
        $sheet->freezePane('A2');

        // Auto-size all columns
        foreach (range('A', 'F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Apply styles to header
        $headerStyle = [
            'font' => ['bold' => true, 'size' => 12],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FFCCE5FF'], // Light blue
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ];
        $sheet->getStyle('A1:F1')->applyFromArray($headerStyle);

        // Optional: add border and wrap text to all cells
        $sheet->getStyle('A1:F2')->getAlignment()->setWrapText(true);
        $sheet->getStyle('A1:F2')->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        // Set default font for entire sheet
        $spreadsheet->getDefaultStyle()->getFont()->setName('Calibri')->setSize(11);

        // Export the file
        $writer = new Xlsx($spreadsheet);
        $fileName = 'event_upload_template.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }


}
