<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FaithTrack extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'name',
        'address',
        'contact_number',
        'date_shared',
        'tracks_given',
        'branch_id', // ✅ Add this
    ];

}
