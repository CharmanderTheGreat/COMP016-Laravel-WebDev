<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SessionsController extends Controller
{
    /** Show the login form. */
    public function create()
    {
        return view('auth.login');
    }

    /**
     * One "login" field for everyone:
     *  - Students log in with their ID number only (their email is just shown on the profile).
     *  - The instructor has no ID, so they log in with their email.
     * After login, the user's role decides which dashboard they land on.
     */
    public function store(Request $request)
    {
        $request->validate([
            'login'    => ['required', 'string', 'max:255'],
            'password' => ['required', 'string'],
        ]);

        $login = trim($request->input('login'));
        $password = $request->input('password');

        if (preg_match(Student::ID_REGEX, strtoupper($login))) {
            // Looks like a students ID -> find that students's account email
            $email = User::whereHas(
                'student',
                fn ($q) => $q->where('student_number', strtoupper($login))
            )->value('email') ?? '';

            $credentials = ['email' => $email, 'password' => $password];
        } else {
            // Anything else is treated as an email, and ONLY instructors may use it.
            // The extra 'role' condition makes a students's email fail here.
            $credentials = [
                'email'    => $login,
                'password' => $password,
                'role'     => Role::Instructor->value,
            ];
        }

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate(); // prevents session fixation

            // Student -> /students/dashboard, instructor -> /instructor/dashboard
            return redirect()->intended(Auth::user()->role->homePath());
        }

        // One generic message so we don't reveal which part was wrong
        return back()
            ->withErrors(['login' => 'Invalid ID number / email or password.'])
            ->onlyInput('login');
    }

    /** Log out (POST /logout). */
    public function destroy(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
