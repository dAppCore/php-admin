<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('honeypot_hits', function (Blueprint $table) {
            $table->id();
            $table->string('ip_address', 45);
            $table->string('user_agent', 1000)->nullable();
            $table->string('referer', 2000)->nullable();
            $table->string('path', 255);
            $table->string('method', 10);
            $table->json('headers')->nullable();
            $table->string('country', 2)->nullable();
            $table->string('city', 100)->nullable();
            $table->boolean('is_bot')->default(false);
            $table->string('bot_name', 100)->nullable();
            $table->timestamps();

            $table->index('ip_address');
            $table->index('created_at');
            $table->index('is_bot');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('honeypot_hits');
    }
};
