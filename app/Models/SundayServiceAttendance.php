<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SundayServiceAttendance extends Model
{
    use HasFactory;

    protected $table = 'sunday_service_attendance';

    // Define the fillable fields
    protected $fillable = [
        'member_id',
        'branch_id',
        'event_id',
        'service_date',
        'status',
    ];

    // Relationship to User (Member)
    public function member()
    {
        return $this->belongsTo(User::class, 'member_id');
    }

    // Relationship to Branch
    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    // Relationship to Event
    public function event()
    {
        return $this->belongsTo(Event::class, 'event_id');
    }
}