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
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            
            // Links to the Organization and the BPH who created it
            $table->foreignId('organization_id')->constrained('organizations')->onDelete('cascade');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            
            // Event Details
            $table->dateTime('waktu_mulai');
            $table->dateTime('waktu_selesai');
            $table->string('lokasi');
            $table->json('jenis_kegiatan'); // E.g., ["Cardio", "Sparring"]
            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
