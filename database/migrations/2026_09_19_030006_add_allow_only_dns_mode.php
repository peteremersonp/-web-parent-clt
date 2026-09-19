<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blacklist_profiles', function (Blueprint $table) {
            $table->enum('dns_mode', ['local-filter', 'off', 'block-all', 'allow-only'])->default('local-filter')->change();
        });
    }

    public function down(): void
    {
        Schema::table('blacklist_profiles', function (Blueprint $table) {
            $table->enum('dns_mode', ['local-filter', 'off', 'block-all'])->default('local-filter')->change();
        });
    }
};
