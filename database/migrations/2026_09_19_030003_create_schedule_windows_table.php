<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Programación por defecto: franjas día+horario -> perfil.
        // day_of_week sigue el estándar de .NET: 0=Domingo ... 6=Sábado.
        // start_time/end_time en formato "HH:mm" 24h; start inclusive, end exclusive.
        // NO se permiten ventanas que crucen la medianoche (se modelan como dos franjas).
        Schema::create('schedule_windows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->constrained('blacklist_profiles')->cascadeOnDelete();
            $table->unsignedTinyInteger('day_of_week');
            $table->time('start_time');
            $table->time('end_time');
            $table->boolean('enabled')->default(true);
            $table->timestamps();
            $table->unique(['profile_id', 'day_of_week', 'start_time', 'end_time'], 'schedule_slot_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedule_windows');
    }
};
