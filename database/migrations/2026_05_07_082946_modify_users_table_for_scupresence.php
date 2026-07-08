<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('google_id')->nullable()->unique()->after('id');
            $table->string('profile_picture')->nullable()->after('password');
            $table->string('nim', 50)->nullable()->after('profile_picture');
            $table->string('phone', 20)->nullable()->after('nim');
            $table->string('fakultas', 100)->nullable()->after('phone');
            $table->enum('role', ['admin', 'pengurus', 'pelatih', 'anggota'])->nullable()->after('fakultas');
            $table->enum('account_status', ['pending', 'active'])->default('pending')->after('role');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['google_id', 'profile_picture', 'nim', 'phone', 'fakultas', 'role', 'account_status']);
        });
    }
};
