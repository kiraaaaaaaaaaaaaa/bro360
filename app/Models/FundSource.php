<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;


class FundSource extends Model
{
    protected $table = 'fund_sources';

    protected $fillable = [
        'code',
        'name',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function paps(): HasMany
    {
        return $this->hasMany(Pap::class, 'fund_source_id');
    }

    public function budgetAllocations(): HasMany
    {
        return $this->hasMany(BudgetAllocation::class, 'fund_source_id');
    }
}