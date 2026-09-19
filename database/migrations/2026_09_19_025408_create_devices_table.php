<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('devices', function (Blueprint $table) {
            $table->id();
            $table->uuid('machine_id')->unique()->index();
            $table->string('name', 255);
            $table->string('os_version', 64)->nullable();
            $table->string('api_token', 80)->unique();
            $table->unsignedInteger('policy_version')->default(1);
            $table->enum('status', ['never', 'online', 'degraded', 'offline'])->default('never');
            $table->timestamp('last_seen_at')->nullable();
            $table->unsignedInteger('applied_version')->nullable();
            $table->json('last_heartbeat')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('devices');
    }
};
