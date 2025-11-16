<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BranchTransferRequest extends Model
{
    protected $fillable = [
        'user_id',
        'current_branch_id',
        'requested_branch_id',
        'reason',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function currentBranch()
    {
        return $this->belongsTo(Branch::class, 'current_branch_id');
    }

    public function requestedBranch()
    {
        return $this->belongsTo(Branch::class, 'requested_branch_id');
    }

}
