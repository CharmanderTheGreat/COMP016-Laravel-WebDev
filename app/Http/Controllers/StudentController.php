<?php

namespace App\Http\Controllers;

use App\Enums\AttendanceStatus;
use App\Models\Student;
use App\Models\Subject;
use App\Support\AttendanceExport;
use App\Support\ProfilePhoto;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * Student side: dashboard, profile, attendance history.
 * Every method only ever reads the LOGGED-IN student's own data.
 */
class StudentController extends Controller
{
    /** Dashboard: hero counters + the 5 most recent records. */
    public function dashboard(Request $request)
    {
        $student = $this->currentStudent($request);

        // [ 'present' => 12, 'late' => 1, ... ] grouped in one query
        $totals = $student->attendances()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        // Make sure all 4 statuses exist, even when the count is 0
        $counts = collect(AttendanceStatus::cases())
            ->mapWithKeys(fn ($s) => [$s->value => (int) ($totals[$s->value] ?? 0)]);

        // Attendance rate = (present + late) out of everything recorded
        $total = $counts->sum();
        $rate = $total > 0
            ? (int) round((($counts['present'] + $counts['late']) / $total) * 100)
            : null;

        $recent = $student->attendances()
            ->with('subject')
            ->orderByDesc('date')
            ->limit(5)
            ->get();

        $subject = Subject::first();

        return view('student.dashboard', compact('student', 'counts', 'total', 'rate', 'recent', 'subject'));
    }

    /** Profile tab. */
    public function profile(Request $request)
    {
        $student = $this->currentStudent($request);

        return view('student.profile', compact('student'));
    }

    /** Replace the profile picture (also fixes accounts whose old picture was lost). */
    public function updatePhoto(Request $request)
    {
        $student = $this->currentStudent($request);

        $request->validate([
            'profile_photo' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:2048'], // 2MB
        ]);

        ProfilePhoto::store($student, $request->file('profile_photo'));

        return redirect()->route('students.profile')->with('success', 'Profile picture updated.');
    }

    /**
     * Attendance history: one tab per month, sortable by date/subject/status.
     * ?month=2026-10&sort=date&dir=desc
     */
    public function history(Request $request)
    {
        $student = $this->currentStudent($request);

        $all = $student->attendances()->with('subject')->orderByDesc('date')->get();

        // Months that actually have records, newest first: ['2026-10' => 'October 2026']
        $months = $all
            ->map(fn ($r) => $r->date->format('Y-m'))
            ->unique()
            ->mapWithKeys(fn ($m) => [
                $m => Carbon::createFromFormat('Y-m-d', $m . '-01')->format('F Y'),
            ]);

        // Selected month: from the URL if valid, otherwise the newest month
        $requested = (string) $request->query('month');
        $month = $months->has($requested) ? $requested : $months->keys()->first();

        // Whitelist sortable columns so random URL input is ignored
        $sort = in_array($request->query('sort'), ['date', 'subject', 'status'], true)
            ? $request->query('sort')
            : 'date';
        $dir = $request->query('dir') === 'asc' ? 'asc' : 'desc';

        $key = fn ($r) => match ($sort) {
            'subject' => $r->subject->code,
            'status'  => $r->status->value,
            default   => $r->date->timestamp,
        };

        $records = $all->filter(fn ($r) => $r->date->format('Y-m') === $month);
        $records = ($dir === 'asc' ? $records->sortBy($key) : $records->sortByDesc($key))->values();

        // Counts for the selected month (the little summary above the table)
        $monthCounts = collect(AttendanceStatus::cases())
            ->mapWithKeys(fn ($s) => [$s->value => $records->where('status', $s)->count()]);

        return view('student.history', compact('student', 'records', 'months', 'month', 'sort', 'dir', 'monthCounts'));
    }

    /** Download my attendance as an Excel file (a Summary sheet + one sheet per month). */
    public function export(Request $request)
    {
        $student = $this->currentStudent($request);
        $subject = Subject::first();

        $records = $student->attendances()->with('subject')->get()
            ->map(fn ($r) => [
                'date'    => $r->date->toDateString(),
                'subject' => $r->subject->code,
                'status'  => $r->status->value,
            ])
            ->all();

        $xlsx = AttendanceExport::forStudent([
            'name'    => $student->full_name,
            'number'  => $student->student_number,
            'class'   => $student->class_label,
            'subject' => $subject ? "{$subject->code} – {$subject->name}" : '',
        ], $records);

        return $this->xlsxResponse($xlsx, 'attendance-' . $student->student_number . '.xlsx');
    }

    /** The student profile of whoever is logged in (403 if somehow missing). */
    private function currentStudent(Request $request): Student
    {
        return $request->user()->student ?? abort(403, 'No student profile found.');
    }
}
