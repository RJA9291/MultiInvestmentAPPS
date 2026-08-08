<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** DB-029: notification_preferences (Notification Context, already locked). Not present in the Project Owner's sketch at all — built as originally designed, not omitted. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_preferences', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('user_id'); // UserReference, no DB FK (PDL-020)
            $table->string('notification_type');
            $table->string('channel'); // in_app | email

            $table->boolean('is_enabled')->default(true);

            $table->timestamps();

            $table->unique(['user_id', 'notification_type', 'channel']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_preferences');
    }
};
