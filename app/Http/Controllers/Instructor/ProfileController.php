<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/** The instructor's own account: change name / email / password. */
class ProfileController extends Controller
{
    public function show(Request $request)
    {
        return view('instructor.profile', ['user' => $request->user()]);
    }

    /** Change name and email (the email is what the instructor logs in with). */
    public function update(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'name'  => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
        ]);

        $user->update($data);

        return redirect()->route('instructor.profile')->with('success', 'Profile updated.');
    }

    /** Change password: the current one must be typed first. Errors go to the "password" bag. */
    public function password(Request $request)
    {
        $data = $request->validateWithBag('password', [
            'current_password' => ['required', 'current_password'],
            'password'         => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()],
        ]);

        $request->user()->update(['password' => $data['password']]); // hashed by the User cast

        return redirect()->route('instructor.profile')->with('success', 'Password changed.');
    }
}
