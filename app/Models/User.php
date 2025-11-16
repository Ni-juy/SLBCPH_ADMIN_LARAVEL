<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'branch_id',
        'role',
        'username',
        'email',
        'password',
        'first_name',
        'middle_name',
        'last_name',
        'contact_number',
        'address',
        'birthdate',
        'baptism_date',
        'salvation_date',
        'gender',
        'status',
        'profile_image',
        'terms_accepted_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'password' => 'hashed',
        'birthdate' => 'date',
        'baptism_date' => 'date',
        'salvation_date' => 'date',
        'terms_accepted_at' => 'datetime',
    ];


    public function branch()
    {
        return $this->belongsTo(\App\Models\Branch::class, 'branch_id');
    }

    // In User model

public function refreshStatus()
{
    // Do not change status if member is archived
    if (strtolower($this->status) === 'archived') {
        return;
    }

    $branchId = $this->branch_id;
    $attendances = \App\Models\SundayServiceAttendance::where('member_id', $this->id)
        ->where('branch_id', $branchId)
        ->where('service_date', '>=', $this->created_at->toDateString())
        ->orderBy('service_date', 'desc')
        ->get();

    $consecutiveMissed = 0;
    foreach ($attendances as $attendance) {
        if ($attendance->status === 'Missed') {
            $consecutiveMissed++;
        } else {
            break;
        }
    }

    $newStatus = $consecutiveMissed >= 3 ? 'Inactive' : 'Active';
    if ($this->status !== $newStatus) {
        $this->status = $newStatus;
        $this->save();
    }
}

    public function hasAcceptedTerms()
    {
        return !is_null($this->terms_accepted_at);
    }


}
