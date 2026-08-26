<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('marketplace')->index();
            $table->string('image_path')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->string('sku')->nullable();
            $table->text('description')->nullable();
            $table->string('product_type')->index();
            $table->decimal('price', 12, 2);
            $table->decimal('sale_price', 12, 2)->nullable();
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->integer('stock_quantity')->default(0);
            $table->string('unit')->nullable();
            $table->unsignedSmallInteger('preparation_minutes')->nullable();
            $table->boolean('is_vegetarian')->nullable()->index();
            $table->boolean('is_active')->default(false)->index();
            $table->timestamps();
            $table->unique(['business_id', 'slug']);
            $table->unique(['business_id', 'sku']);
        });

        Schema::create('inventory', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('outlet_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->integer('quantity')->default(0);
            $table->unsignedInteger('reserved_quantity')->default(0);
            $table->unsignedInteger('low_stock_threshold')->default(5);
            $table->timestamps();
            $table->unique(['outlet_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory');
        Schema::dropIfExists('products');
        Schema::dropIfExists('categories');
    }
};

