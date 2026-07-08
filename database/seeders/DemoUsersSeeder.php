<?php

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoUsersSeeder extends Seeder
{
    public function run(): void
    {
        $ormawa = Organization::where('type', 'ormawa')->first();
        $ukm    = Organization::where('type', 'ukm')->first();

        // Demo Anggota
        $anggota = User::firstOrCreate(
            ['email' => 'anggota@demo.com'],
            [
                'name'           => 'Budi Santoso',
                'password'       => Hash::make('demo'),
                'role'           => 'anggota',
                'account_status' => 'active',
                'nim'            => '21.B1.0001',
                'phone'          => '08123456789',
                'fakultas'       => 'Fakultas Ilmu Komputer',
            ]
        );
        if ($ormawa && !$anggota->organizations()->where('organization_id', $ormawa->id)->exists()) {
            $anggota->organizations()->attach($ormawa->id, ['jabatan' => 'Anggota', 'membership_status' => 'active']);
        }

        // Demo Pengurus
        $pengurus = User::firstOrCreate(
            ['email' => 'pengurus@demo.com'],
            [
                'name'           => 'Sari Wulandari',
                'password'       => Hash::make('demo'),
                'role'           => 'pengurus',
                'account_status' => 'active',
                'nim'            => '20.B1.0010',
                'phone'          => '08234567890',
                'fakultas'       => 'Fakultas Ekonomi dan Bisnis',
            ]
        );
        if ($ormawa && !$pengurus->organizations()->where('organization_id', $ormawa->id)->exists()) {
            $pengurus->organizations()->attach($ormawa->id, ['jabatan' => 'Ketua', 'membership_status' => 'active']);
        }

        // Demo Pelatih
        $pelatih = User::firstOrCreate(
            ['email' => 'pelatih@demo.com'],
            [
                'name'           => 'Agus Prasetyo',
                'password'       => Hash::make('demo'),
                'role'           => 'pelatih',
                'account_status' => 'active',
                'phone'          => '08345678901',
            ]
        );
        if ($ukm && !$pelatih->organizations()->where('organization_id', $ukm->id)->exists()) {
            $pelatih->organizations()->attach($ukm->id, ['membership_status' => 'active']);
        }

        $this->command->info('Demo users ready!');
    }
}
