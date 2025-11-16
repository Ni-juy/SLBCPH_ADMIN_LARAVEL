<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DonationConfirmation extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'reference_number',
        'amount',
        'message',
        'image_path',
        'is_verified',
        'branch_id',
    ];

    // Define relationship to Branch
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
