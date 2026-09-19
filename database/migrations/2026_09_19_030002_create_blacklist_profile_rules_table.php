<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blacklist_profile_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blacklist_profile_id')->constrained('blacklist_profiles')->cascadeOnDelete();
            $table->string('domain', 255);
            $table->enum('type', ['exact', 'wildcard'])->default('exact');
            $table->boolean('enabled')->default(true);
            $table->timestamps();
            $table->unique(['blacklist_profile_id', 'domain', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blacklist_profile_rules');
    }
};
