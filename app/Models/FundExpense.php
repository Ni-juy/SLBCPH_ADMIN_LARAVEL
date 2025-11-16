<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FundExpense extends Model
{
    use HasFactory;

    protected $fillable = [
        'allocation_id',
        'description',
        'amount',
        'date',
        'image',
    ];

    public function allocation()
    {
        return $this->belongsTo(\App\Models\DonationAllocation::class, 'allocation_id');
    }
}
