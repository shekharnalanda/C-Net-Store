<?php

namespace App\Models;

use App\Enums\ApprovalStatus;
use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'phone', 'password', 'role', 'status', 'preferred_language'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'status' => ApprovalStatus::class,
        ];
    }

    public function businesses(): HasMany
    {
        return $this->hasMany(Business::class, 'owner_id');
    }

    public function addresses(): HasMany { return $this->hasMany(Address::class); }
    public function orders(): HasMany { return $this->hasMany(Order::class); }
    public function deliveryPartner(): HasOne { return $this->hasOne(DeliveryPartner::class); }
}
