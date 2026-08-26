<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('delivery_assignments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')->unique()->constrained()->restrictOnDelete();
            $table->foreignId('delivery_partner_id')->constrained()->restrictOnDelete();
            $table->foreignId('assigned_by')->constrained('users')->restrictOnDelete();
            $table->string('status')->default('assigned')->index();
            $table->string('pickup_otp', 6);
            $table->string('delivery_otp', 6);
            $table->timestamp('assigned_at');
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('picked_up_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->text('failure_reason')->nullable();
            $table->timestamps();
        });

        Schema::create('delivery_locations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('delivery_assignment_id')->constrained()->cascadeOnDelete();
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->decimal('accuracy_meters', 8, 2)->nullable();
            $table->timestamp('recorded_at')->index();
            $table->timestamps();
            $table->index(['delivery_assignment_id', 'recorded_at']);
        });

        Schema::create('order_status_history', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('from_status')->nullable();
            $table->string('to_status')->index();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('actor_role')->nullable();
            $table->text('note')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->index(['order_id', 'created_at']);
        });

        Schema::create('delivery_earnings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('delivery_assignment_id')->unique()->constrained()->restrictOnDelete();
            $table->foreignId('delivery_partner_id')->constrained()->restrictOnDelete();
            $table->decimal('base_amount', 10, 2)->default(0);
            $table->decimal('distance_amount', 10, 2)->default(0);
            $table->decimal('incentive_amount', 10, 2)->default(0);
            $table->decimal('deduction_amount', 10, 2)->default(0);
            $table->decimal('net_amount', 10, 2)->default(0);
            $table->string('status')->default('pending')->index();
            $table->timestamp('settled_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_earnings');
        Schema::dropIfExists('order_status_history');
        Schema::dropIfExists('delivery_locations');
        Schema::dropIfExists('delivery_assignments');
    }
};

