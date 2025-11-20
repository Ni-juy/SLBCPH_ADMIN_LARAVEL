<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transparency extends Model
{
    protected $table = 'transparency';

    protected $fillable = [
        'id',
        'pdf_link',
        'branch_id'
    ];
    public $timestamps = false;
}
