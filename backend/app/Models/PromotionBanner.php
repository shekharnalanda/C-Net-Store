<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromotionBanner extends Model
{
    protected $fillable = ['title', 'subtitle', 'image_path', 'target_type', 'target_value', 'audience', 'sort_order', 'starts_at', 'ends_at', 'is_active'];
    protected function casts(): array { return ['starts_at' => 'datetime', 'ends_at' => 'datetime', 'is_active' => 'boolean']; }
}

