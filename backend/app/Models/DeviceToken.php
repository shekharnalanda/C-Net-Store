<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeviceToken extends Model
{
    protected $fillable = ['user_id', 'app_type', 'platform', 'token', 'device_name', 'last_used_at', 'is_active'];
    protected $hidden = ['token'];
    protected function casts(): array { return ['last_used_at' => 'datetime', 'is_active' => 'boolean']; }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}

