<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Offering extends Model
{
    use HasFactory;

    protected $fillable = [
        'category',
        'branch_id',
        'parent_id',
        'user_id',
        'created_at',
        'updated_at'
    ];

    /**
     * The partitions that belong to the offering.
     */
    public function partitions(): BelongsToMany
    {
        return $this->belongsToMany(Partition::class, 'partition_offering');
    }

    /**
     * Get the subcategories (children) of this offering.
     */
    public function children(): HasMany
    {
        return $this->hasMany(Offering::class, 'parent_id');
    }

    /**
     * Get the parent offering of this subcategory.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Offering::class, 'parent_id');
    }

    public $timestamps = false;
}
