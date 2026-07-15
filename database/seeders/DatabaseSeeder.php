<?php

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create Admin user
        User::create([
            'name'           => 'Super Admin',
            'email'          => 'admin@scupresence.ac.id',
            'password'       => Hash::make('admin123'),
            'role'           => 'admin',
            'account_status' => 'active',
        ]);

        // Seed ORMAWA organizations
        $ormawas = [
            'BEM Universitas',
            'Senat Universitas',
            'BEM Fakultas Arsitektur & Desain',
            'BEM Fakultas Bahasa & Seni',
            'BEM Fakultas Ekonomi & Bisnis',
            'BEM Fakultas Hukum & Komunikasi',
            'BEM Fakultas Ilmu & Teknologi Lingkungan',
            'BEM Fakultas Ilmu Komputer',
            'BEM Fakultas Kedokteran',
            'BEM Fakultas Psikologi',
            'BEM Fakultas Teknik',
            'BEM Fakultas Teknologi Pertanian',
        ];

        foreach ($ormawas as $name) {
            Organization::create(['name' => $name, 'type' => 'ormawa']);
        }

        // Seed UKM organizations
        $ukms = [
            'GRATIA Choir',
            'Teater',
            'Orkes',
            'GRATIA Voice',
            'MIROR',
            'Karawitan',
            'Kembangtaru',
            'SDS',
            'Perisai Diri',
            'Badminton',
            'MENWA',
            'Tennis',
            'Karate',
            'Basket',
            'SARAS',
            'Sepak Bola',
            'Wanacaraka',
            'SEL',
            'KSR',
            'Racana',
            'SBC',
            'IMA',
            'SOEPRA',
        ];

        foreach ($ukms as $name) {
            Organization::create(['name' => $name, 'type' => 'ukm']);
        }
    }
}
