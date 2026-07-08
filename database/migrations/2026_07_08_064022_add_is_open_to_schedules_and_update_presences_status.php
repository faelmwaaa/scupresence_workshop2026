<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add is_open column to schedules
        Schema::table('schedules', function (Blueprint $table) {
            $table->boolean('is_open')->default(false)->after('description');
        });

        // Change presences status enum to include 'menunggu'
        DB::statement("ALTER TABLE presences MODIFY COLUMN status ENUM('menunggu', 'hadir', 'terlambat', 'tidak_hadir') NOT NULL DEFAULT 'menunggu'");
    }

    public function down(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->dropColumn('is_open');
        });

        DB::statement("ALTER TABLE presences MODIFY COLUMN status ENUM('hadir', 'terlambat', 'tidak_hadir') NOT NULL");
    }
};
