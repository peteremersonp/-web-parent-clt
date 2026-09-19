<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Lista de dominios PERMITIDOS por perfil (para dns_mode = allow-only:
        // se bloquea todo excepto estos dominios y sus subdominios).
        Schema::create('blacklist_profile_allows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blacklist_profile_id')->constrained('blacklist_profiles')->cascadeOnDelete();
            $table->string('domain', 255);
            $table->boolean('enabled')->default(true);
            $table->timestamps();
            $table->unique(['blacklist_profile_id', 'domain']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blacklist_profile_allows');
    }
};
