<?php

namespace App\Console\Commands;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

/**
 * Create a new instructor (or reset an existing instructor's name/password).
 * There is no instructor sign up page on purpose, so this is how you add one:
 *
 *   php artisan instructor:create prof@school.edu --name="Prof. Cruz"
 *   (it asks for the password; or pass --password=... for scripts)
 */
class CreateInstructor extends Command
{
    protected $signature = 'instructor:create
                            {email : Login email of the instructor}
                            {--name=Instructor : Display name}
                            {--password= : Password (asked interactively if left out)}';

    protected $description = 'Create an instructor account (or update an existing instructor)';

    public function handle(): int
    {
        $email = (string) $this->argument('email');
        $password = $this->option('password') ?: $this->secret('Password');

        $validator = Validator::make(
            ['email' => $email, 'name' => $this->option('name'), 'password' => $password],
            [
                'email'    => ['required', 'email', 'max:255'],
                'name'     => ['required', 'string', 'max:100'],
                'password' => ['required', Password::min(8)->mixedCase()->numbers()->symbols()],
            ]
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        // Never turn an existing STUDENT account into an instructor by accident
        $existing = User::where('email', $email)->first();
        if ($existing && $existing->role !== Role::Instructor) {
            $this->error("{$email} already belongs to a student account. Use a different email.");

            return self::FAILURE;
        }

        User::updateOrCreate(
            ['email' => $email],
            [
                'name'     => $this->option('name'),
                'password' => $password, // hashed by the User cast
                'role'     => Role::Instructor,
            ]
        );

        $this->info($existing ? "Instructor {$email} updated." : "Instructor {$email} created.");

        return self::SUCCESS;
    }
}
