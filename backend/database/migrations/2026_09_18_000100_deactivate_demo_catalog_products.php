<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $productIds = DB::table('products')
            ->where('sku', 'like', 'CNET-CATALOG-%')
            ->pluck('id');

        if ($productIds->isEmpty()) {
            return;
        }

        DB::transaction(function () use ($productIds): void {
            DB::table('products')
                ->whereIn('id', $productIds)
                ->update([
                    'price' => 0,
                    'sale_price' => null,
                    'stock_quantity' => 0,
                    'is_active' => false,
                    'updated_at' => now(),
                ]);

            DB::table('inventory')
                ->whereIn('product_id', $productIds)
                ->update([
                    'quantity' => 0,
                    'reserved_quantity' => 0,
                    'updated_at' => now(),
                ]);
        });
    }

    public function down(): void
    {
        // Deliberately irreversible: demo prices and stock must never be
        // restored to the public marketplace automatically.
    }
};
