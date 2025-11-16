<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Donation extends Model
{
    use HasFactory;

    // Define the fillable attributes (for mass assignment)
    protected $fillable = [
        'user_id',
        'offering_id',
        'amount',
        'date',
        'branch_id',
        'parent_donation_id', // <== This must be included!
    ];


    // Define the relationship with the User model (donation made by a member)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Define the relationship with the Offering model (the offering category/type)
    public function offering()
    {
        return $this->belongsTo(Offering::class);
    }

    // Define the relationship with the Branch model (the branch where the donation was made)
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    // Define the relationship with the DonationAllocation model (allocation of funds for the donation)
    public function allocations()
    {
        return $this->hasMany(DonationAllocation::class);
    }

    public function children()
    {
        return $this->hasMany(Donation::class, 'parent_donation_id');
    }

    public function parent()
    {
        return $this->belongsTo(Donation::class, 'parent_donation_id');
    }

}
