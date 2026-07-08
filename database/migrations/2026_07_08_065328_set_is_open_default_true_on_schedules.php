<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Change column default to true
        Schema::table('schedules', function (Blueprint $table) {
            $table->boolean('is_open')->default(true)->change();
        });

        // Set all existing schedules to open by default
        DB::table('schedules')->update(['is_open' => true]);
    }

    public function down(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->boolean('is_open')->default(false)->change();
        });
    }
};
