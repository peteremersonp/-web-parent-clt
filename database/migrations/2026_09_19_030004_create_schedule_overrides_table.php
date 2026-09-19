<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Overrides por dispositivo sobre la programación por defecto.
        //   action = 'delete'  -> la franja default NO aplica para este dispositivo ("borrar la
        //                         ocurrencia de ese dia para ese dispositivo").
        //   action = 'replace' -> la franja default usa profile_id (reemplazo) para este dispositivo.
        Schema::create('schedule_overrides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained()->cascadeOnDelete();
            $table->foreignId('window_id')->constrained('schedule_windows')->cascadeOnDelete();
            $table->enum('action', ['delete', 'replace'])->default('delete');
            $table->foreignId('profile_id')->nullable()->constrained('blacklist_profiles')->nullOnDelete();
            $table->timestamps();
            $table->unique(['device_id', 'window_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedule_overrides');
    }
};
