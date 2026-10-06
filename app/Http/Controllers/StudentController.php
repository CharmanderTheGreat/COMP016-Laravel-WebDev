<?php

namespace App\Http\Controllers;

use App\Enums\AttendanceStatus;
use App\Models\Student;
use App\Models\Subject;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * Student side: dashboard, profile (read-only), attendance history.
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

        $recent = $student->attendances()
            ->with('subject')
            ->orderByDesc('date')
            ->limit(5)
            ->get();

        $subject = Subject::first();

        return view('student.dashboard', compact('student', 'counts', 'recent', 'subject'));
    }

    /** Profile tab (view only for now). */
    public function profile(Request $request)
    {
        $student = $this->currentStudent($request);

        return view('student.profile', compact('student'));
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

        return view('student.history', compact('student', 'records', 'months', 'month', 'sort', 'dir'));
    }

    /** The student profile of whoever is logged in (403 if somehow missing). */
    private function currentStudent(Request $request): Student
    {
        return $request->user()->student ?? abort(403, 'No student profile found.');
    }
}