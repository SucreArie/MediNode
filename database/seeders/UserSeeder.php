<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Admin MediNode',
            'email' => 'admin@medinode.local',
            'password' => Hash::make('password123'),
            'etablissement' => 'Direction',
            'role' => 'admin',
        ]);

        User::create([
            'name' => 'Docteur MediNode',
            'email' => 'doctor@medinode.local',
            'password' => Hash::make('password123'),
            'etablissement' => 'Clinique Centrale',
            'role' => 'doctor',
        ]);

        User::create([
            'name' => 'Receptionniste MediNode',
            'email' => 'receptionist@medinode.local',
            'password' => Hash::make('password123'),
            'etablissement' => 'Clinique Centrale',
            'role' => 'receptionist',
        ]);

        User::create([
            'name' => 'Patient MediNode',
            'email' => 'patient@medinode.local',
            'password' => Hash::make('password123'),
            'etablissement' => 'Clinique Centrale',
            'role' => 'patient',
        ]);
    }
}
