<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('user_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('telegram_id')->on('users')->cascadeOnDelete();
            $table->string('currency')->default('KZT');
            $table->string('language')->default('ru');
            $table->string('timezone')->nullable();
            $table->boolean('reminders_enabled')->default(true);
            $table->unsignedTinyInteger('reminder_hour')->default(22);
            $table->unsignedTinyInteger('reminder_minute')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_settings');
    }
};
