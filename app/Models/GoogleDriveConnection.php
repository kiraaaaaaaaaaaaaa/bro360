<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GoogleDriveConnection extends Model
{
    protected $table = 'google_drive_connections';

    protected $fillable = [
        'user_id',
        'google_user_id',
        'google_email',
        'access_token',
        'refresh_token',
        'expires_at',
        'scopes',
        'connected_at',
        'root_folder_id',
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}