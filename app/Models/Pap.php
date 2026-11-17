<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pap extends Model
{
    use SoftDeletes;

    protected $table = 'paps';

    protected $fillable = [
        'fiscal_year_id',
        'division_id',
        'fund_source_id',
        'google_drive_folder_id',
        'program_id',
        'output_category_id',
        'focal_user_id',
        'pap_code',
        'name',
        'description',
        'status_id',
        'start_date',
        'end_date',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function fiscalYear()
    {
        return $this->belongsTo(FiscalYear::class);
    }

    public function division()
    {
        return $this->belongsTo(Division::class);
    }

    public function fundSource()
    {
        return $this->belongsTo(FundSource::class);
    }

    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function outputCategory()
    {
        return $this->belongsTo(OutputCategory::class);
    }

    public function focalUser()
    {
        return $this->belongsTo(User::class, 'focal_user_id');
    }

    public function status()
    {
        return $this->belongsTo(PapStatus::class, 'status_id');
    }

    public function assignments()
    {
        return $this->hasMany(PapAssignment::class);
    }

    public function activities()
    {
        return $this->hasMany(PapActivity::class);
    }

    public function activityTasks()
    {
        return $this->hasMany(PapActivityTask::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    
}
