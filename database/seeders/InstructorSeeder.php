<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class InstructorSeeder extends Seeder
{
    /**
     * Creates the default instructor account, but ONLY when there is no instructor yet.
     *
     * This matters on Railway: its start command runs `db:seed` on every deploy.
     * If this seeder overwrote the account each time, the instructor's new password
     * (changed on the Profile page) would be reset to the default after every deploy.
     *
     * CHANGE the name/email/password below to the prof's real details, or create
     * another instructor with:  php artisan instructor:create
     */
    public function run(): void
    {
        if (User::where('role', Role::Instructor->value)->exists()) {
            return;
        }

        User::create([
            'name'     => 'Instructor',
            'email'    => 'instructor@example.com',
            'password' => 'Instructor@123', // hashed automatically by the User cast
            'role'     => Role::Instructor,
        ]);
    }
}
