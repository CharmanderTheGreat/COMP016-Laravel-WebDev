<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StudentSeeder extends Seeder
{
    public function run(): void
    {
        $students = [
            [
                'first_name' => 'Juan',
                'last_name' => 'Dela Cruz',
                'email' => 'juan@example.com',
                'student_number' => '2024-00001-SR-0',
                'course' => 'BSIT',
                'year_level' => 3,
                'section' => 2,
            ],
            [
                'first_name' => 'Maria',
                'last_name' => 'Santos',
                'email' => 'maria@example.com',
                'student_number' => '2024-00002-SR-0',
                'course' => 'BSIT',
                'year_level' => 3,
                'section' => 2,
            ],
        ];

        DB::transaction(function () use ($students) {
            foreach ($students as $data) {
                $user = User::updateOrCreate(
                    ['email' => $data['email']],
                    [
                        'name' => $data['first_name'] . ' ' . $data['last_name'],
                        'password' => 'Student@123',
                        'role' => Role::Student,
                    ]
                );

                Student::updateOrCreate(
                    ['student_number' => $data['student_number']],
                    [
                        'user_id' => $user->id,
                        'first_name' => $data['first_name'],
                        'last_name' => $data['last_name'],
                        'course' => $data['course'],
                        'year_level' => $data['year_level'],
                        'section' => $data['section'],
                    ]
                );
            }
        });
    }
}
