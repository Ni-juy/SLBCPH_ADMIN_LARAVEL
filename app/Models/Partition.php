<?php

// app/Models/Partition.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Partition extends Model
{
    use HasFactory;

    protected $fillable = [
        'category',
        'partition',
        'description',
        'branch_id',
        'amount',
    ];

    public function offerings(): BelongsToMany
    {
        return $this->belongsToMany(Offering::class, 'partition_offering');
    }
}
