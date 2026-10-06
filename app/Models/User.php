<?php

namespace App\Models;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = ['name', 'email', 'phone', 'password', 'role', 'status'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'role' => UserRole::class,
            'status' => UserStatus::class,
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
        ];
    }

    public function hasRole(UserRole ...$roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    public function facultyProfile(): HasOne
    {
        return $this->hasOne(FacultyProfile::class);
    }

    public function company(): HasOne
    {
        return $this->hasOne(Company::class, 'owner_user_id');
    }

    public function branch(): HasOne
    {
        return $this->hasOne(CompanyBranch::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    public function files(): HasMany
    {
        return $this->hasMany(File::class);
    }

    public function appNotifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }
}
