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
            'BEM (Badan Eksekutif Mahasiswa)',
            'DPM (Dewan Perwakilan Mahasiswa)',
            'HIMA Teknik',
            'HIMA Ekonomi',
            'HIMA Hukum',
            'HIMA Psikologi',
        ];

        foreach ($ormawas as $name) {
            Organization::create(['name' => $name, 'type' => 'ormawa']);
        }

        // Seed UKM organizations
        $ukms = [
            'UKM Basket',
            'UKM Futsal',
            'UKM Badminton',
            'UKM Voli',
            'UKM Taekwondo',
            'UKM Renang',
            'UKM Paduan Suara',
            'UKM Teater',
            'UKM Fotografi',
            'UKM English Club',
        ];

        foreach ($ukms as $name) {
            Organization::create(['name' => $name, 'type' => 'ukm']);
        }
    }
}
