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
     * DEMO ONLY: fills every student with fake weekday attendance for the last ~60 days
     * so the dashboards, history, lists and Excel export have plenty to show.
     * Each student gets their own "habit" (some almost always present, some often late/absent).
     *
     * It only ADDS missing records. Anything already recorded (for example by the
     * instructor) is left untouched, so it is safe to run on the live database.
     *
     * Run manually:  php artisan db:seed --class=DemoAttendanceSeeder
     */
    public function run(): void
    {
        $subject = Subject::firstOrFail(); // run SubjectSeeder first

        foreach (Student::all() as $student) {
            // Weighted pool built per student: [present, late, absent, excused] weights
            $present = random_int(5, 14);
            $pool = array_merge(
                array_fill(0, $present, AttendanceStatus::Present),
                array_fill(0, random_int(0, 3), AttendanceStatus::Late),
                array_fill(0, random_int(0, 3), AttendanceStatus::Absent),
                array_fill(0, random_int(0, 2), AttendanceStatus::Excused),
            );

            // Dates this student already has, so we never overwrite real data
            $have = Attendance::where('student_id', $student->id)
                ->where('subject_id', $subject->id)
                ->get()
                ->map(fn ($a) => $a->date->toDateString())
                ->all();

            for ($i = 0; $i < 60; $i++) {
                $date = today()->subDays($i);

                if ($date->isWeekend()) {
                    continue; // no classes on weekends
                }

                if (in_array($date->toDateString(), $have, true)) {
                    continue;
                }

                Attendance::create([
                    'student_id' => $student->id,
                    'subject_id' => $subject->id,
                    'date'       => $date->toDateString(),
                    'status'     => $pool[array_rand($pool)],
                ]);
            }
        }
    }
}
