<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('coupons', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('business_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('code')->unique();
            $table->string('discount_type');
            $table->decimal('discount_value', 12, 2);
            $table->decimal('maximum_discount', 12, 2)->nullable();
            $table->decimal('minimum_order', 12, 2)->default(0);
            $table->unsignedInteger('usage_limit')->nullable();
            $table->unsignedInteger('per_user_limit')->default(1);
            $table->timestamp('starts_at');
            $table->timestamp('ends_at');
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::table('orders', function (Blueprint $table): void {
            $table->foreignId('coupon_id')->nullable()->after('address_id')->constrained()->nullOnDelete();
            $table->string('coupon_code')->nullable()->after('coupon_id');
            $table->text('cancellation_reason')->nullable();
            $table->timestamp('cancelled_at')->nullable();
        });

        Schema::create('coupon_redemptions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('coupon_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('order_id')->unique()->constrained()->restrictOnDelete();
            $table->decimal('discount_amount', 12, 2);
            $table->timestamp('redeemed_at');
            $table->timestamps();
            $table->index(['coupon_id', 'user_id']);
        });

        Schema::create('refunds', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')->constrained()->restrictOnDelete();
            $table->foreignId('payment_transaction_id')->constrained()->restrictOnDelete();
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('provider_refund_id')->nullable()->unique();
            $table->decimal('amount', 12, 2);
            $table->text('reason');
            $table->string('status')->default('requested')->index();
            $table->json('provider_payload')->nullable();
            $table->timestamp('requested_at');
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('seller_settlements', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('business_id')->constrained()->restrictOnDelete();
            $table->string('settlement_number')->unique();
            $table->date('period_start');
            $table->date('period_end');
            $table->decimal('gross_sales', 14, 2);
            $table->decimal('discount_total', 14, 2)->default(0);
            $table->decimal('refund_total', 14, 2)->default(0);
            $table->decimal('commission_total', 14, 2)->default(0);
            $table->decimal('tax_total', 14, 2)->default(0);
            $table->decimal('net_payable', 14, 2);
            $table->string('status')->default('draft')->index();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->string('payment_reference')->nullable()->unique();
            $table->timestamps();
        });

        Schema::create('seller_settlement_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('seller_settlement_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->unique()->constrained()->restrictOnDelete();
            $table->decimal('gross_amount', 12, 2);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('refund_amount', 12, 2)->default(0);
            $table->decimal('commission_rate', 5, 2);
            $table->decimal('commission_amount', 12, 2);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('net_amount', 12, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seller_settlement_items');
        Schema::dropIfExists('seller_settlements');
        Schema::dropIfExists('refunds');
        Schema::dropIfExists('coupon_redemptions');
        Schema::table('orders', function (Blueprint $table): void { $table->dropConstrainedForeignId('coupon_id'); $table->dropColumn(['coupon_code', 'cancellation_reason', 'cancelled_at']); });
        Schema::dropIfExists('coupons');
    }
};

