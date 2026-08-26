<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('businesses', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('type')->index();
            $table->string('status')->default('pending')->index();
            $table->string('phone', 20);
            $table->string('email')->nullable();
            $table->string('tax_number')->nullable();
            $table->decimal('commission_rate', 5, 2)->default(0);
            $table->boolean('seller_delivery_enabled')->default(true);
            $table->boolean('cnet_delivery_enabled')->default(true);
            $table->text('rejection_reason')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });

        Schema::create('outlets', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('phone', 20);
            $table->string('address_line');
            $table->string('landmark')->nullable();
            $table->string('city')->default('Bihar Sharif');
            $table->string('postal_code', 10);
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->decimal('service_radius_km', 6, 2)->default(5);
            $table->decimal('minimum_order', 10, 2)->default(0);
            $table->string('status')->default('pending')->index();
            $table->time('opens_at')->nullable();
            $table->time('closes_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('outlets');
        Schema::dropIfExists('businesses');
    }
};

