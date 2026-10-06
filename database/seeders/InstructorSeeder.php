<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class InstructorSeeder extends Seeder
{
    /**
     * Creates the one instructor account (no instructor sign up exists).
     * CHANGE the name/email/password below to the prof's real details.
     * updateOrCreate = safe to run again without duplicating.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'instructor@example.com'],
            [
                'name'     => 'Instructor',
                'password' => 'Instructor@123', // hashed automatically by the User cast
                'role'     => Role::Instructor,
            ]
        );
    }
}