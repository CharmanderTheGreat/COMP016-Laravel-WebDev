<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StudentSeeder extends Seeder
{
    /**
     * Demo students: BSIT 3-2, password "Student@123" for all of them.
     * Log in with the ID number. To add more, just add a line to the list below.
     * Safe to run again: existing students are updated, not duplicated.
     */
    public function run(): void
    {
        // [first name, last name]  -> ID and email are generated from the position in this list
        $names = [
            ['Juan', 'Dela Cruz'],     // 2024-00001-SR-0  (kept: the original demo accounts)
            ['Maria', 'Santos'],       // 2024-00002-SR-0
            ['Ana', 'Reyes'],
            ['Miguel', 'Garcia'],
            ['Bianca', 'Mendoza'],
            ['Carlo', 'Ramos'],
            ['Jasmine', 'Torres'],
            ['Dennis', 'Flores'],
            ['Katrina', 'Villanueva'],
            ['Paolo', 'Aquino'],
            ['Rina', 'Bautista'],
            ['Joshua', 'Castillo'],
            ['Angelica', 'Domingo'],
            ['Mark', 'Estrada'],
            ['Lea', 'Fernandez'],
            ['Rico', 'Gonzales'],
            ['Trisha', 'Hernandez'],
            ['Gabriel', 'Ignacio'],
            ['Nicole', 'Jimenez'],
            ['Vince', 'Lopez'],
            ['Chloe', 'Navarro'],
            ['Ethan', 'Ocampo'],
            ['Sofia', 'Pascual'],
            ['Kyle', 'Salazar'],
        ];

        DB::transaction(function () use ($names) {
            foreach ($names as $i => [$first, $last]) {
                $number = sprintf('2024-%05d-SR-0', $i + 1);

                // The first two keep their original emails so existing demo logins still work
                $email = match ($i) {
                    0 => 'juan@example.com',
                    1 => 'maria@example.com',
                    default => Str::lower($first . '.' . str_replace(' ', '', $last)) . '@example.com',
                };

                $user = User::updateOrCreate(
                    ['email' => $email],
                    [
                        'name'     => "{$first} {$last}",
                        'password' => 'Student@123', // hashed automatically by the User cast
                        'role'     => Role::Student,
                    ]
                );

                Student::updateOrCreate(
                    ['student_number' => $number],
                    [
                        'user_id'    => $user->id,
                        'first_name' => $first,
                        'last_name'  => $last,
                        'course'     => 'BSIT',
                        'year_level' => 3,
                        'section'    => 2,
                    ]
                );
            }
        });
    }
}
