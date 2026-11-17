<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Office extends Model
{
    protected $table = 'offices';

    protected $fillable = [
        'name',
        'code',
        'description',
        'is_active',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function divisions(): HasMany
    {
        return $this->hasMany(Division::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
