<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blacklist_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->nullable()
                ->index()
                ->constrained('devices')
                ->cascadeOnDelete();
            $table->string('domain', 255);
            $table->enum('type', ['exact', 'wildcard'])->default('exact');
            $table->boolean('enabled')->default(true);
            $table->timestamps();

            $table->unique(['device_id', 'domain', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blacklist_rules');
    }
};
