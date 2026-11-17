<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Document extends Model
{
    use SoftDeletes;

    protected $table = 'documents';

    protected $fillable = [
        'category_id',
        'title',
        'description',
        'original_name',
        'file_path',
        'mime_type',
        'file_size',
        'uploaded_by',
        'visibility',
        'classification',
        'document_code',
        'document_number',
        'subject',
        'document_date',
        'date_received',
    ];

    protected function casts(): array
{
    return [
        'document_date' => 'date',
        'date_received' => 'date',
        'file_size' => 'integer',
    ];
}

    public function category(): BelongsTo
    {
        return $this->belongsTo(DocumentCategory::class, 'category_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(DocumentVersion::class, 'document_id')
            ->orderByDesc('version_no');
    }

    public function links(): HasMany
    {
        return $this->hasMany(DocumentLink::class, 'document_id');
    }

    public function accessLogs(): HasMany
    {
        return $this->hasMany(DocumentAccessLog::class, 'document_id');
    }
    public function scopeVisibleTo(Builder $query, User $user): Builder
{
    return $query->where(function ($query) use ($user) {

        // Accessible to all authenticated users.
        $query->where('visibility', 'System')

            // Accessible only to the uploader.
            ->orWhere(function ($query) use ($user) {
                $query->where('visibility', 'Private')
                    ->where('uploaded_by', $user->id);
            })

            // Accessible to users in the same division.
            ->orWhere(function ($query) use ($user) {
                if ($user->division_id) {
                    $query->where('visibility', 'Division')
                        ->where('uploaded_by', '!=', null)
                        ->whereHas('uploader', function ($uploader) use ($user) {
                            $uploader->where('division_id', $user->division_id);
                        });
                }
            })

            // Accessible to users in the same office.
            ->orWhere(function ($query) use ($user) {
                if ($user->office_id) {
                    $query->where('visibility', 'Office')
                        ->whereHas('uploader', function ($uploader) use ($user) {
                            $uploader->where('office_id', $user->office_id);
                        });
                }
            });

    });
}
}