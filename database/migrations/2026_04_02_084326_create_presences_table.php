<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('presences', function (Blueprint $table) {
            $table->id();
            
            // Links to the Schedule and the User
            $table->foreignId('schedule_id')->constrained('schedules')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            // The core data
            $table->enum('status_kehadiran', ['hadir', 'tidak_hadir']);
            $table->text('alasan_absen')->nullable();
            
            // Hardware Data (GPS & Camera)
            $table->string('foto_path')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('kegiatan_dipilih')->nullable();
            
            // BPH Validation Gate
            $table->enum('status_validasi', ['pending', 'approved', 'rejected'])->default('pending');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('presences');
    }
};
