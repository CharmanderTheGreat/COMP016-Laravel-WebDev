<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $days = Attendance::select("date", DB::raw('count(*) as present_count'))
        ->groupBy('date')
        ->orderByDesc('date')
        ->paginate(15);

        return view("instructor.attendance.index",  [
            "days" => $days,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('instructor.attendance.create', [
            "students" => Student::all(),
            "date"=> today()
        ]);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            "ids" => ["required", "array", "min:1"],
            "ids.*" => ["integer", "exists:students,id"],
        ]);

        foreach ($validated["ids"] as $id) {
            Attendance::firstOrCreate([
                "student_id" => $id,
                "date" => today()
            ]);
        }

        return redirect("/instructor/attendance");
    }

    /**
     * Display the specified resource.
     */
    public function show(string $date)
    {
        $attendances = Attendance::with("student.user")
            ->whereDate("date", $date)
            ->get()
            ->sortBy(fn ($a) => $a->student->user->name)
            ->values();

        abort_if($attendances->isEmpty(), 404);

        return view("instructor.attendance.show",  [
            "attendances" => $attendances,
            "date"=> Carbon::parse($date)
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Attendance $attendance)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Attendance $attendance)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Attendance $attendance)
    {
        //
    }
}
