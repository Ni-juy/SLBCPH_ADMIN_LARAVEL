<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'title',
        'description',
        'event_date',
        'start_time',
        'end_time',
        'location',
        'status',
        'created_by',
        'is_global',
    ];

    // Auto-update event status upon retrieval
    protected static function boot()
    {
        parent::boot();

        static::retrieved(function ($event) {
            $now = Carbon::now('Asia/Manila');
            $eventEndDateTime = Carbon::parse($event->event_date . ' ' . $event->end_time, 'Asia/Manila');
            if (($eventEndDateTime->isPast()) && $event->status === 'upcoming') {
                $event->update(['status' => 'finished']);
            }
        });
    }

    public function sundayServiceAttendances()
    {
        return $this->hasMany(SundayServiceAttendance::class, 'event_id');
    }

}
