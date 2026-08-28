<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('carts', function (Blueprint $table): void {
            $table->index('user_id', 'carts_user_id_index');
            $table->index('business_id', 'carts_business_id_index');
        });
        Schema::table('carts', function (Blueprint $table): void {
            $table->dropUnique('carts_user_id_business_id_status_unique');
            $table->index(['user_id', 'business_id', 'status'], 'carts_user_business_status_index');
        });
    }

    public function down(): void
    {
        Schema::table('carts', function (Blueprint $table): void {
            $table->dropIndex('carts_user_business_status_index');
            $table->unique(['user_id', 'business_id', 'status']);
            $table->dropIndex('carts_user_id_index');
            $table->dropIndex('carts_business_id_index');
        });
    }
};
