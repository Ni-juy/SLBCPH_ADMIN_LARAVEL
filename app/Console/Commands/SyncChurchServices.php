<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schedule;
use App\Models\ChurchService;
use App\Models\Event;
use Carbon\Carbon;

class SyncChurchServices extends Command
{
    protected $signature = 'churchservices:sync';
    protected $description = 'Sync church services into the event table and update statuses';

    public function handle()
    {
        $today = Carbon::today();
        $now = Carbon::now();

        $services = ChurchService::all();

        foreach ($services as $service) {
            $dayIndex = $this->getDayIndex($service->day_of_week);
            $nextDate = Carbon::now()->startOfWeek()->addDays($dayIndex);
            if ($nextDate->lt($today)) {
                $nextDate->addWeek();
            }

            $exists = Event::where('branch_id', $service->branch_id)
                ->whereDate('event_date', $nextDate->toDateString())
                ->where('title', $service->title ?? 'Sunday Service')
                ->exists();

            if (!$exists) {
                Event::create([
                    'branch_id'   => $service->branch_id,
                    'title'       => $service->title ?? 'Sunday Service',
                    'description' => 'Auto-generated Sunday Service',
                    'location'    => 'Main Sanctuary',
                    'event_date'  => $nextDate->toDateString(),
                    'start_time'  => $service->start_time,
                    'end_time'    => $service->end_time,
                    'created_by'  => 1,
                    'status'      => 'upcoming',
                ]);
            }
        }

        $events = Event::whereDate('event_date', $today)->get();

        foreach ($events as $event) {
            $start = Carbon::parse($event->start_time);
            $end = Carbon::parse($event->end_time);

            if ($now->lt($start)) {
                $event->status = 'upcoming';
            } elseif ($now->between($start, $end)) {
                $event->status = 'ongoing';
            } else {
                $event->status = 'finished';
            }

            $event->save();
        }

        $this->info('Church services synced and event statuses updated.');
    }

    public function schedule(Schedule $schedule)
    {
        $schedule->command(static::class)->everyMinute(); // Change to dailyAt('00:01') if needed
    }

    protected function getDayIndex($day)
    {
        return [
            'Sunday'    => 0,
            'Monday'    => 1,
            'Tuesday'   => 2,
            'Wednesday' => 3,
            'Thursday'  => 4,
            'Friday'    => 5,
            'Saturday'  => 6,
        ][$day] ?? 0;
    }
}
