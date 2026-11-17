<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'role_id',
        'name',
        'email',
        'password',
        'phone',
        'role',
        'avatar',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function roleRelation()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function hasRole(string $roleName): bool
    {
        return $this->role?->name === $roleName || $this->role === $roleName;
    }

    public function hasPermission(string $code): bool
    {
        if (!$this->role_id) {
            return false;
        }

        return $this->roleRelation?->permissions()->where('code', $code)->exists() ?? false;
    }

    public function linkedSocialAccounts()
    {
        return $this->hasMany(LinkedGoogleAccount::class);
    }

    public function linkedGoogleAccounts()
    {
        return $this->hasMany(LinkedGoogleAccount::class);
    }

    public function luckyWheelSpins()
    {
        return $this->hasMany(LuckyWheelSpin::class);
    }
}

