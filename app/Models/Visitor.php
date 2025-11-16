<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Visitor extends Model
{
    protected $table = 'visitors';

    protected $fillable = [
        'first_name',
        'middle_name',
        'last_name',
        'visit_date',
        'address',
        'inviter',
        'branch_id',
    ];

    public $timestamps = true;
}
