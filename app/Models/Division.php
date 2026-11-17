<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Division extends Model
{
    protected $table = 'divisions';

    protected $fillable = [
        'office_id',
        'name',
        'code',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class);
    }

    public function organizationalUnits(): HasMany
    {
        return $this->hasMany(OrganizationalUnit::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}