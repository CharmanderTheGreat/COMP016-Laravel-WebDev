<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Models\Student;
use Illuminate\Http\Request;

/** Serves a student's profile picture (stored in the database). */
class PhotoController extends Controller
{
    public function show(Request $request, Student $student)
    {
        $user = $request->user();

        // A student may only see their own picture; the instructor may see everyone's.
        abort_unless(
            $user->role === Role::Instructor || $user->student?->id === $student->id,
            403
        );

        $photo = $student->photo;
        abort_if($photo === null, 404);

        return response(base64_decode($photo->data), 200, [
            'Content-Type' => $photo->mime,
            'Cache-Control' => 'private, max-age=86400',
        ]);
    }
}
