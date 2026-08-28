<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('product_image_assets', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('group_name')->index();
            $table->string('name');
            $table->string('slug')->unique();
            $table->json('keywords')->nullable();
            $table->string('image_path');
            $table->string('alt_text')->nullable();
            $table->string('license_type');
            $table->string('license_source')->nullable();
            $table->boolean('is_active')->default(false)->index();
            $table->unsignedInteger('sort_order')->default(0);
            $table->unsignedBigInteger('usage_count')->default(0);
            $table->timestamps();
            $table->index(['group_name', 'is_active', 'sort_order']);
        });

        Schema::table('products', function (Blueprint $table): void {
            $table->foreignId('product_image_asset_id')->nullable()->after('category_id')->constrained()->nullOnDelete();
            $table->string('image_path')->nullable()->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('product_image_asset_id');
            $table->dropColumn('image_path');
        });
        Schema::dropIfExists('product_image_assets');
    }
};
