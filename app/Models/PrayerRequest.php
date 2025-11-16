<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrayerRequest extends Model
{
    use HasFactory;

    protected $table = 'prayer_requests'; // Existing table name
    protected $fillable = ['member_id', 'branch_id', 'type', 'request', 'status'];

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
}
