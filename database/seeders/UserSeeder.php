<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        DB::table('users')->insert([

            // ── Employee ──────────────────────────────────────────────────────
            [
                'id'               => 'EMP-0001',
                'username'         => 'employee1',
                'password'         => Hash::make('password'),
                'fullName'         => 'Juan Dela Cruz',
                'role'             => 'employee',
                'employmentStatus' => 'regular',
                'isActive'         => 1,

                'firstName'        => 'Juan',
                'middleName'       => 'Santos',
                'lastName'         => 'Dela Cruz',
                'gender'           => 'Male',
                'dateOfBirth'      => '1995-06-15',
                'civilStatus'      => 'Single',
                'email'            => 'juan.delacruz@fastservices.com',
                'phoneNumber'      => '09171234501',

                'addressStreet'    => '12 Mabini St.',
                'addressBarangay'  => 'Barangay Poblacion',
                'addressCity'      => 'Meycauayan City',
                'addressProvince'  => 'Bulacan',
                'addressRegion'    => 'Region III – Central Luzon',
                'addressZipCode'   => '3020',

                'department'       => 'Operations',
                'position'         => 'Staff',
                'branch'           => 'Meycauayan Main',
                'hireDate'         => '2022-01-10',

                'basicSalary'      => 18000.00,
                'dailyRate'        => 692.31,
                'hourlyRate'       => 86.54,

                'biometricEnrolled'=> 0,
                'created_at'       => $now,
                'updated_at'       => $now,
            ],

            // ── HR ────────────────────────────────────────────────────────────
            [
                'id'               => 'EMP-0002',
                'username'         => 'hr1',
                'password'         => Hash::make('password'),
                'fullName'         => 'Maria Santos Reyes',
                'role'             => 'hr',
                'employmentStatus' => 'regular',
                'isActive'         => 1,

                'firstName'        => 'Maria',
                'middleName'       => 'Santos',
                'lastName'         => 'Reyes',
                'gender'           => 'Female',
                'dateOfBirth'      => '1990-03-22',
                'civilStatus'      => 'Married',
                'email'            => 'maria.reyes@fastservices.com',
                'phoneNumber'      => '09181234502',

                'addressStreet'    => '45 Rizal Ave.',
                'addressBarangay'  => 'Barangay San Isidro',
                'addressCity'      => 'Meycauayan City',
                'addressProvince'  => 'Bulacan',
                'addressRegion'    => 'Region III – Central Luzon',
                'addressZipCode'   => '3020',

                'department'       => 'Human Resources',
                'position'         => 'HR Specialist',
                'branch'           => 'Meycauayan Main',
                'hireDate'         => '2020-05-01',

                'basicSalary'      => 25000.00,
                'dailyRate'        => 961.54,
                'hourlyRate'       => 120.19,

                'biometricEnrolled'=> 0,
                'created_at'       => $now,
                'updated_at'       => $now,
            ],

            // ── Accounting ────────────────────────────────────────────────────
            [
                'id'               => 'EMP-0003',
                'username'         => 'acct1',
                'password'         => Hash::make('password'),
                'fullName'         => 'Pedro Reyes Gomez',
                'role'             => 'accounting',
                'employmentStatus' => 'regular',
                'isActive'         => 1,

                'firstName'        => 'Pedro',
                'middleName'       => 'Reyes',
                'lastName'         => 'Gomez',
                'gender'           => 'Male',
                'dateOfBirth'      => '1988-11-08',
                'civilStatus'      => 'Married',
                'email'            => 'pedro.gomez@fastservices.com',
                'phoneNumber'      => '09191234503',

                'addressStreet'    => '78 Luna St.',
                'addressBarangay'  => 'Barangay Sta. Maria',
                'addressCity'      => 'Meycauayan City',
                'addressProvince'  => 'Bulacan',
                'addressRegion'    => 'Region III – Central Luzon',
                'addressZipCode'   => '3020',

                'department'       => 'Accounting',
                'position'         => 'Accountant',
                'branch'           => 'Meycauayan Main',
                'hireDate'         => '2019-08-15',

                'basicSalary'      => 28000.00,
                'dailyRate'        => 1076.92,
                'hourlyRate'       => 134.62,

                'biometricEnrolled'=> 0,
                'created_at'       => $now,
                'updated_at'       => $now,
            ],

            // ── Admin ─────────────────────────────────────────────────────────
            [
                'id'               => 'EMP-0004',
                'username'         => 'admin1',
                'password'         => Hash::make('password'),
                'fullName'         => 'Admin User',
                'role'             => 'admin',
                'employmentStatus' => 'regular',
                'isActive'         => 1,

                'firstName'        => 'Admin',
                'middleName'       => null,
                'lastName'         => 'User',
                'gender'           => 'Male',
                'dateOfBirth'      => '1985-01-01',
                'civilStatus'      => 'Single',
                'email'            => 'admin@fastservices.com',
                'phoneNumber'      => '09001234504',

                'addressStreet'    => '1 Admin Road',
                'addressBarangay'  => 'Barangay Central',
                'addressCity'      => 'Meycauayan City',
                'addressProvince'  => 'Bulacan',
                'addressRegion'    => 'Region III – Central Luzon',
                'addressZipCode'   => '3020',

                'department'       => 'Administration',
                'position'         => 'System Administrator',
                'branch'           => 'Meycauayan Main',
                'hireDate'         => '2018-01-01',

                'basicSalary'      => 35000.00,
                'dailyRate'        => 1346.15,
                'hourlyRate'       => 168.27,

                'biometricEnrolled'=> 0,
                'created_at'       => $now,
                'updated_at'       => $now,
            ],

        ]);
    }
}