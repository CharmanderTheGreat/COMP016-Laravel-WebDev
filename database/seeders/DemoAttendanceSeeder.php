<?php

namespace Database\Seeders;

use App\Enums\AttendanceStatus;
use App\Models\Attendance;
use App\Models\Student;
use App\Models\Subject;
use Illuminate\Database\Seeder;

class DemoAttendanceSeeder extends Seeder
{
    /**
     * DEMO ONLY: fills every registered students with fake weekday attendance
     * for the last ~45 days so the dashboard/history UI has something to show.
     * Run manually:  php artisan db:seed --class=DemoAttendanceSeeder
     */
    public function run(): void
    {
        $subject = Subject::firstOrFail(); // run SubjectSeeder first

        // Weighted pool: mostly present, a few of the others
        $pool = [
            AttendanceStatus::Present, AttendanceStatus::Present, AttendanceStatus::Present,
            AttendanceStatus::Present, AttendanceStatus::Present, AttendanceStatus::Present,
            AttendanceStatus::Late, AttendanceStatus::Absent, AttendanceStatus::Excused,
        ];

        foreach (Student::all() as $student) {
            for ($i = 0; $i < 45; $i++) {
                $date = now()->subDays($i);

                if ($date->isWeekend()) {
                    continue; // no classes on weekends
                }

                Attendance::updateOrCreate(
                    [
                        'student_id' => $student->id,
                        'subject_id' => $subject->id,
                        'date'       => $date->toDateString(),
                    ],
                    ['status' => $pool[array_rand($pool)]]
                );
            }
        }
    }
}
