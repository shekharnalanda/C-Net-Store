<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = ['business_id', 'category_id', 'name', 'slug', 'sku', 'description', 'product_type', 'price', 'sale_price', 'tax_rate', 'stock_quantity', 'unit', 'preparation_minutes', 'is_vegetarian', 'is_active'];

    protected function casts(): array
    {
        return ['price' => 'decimal:2', 'sale_price' => 'decimal:2', 'tax_rate' => 'decimal:2', 'is_vegetarian' => 'boolean', 'is_active' => 'boolean'];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function inventory(): HasMany
    {
        return $this->hasMany(Inventory::class);
    }
}
