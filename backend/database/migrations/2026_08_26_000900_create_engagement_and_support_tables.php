<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table): void {
            $table->id(); $table->foreignId('user_id')->constrained()->restrictOnDelete(); $table->foreignId('order_id')->constrained()->restrictOnDelete(); $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->unsignedTinyInteger('rating'); $table->string('title')->nullable(); $table->text('comment')->nullable(); $table->string('status')->default('published')->index(); $table->text('seller_reply')->nullable(); $table->dateTime('replied_at')->nullable(); $table->timestamps();
            $table->unique(['user_id', 'order_id', 'product_id']);
        });

        Schema::create('wishlist_items', function (Blueprint $table): void { $table->id(); $table->foreignId('user_id')->constrained()->cascadeOnDelete(); $table->foreignId('product_id')->constrained()->cascadeOnDelete(); $table->timestamps(); $table->unique(['user_id', 'product_id']); });

        Schema::create('device_tokens', function (Blueprint $table): void { $table->id(); $table->foreignId('user_id')->constrained()->cascadeOnDelete(); $table->string('app_type')->index(); $table->string('platform'); $table->string('token', 512)->unique(); $table->string('device_name')->nullable(); $table->dateTime('last_used_at')->nullable(); $table->boolean('is_active')->default(true)->index(); $table->timestamps(); });

        Schema::create('notifications', function (Blueprint $table): void { $table->uuid('id')->primary(); $table->string('type'); $table->morphs('notifiable'); $table->text('data'); $table->dateTime('read_at')->nullable(); $table->timestamps(); });

        Schema::create('promotion_banners', function (Blueprint $table): void { $table->id(); $table->string('title'); $table->string('subtitle')->nullable(); $table->string('image_path'); $table->string('target_type')->default('none'); $table->string('target_value')->nullable(); $table->string('audience')->default('customer')->index(); $table->unsignedInteger('sort_order')->default(0); $table->dateTime('starts_at')->nullable(); $table->dateTime('ends_at')->nullable(); $table->boolean('is_active')->default(true)->index(); $table->timestamps(); });

        Schema::create('cms_pages', function (Blueprint $table): void { $table->id(); $table->string('title'); $table->string('slug')->unique(); $table->longText('content'); $table->string('meta_title')->nullable(); $table->string('meta_description')->nullable(); $table->boolean('is_published')->default(false)->index(); $table->dateTime('published_at')->nullable(); $table->timestamps(); });

        Schema::create('support_tickets', function (Blueprint $table): void { $table->id(); $table->string('ticket_number')->unique(); $table->foreignId('user_id')->constrained()->restrictOnDelete(); $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete(); $table->string('subject'); $table->string('category')->index(); $table->string('priority')->default('normal')->index(); $table->string('status')->default('open')->index(); $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete(); $table->dateTime('closed_at')->nullable(); $table->timestamps(); });

        Schema::create('support_messages', function (Blueprint $table): void { $table->id(); $table->foreignId('support_ticket_id')->constrained()->cascadeOnDelete(); $table->foreignId('sender_id')->constrained('users')->restrictOnDelete(); $table->text('message'); $table->string('attachment_path')->nullable(); $table->boolean('is_internal')->default(false); $table->timestamps(); });
    }

    public function down(): void
    {
        Schema::dropIfExists('support_messages'); Schema::dropIfExists('support_tickets'); Schema::dropIfExists('cms_pages'); Schema::dropIfExists('promotion_banners'); Schema::dropIfExists('notifications'); Schema::dropIfExists('device_tokens'); Schema::dropIfExists('wishlist_items'); Schema::dropIfExists('reviews');
    }
};

