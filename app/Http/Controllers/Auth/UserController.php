<?php

namespace App\Http\Controllers\Auth;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    /** Show the student sign up form. */
    public function create()
    {
        return view('auth.register');
    }

    /**
     * Handle student sign up.
     * Only students can register; the instructor is seeded (InstructorSeeder).
     */
    public function store(Request $request)
    {
        // Normalize the ID so "2024-00482-sr-0" is accepted as "2024-00482-SR-0"
        $request->merge([
            'student_number' => strtoupper(trim((string) $request->input('student_number'))),
        ]);

        $data = $request->validate([
            'first_name'     => ['required', 'string', 'max:100'],
            'last_name'      => ['required', 'string', 'max:100'],
            'email'          => ['required', 'email', 'max:255', 'unique:users,email'],
            'student_number' => ['required', 'regex:' . Student::ID_REGEX, 'unique:students,student_number'],
            'course'         => ['required', Rule::in(Student::COURSES)],
            'year_level'     => ['required', 'integer', 'between:1,4'],
            'section'        => ['required', 'integer', 'between:1,9'],
            'profile_photo'  => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:2048'], // 2MB
            // min 8, upper + lower, number, symbol; must match password_confirmation
            'password'       => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()],
        ], [
            'student_number.regex' => 'ID number must look like 2024-00482-SR-0.',
        ]);

        // Save the picture to storage/app/public/profile-photos
        $photoPath = $request->file('profile_photo')->store('profile-photos', 'public');

        // Create the user + student profile together (all or nothing)
        $user = DB::transaction(function () use ($data, $photoPath) {
            $user = User::create([
                'name'     => $data['first_name'] . ' ' . $data['last_name'],
                'email'    => $data['email'],
                'password' => $data['password'], // hashed by the User model cast
                'role'     => Role::Student,     // hardcoded: nobody can self-register as instructor
            ]);

            $user->student()->create([
                'first_name'     => $data['first_name'],
                'last_name'      => $data['last_name'],
                'student_number' => $data['student_number'],
                'course'         => $data['course'],
                'year_level'     => $data['year_level'],
                'section'        => $data['section'],
                'profile_photo'  => $photoPath,
            ]);

            return $user;
        });

        // Log them in and send them to their dashboard
        Auth::login($user);
        $request->session()->regenerate();

        return redirect($user->role->homePath());
    }
}