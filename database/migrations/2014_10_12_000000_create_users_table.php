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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            
            // --- SCUPresence Custom Columns ---
            // These are nullable because BPH doesn't need NIM, and Anggota doesn't need Phone/Jabatan
            $table->string('nim')->nullable(); 
            $table->string('phone')->nullable(); 
            $table->string('jabatan')->nullable(); // e.g., 'Ketua', 'Pelatih'
            
            // Role and Status Management
            $table->enum('role', ['admin', 'bph', 'anggota'])->default('anggota');
            $table->enum('account_status', ['pending', 'active', 'rejected'])->default('active');
            
            // The link to the Organization table (Where do they belong?)
            $table->foreignId('organization_id')
                  ->nullable()
                  ->constrained('organizations')
                  ->onDelete('set null');

            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
