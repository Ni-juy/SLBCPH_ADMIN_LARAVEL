<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    use HasFactory;

    protected $table = 'branches';

    protected $fillable = [
        'name',
        'address',
        'branch_type',
        'extension_of', // ✅ Make sure this is fillable
        'is_archived',
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'branch_id');
    }

    // ✅ Add this relationship
    public function parent_branch()
    {
        return $this->belongsTo(Branch::class, 'extension_of');
    }
}

