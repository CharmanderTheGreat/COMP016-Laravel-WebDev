<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class StudentController extends Controller
{
    /** All enrolled students, with a simple search (?q=name, ID number or email). */
    public function index(Request $request)
    {
        $search = trim((string) $request->query('q'));

        $students = Student::with('user')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('student_number', 'like', "%{$search}%")
                        ->orWhereHas('user', fn ($u) => $u->where('email', 'like', "%{$search}%"));
                });
            })
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->paginate(20)
            ->withQueryString();

        return view('instructor.students.index', [
            'students' => $students,
            'search'   => $search,
            'total'    => Student::count(),
        ]);
    }

    /**
     * "Forgot password" without email: the instructor generates a temporary password
     * and hands it to the student. It is shown once, right after this redirect.
     */
    public function resetPassword(Student $student)
    {
        $temporary = Str::password(10, symbols: false);

        $student->user->forceFill([
            'password'       => $temporary, // hashed by the User model cast
            'remember_token' => Str::random(60),
        ])->save();

        return back()->with('temp_password', [
            'name'           => $student->full_name,
            'student_number' => $student->student_number,
            'password'       => $temporary,
        ]);
    }
}
