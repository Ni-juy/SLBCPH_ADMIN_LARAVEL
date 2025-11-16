<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DonationAllocation extends Model
{
    use HasFactory;

    // Define the fillable attributes (for mass assignment)
    protected $fillable = [
        'donation_id',
        'partition_id',
        'allocated_amount',
        'allocation_date',
    ];

    // Define the relationship with the Donation model (allocation belongs to a donation)
    public function donation()
    {
        return $this->belongsTo(Donation::class);
    }

    // Define the relationship with the Partition model (allocation is based on partition/category)
    public function partition()
    {
        return $this->belongsTo(\App\Models\Partition::class, 'partition_id');
    }

    public function expenses()
{
    return $this->hasMany(FundExpense::class, 'allocation_id');
}

}
