<?php

namespace App\Http\Controllers\Instructor;

use App\Enums\AttendanceStatus;
use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Student;
use App\Models\Subject;
use App\Support\AttendanceExport;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AttendanceController extends Controller
{
    /** List of days that have attendance, with a count per status. */
    public function index()
    {
        $days = Attendance::query()
            ->select('date')
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present_count")
            ->selectRaw("SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late_count")
            ->selectRaw("SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent_count")
            ->selectRaw("SUM(CASE WHEN status = 'excused' THEN 1 ELSE 0 END) as excused_count")
            ->groupBy('date')
            ->orderByDesc('date')
            ->paginate(15);

        return view('instructor.attendance.index', ['days' => $days]);
    }

    /**
     * Record (or edit) attendance for one day. ?date=2026-10-05 (defaults to today).
     * Students already saved for that day show their saved status.
     */
    public function create(Request $request)
    {
        $date = $this->resolveDate($request->query('date'));

        $students = Student::orderBy('last_name')->orderBy('first_name')->get();

        $existing = Attendance::whereDate('date', $date->toDateString())
            ->get()
            ->keyBy('student_id')
            ->map(fn ($a) => $a->status);

        return view('instructor.attendance.create', [
            'students' => $students,
            'existing' => $existing,
            'date'     => $date,
        ]);
    }

    /** Save the whole class for one day (inserts new rows, updates existing ones). */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'date'       => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
            'statuses'   => ['required', 'array', 'min:1'],
            'statuses.*' => ['required', Rule::enum(AttendanceStatus::class)],
        ]);

        $subject = Subject::firstOrFail();
        $date = $validated['date'];

        // Ignore any student id that does not exist
        $validIds = Student::whereIn('id', array_keys($validated['statuses']))->pluck('id');

        DB::transaction(function () use ($validated, $subject, $date, $validIds) {
            $existing = Attendance::where('subject_id', $subject->id)
                ->whereDate('date', $date)
                ->get()
                ->keyBy('student_id');

            foreach ($validIds as $studentId) {
                $status = AttendanceStatus::from($validated['statuses'][$studentId]);

                if ($row = $existing->get($studentId)) {
                    $row->update(['status' => $status]);
                } else {
                    Attendance::create([
                        'student_id' => $studentId,
                        'subject_id' => $subject->id,
                        'date'       => $date,
                        'status'     => $status,
                    ]);
                }
            }
        });

        return redirect()
            ->route('instructor.attendance.show', $date)
            ->with('success', 'Attendance for ' . Carbon::parse($date)->format('F j, Y') . ' saved.');
    }

    /** Download the whole class as an Excel file (Summary + one grid per month). */
    public function export()
    {
        $subject = Subject::first();

        $students = Student::orderBy('last_name')->orderBy('first_name')->get()
            ->map(fn ($s) => [
                'id'     => $s->id,
                'name'   => "{$s->last_name}, {$s->first_name}",
                'number' => $s->student_number,
            ])
            ->all();

        $records = Attendance::get(['student_id', 'date', 'status'])
            ->map(fn ($a) => [
                'student_id' => $a->student_id,
                'date'       => $a->date->toDateString(),
                'status'     => $a->status->value,
            ])
            ->all();

        $xlsx = AttendanceExport::forClass(
            $students,
            $records,
            $subject ? "{$subject->code} – {$subject->name}" : ''
        );

        return $this->xlsxResponse($xlsx, 'class-attendance-' . today()->toDateString() . '.xlsx');
    }

    /** One day: who was present / late / absent / excused. */
    public function show(string $date)
    {
        $attendances = Attendance::with('student')
            ->whereDate('date', $date)
            ->get()
            ->sortBy(fn ($a) => $a->student->last_name . ' ' . $a->student->first_name)
            ->values();

        abort_if($attendances->isEmpty(), 404);

        $counts = collect(AttendanceStatus::cases())
            ->mapWithKeys(fn ($s) => [$s->value => $attendances->where('status', $s)->count()]);

        return view('instructor.attendance.show', [
            'attendances' => $attendances,
            'counts'      => $counts,
            'date'        => Carbon::parse($date),
        ]);
    }

    /** Parse ?date=... safely; anything invalid or in the future becomes today. */
    private function resolveDate(?string $value): Carbon
    {
        try {
            $date = $value ? Carbon::createFromFormat('Y-m-d', $value)->startOfDay() : today();
        } catch (\Throwable) {
            return today();
        }

        return $date->gt(today()) ? today() : $date;
    }
}
