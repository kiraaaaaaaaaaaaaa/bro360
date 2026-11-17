<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GoogleDriveSetting extends Model
{
    protected $table = 'google_drive_settings';

    protected $fillable = [
        'connected_by',
        'google_user_id',
        'google_email',
        'access_token',
        'refresh_token',
        'expires_at',
        'scopes',
        'root_folder_id',
        'connected_at',
    ];

    protected $hidden = [
        'access_token',
        'refresh_token',
    ];

    protected function casts(): array
    {
        return [
            'access_token' => 'encrypted',
            'refresh_token' => 'encrypted',
            'expires_at' => 'datetime',
            'connected_at' => 'datetime',
        ];
    }

    public function connectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'connected_by');
    }

    public static function current(): ?self
    {
        return static::query()->first();
    }
}
