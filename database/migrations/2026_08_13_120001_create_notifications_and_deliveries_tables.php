<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('user_telegram_accounts');
        Schema::dropIfExists('notification_preferences');
        Schema::dropIfExists('notification_deliveries');
        Schema::dropIfExists('notifications');

        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recipient_id')->constrained('users')->onDelete('cascade');
            $table->string('type');
            $table->string('priority')->default('normal');
            $table->string('title');
            $table->text('body');
            $table->string('action_url')->nullable();
            $table->json('data')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['recipient_id', 'read_at', 'created_at']);
            $table->index(['type', 'created_at']);
        });

        Schema::create('notification_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('notification_id')->constrained('notifications')->onDelete('cascade');
            $table->string('channel');
            $table->string('status')->default('pending');
            $table->string('provider_message_id')->nullable();
            $table->unsignedInteger('attempts')->default(0);
            $table->timestamp('last_attempt_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->text('error_message')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['notification_id', 'channel', 'status']);
        });

        Schema::create('notification_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('notification_type');
            $table->string('channel');
            $table->boolean('enabled')->default(true);
            $table->timestamps();

            $table->unique(['user_id', 'notification_type', 'channel'], 'noti_pref_user_type_channel_unique');
        });

        Schema::create('user_telegram_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('telegram_chat_id')->nullable()->unique();
            $table->string('telegram_user_id')->nullable();
            $table->string('username')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('linked_at')->nullable();
            $table->timestamp('unlinked_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('linking_token')->nullable()->unique();
            $table->timestamp('token_expires_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_telegram_accounts');
        Schema::dropIfExists('notification_preferences');
        Schema::dropIfExists('notification_deliveries');
        Schema::dropIfExists('notifications');
    }
};
