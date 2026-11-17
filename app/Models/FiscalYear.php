<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FiscalYear extends Model
{
    protected $table = 'fiscal_years';

    protected $fillable = [
        'year',
        'start_date',
        'google_drive_folder_id',
        'end_date',
        'status',
    ];

    protected $casts = [
        'year' => 'integer',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function paps(): HasMany
    {
        return $this->hasMany(Pap::class, 'fiscal_year_id');
    }
}