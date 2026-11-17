<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;



#[Fillable(['name', 'email', 'password', 'employee_no', 'office_id', 'division_id', 'organizational_unit_id', 'position_id'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    public function office()
{
    return $this->belongsTo(Office::class);
}

public function division()
{
    return $this->belongsTo(Division::class);
}

public function organizationalUnit()
{
    return $this->belongsTo(
        OrganizationalUnit::class,
        'organizational_unit_id'
    );
}

public function position()
{
    return $this->belongsTo(Position::class);
}

public function roles()
{
    return $this->belongsToMany(Role::class, 'role_user');
}
public function hasPermissionTo(string $permission): bool
{
    return $this->roles()
        ->whereHas('permissions', function ($query) use ($permission) {
            $query->where('permissions.name', $permission);
        })
        ->exists();
}

public function googleDriveConnection()
{
    return $this->hasOne(
        \App\Models\GoogleDriveConnection::class,
        'user_id'
    );
}


}
