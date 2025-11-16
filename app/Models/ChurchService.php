<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChurchService extends Model
{
    // Allow mass-assignment for these fields
    protected $fillable = [
        'branch_id',
        'title',
        'day_of_week',
        'start_time',
        'end_time',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}


