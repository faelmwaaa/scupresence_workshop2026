<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Organization;

class OrganizationSeeder extends Seeder
{
    public function run(): void
    {
        // Level 1: Master Categories (Hidden from dropdown)
        $ormawa = Organization::create(['name' => 'ORMAWA', 'level' => 'master']);
        $ukmMaster = Organization::create(['name' => 'UKM', 'level' => 'master']);

        // Level 2: Sub-Categories
        $bemFakultas = Organization::create(['name' => 'Badan Eksekutif Mahasiswa (BEM)', 'level' => 'category', 'parent_id' => $ormawa->id]);
        $senatFakultas = Organization::create(['name' => 'Senat Mahasiswa (SM)', 'level' => 'category', 'parent_id' => $ormawa->id]);
        $ukmOlahraga = Organization::create(['name' => 'UKM Olahraga', 'level' => 'category', 'parent_id' => $ukmMaster->id]);
        $ukmSeni = Organization::create(['name' => 'UKM Kesenian', 'level' => 'category', 'parent_id' => $ukmMaster->id]);

        // Level 3: Direct Units (These are what the students actually select)
        
        // BEM Units
        Organization::create(['name' => 'BEM FIKOM', 'level' => 'unit', 'parent_id' => $bemFakultas->id]);
        Organization::create(['name' => 'BEM FEB', 'level' => 'unit', 'parent_id' => $bemFakultas->id]);
        
        // SM Units
        Organization::create(['name' => 'SM FIKOM', 'level' => 'unit', 'parent_id' => $senatFakultas->id]);
        
        // UKM Olahraga
        Organization::create(['name' => 'UKM Basket', 'level' => 'unit', 'parent_id' => $ukmOlahraga->id]);
        Organization::create(['name' => 'UKM Sepak Bola', 'level' => 'unit', 'parent_id' => $ukmOlahraga->id]);
        
        // UKM Seni
        Organization::create(['name' => 'UKM Gratia Choir', 'level' => 'unit', 'parent_id' => $ukmSeni->id]);
        Organization::create(['name' => 'UKM Tari', 'level' => 'unit', 'parent_id' => $ukmSeni->id]);

        // Direct University Units
        Organization::create(['name' => 'BEM Universitas', 'level' => 'unit', 'parent_id' => $ormawa->id]);
    }
}
