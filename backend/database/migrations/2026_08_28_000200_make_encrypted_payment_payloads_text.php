<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_transactions', function (Blueprint $table): void {
            $table->longText('provider_payload')->nullable()->change();
        });
        Schema::table('refunds', function (Blueprint $table): void {
            $table->longText('provider_payload')->nullable()->change();
        });
        Schema::table('payment_webhook_events', function (Blueprint $table): void {
            $table->longText('payload')->change();
        });
    }

    public function down(): void
    {
        Schema::table('payment_transactions', function (Blueprint $table): void {
            $table->json('provider_payload')->nullable()->change();
        });
        Schema::table('refunds', function (Blueprint $table): void {
            $table->json('provider_payload')->nullable()->change();
        });
        Schema::table('payment_webhook_events', function (Blueprint $table): void {
            $table->json('payload')->change();
        });
    }
};
