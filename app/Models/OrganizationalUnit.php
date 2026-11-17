<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrganizationalUnit extends Model
{
    protected $table = 'organizational_units';

    protected $fillable = [
        'parent_id',
        'division_id',
        'name',
        'unit_type',
        'description',
        'is_active',
        'created_at',
        'updated_at',
    ];

     protected $casts = [
        'is_active' => 'boolean',
    ];

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(
            OrganizationalUnit::class,
            'parent_id'
        );
    }

    public function children(): HasMany
    {
        return $this->hasMany(
            OrganizationalUnit::class,
            'parent_id'
        );
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
