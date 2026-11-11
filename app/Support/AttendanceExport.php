<?php

namespace App\Support;

use App\Enums\AttendanceStatus;
use DateTimeImmutable;

/**
 * Builds the Excel workbooks. Works on plain arrays (no Eloquent) so it is easy to test.
 * Dates are 'Y-m-d' strings, statuses are the AttendanceStatus string values.
 */
class AttendanceExport
{
    /** Cell style for a status. */
    private static function statusStyle(AttendanceStatus $status): int
    {
        return match ($status) {
            AttendanceStatus::Present => XlsxWriter::PRESENT,
            AttendanceStatus::Late => XlsxWriter::LATE,
            AttendanceStatus::Absent => XlsxWriter::ABSENT,
            AttendanceStatus::Excused => XlsxWriter::EXCUSED,
        };
    }

    /** One-letter code used in the instructor's grid. */
    private static function letter(AttendanceStatus $status): string
    {
        return strtoupper($status->value[0]); // P, L, A, E
    }

    /** Attendance rate = (present + late) / everything recorded, as a whole percent. */
    private static function rate(int $present, int $late, int $total): int
    {
        return $total > 0 ? (int) round((($present + $late) / $total) * 100) : 0;
    }

    /**
     * Student workbook: a Summary sheet + one sheet per month (oldest first).
     *
     * @param  array{name: string, number: string, class: string, subject: string}  $student
     * @param  array<int, array{date: string, subject: string, status: string}>  $records
     */
    public static function forStudent(array $student, array $records): XlsxWriter
    {
        usort($records, fn ($a, $b) => strcmp($a['date'], $b['date']));

        $counts = array_fill_keys(array_column(AttendanceStatus::cases(), 'value'), 0);
        foreach ($records as $r) {
            $counts[$r['status']]++;
        }
        $total = array_sum($counts);

        $summary = [
            [['Attendance Report', XlsxWriter::TITLE]],
            [],
            [['Student', XlsxWriter::BOLD], $student['name']],
            [['ID number', XlsxWriter::BOLD], $student['number']],
            [['Class', XlsxWriter::BOLD], $student['class']],
            [['Subject', XlsxWriter::BOLD], $student['subject']],
            [['Generated', XlsxWriter::BOLD], date('F j, Y')],
            [],
            [['Status', XlsxWriter::HEADER], ['Count', XlsxWriter::HEADER]],
        ];
        foreach (AttendanceStatus::cases() as $status) {
            $summary[] = [[$status->label(), self::statusStyle($status)], [$counts[$status->value], XlsxWriter::CENTER]];
        }
        $summary[] = [['Total', XlsxWriter::BOLD], [$total, XlsxWriter::CENTER]];
        $summary[] = [['Attendance rate (Present + Late)', XlsxWriter::BOLD], [self::rate($counts['present'], $counts['late'], $total) . '%', XlsxWriter::CENTER]];

        $xlsx = new XlsxWriter();
        $xlsx->addSheet('Summary', $summary, [34, 28]);

        // One sheet per month
        $byMonth = [];
        foreach ($records as $r) {
            $byMonth[substr($r['date'], 0, 7)][] = $r;
        }
        ksort($byMonth);

        foreach ($byMonth as $month => $rows) {
            $sheet = [[
                ['Date', XlsxWriter::HEADER], ['Day', XlsxWriter::HEADER],
                ['Subject', XlsxWriter::HEADER], ['Status', XlsxWriter::HEADER],
            ]];
            foreach ($rows as $r) {
                $day = new DateTimeImmutable($r['date']);
                $status = AttendanceStatus::from($r['status']);
                $sheet[] = [
                    $day->format('M d, Y'),
                    $day->format('l'),
                    $r['subject'],
                    [$status->label(), self::statusStyle($status)],
                ];
            }
            $xlsx->addSheet((new DateTimeImmutable($month . '-01'))->format('F Y'), $sheet, [16, 14, 14, 12], 1);
        }

        return $xlsx;
    }

    /**
     * Instructor workbook: a Summary sheet (one row per day) + one grid per month
     * (students down, dates across, P / L / A / E in colour, totals at the end).
     *
     * @param  array<int, array{id: int, name: string, number: string}>  $students  already sorted
     * @param  array<int, array{student_id: int, date: string, status: string}>  $records
     */
    public static function forClass(array $students, array $records, string $subject): XlsxWriter
    {
        // [date][student_id] => status   and   [date] => counts
        $byDate = [];
        $dayCounts = [];
        foreach ($records as $r) {
            $byDate[$r['date']][$r['student_id']] = $r['status'];
            $dayCounts[$r['date']][$r['status']] = ($dayCounts[$r['date']][$r['status']] ?? 0) + 1;
        }
        ksort($byDate);

        $summary = [
            [['Class Attendance Report', XlsxWriter::TITLE]],
            [['Subject', XlsxWriter::BOLD], $subject],
            [['Generated', XlsxWriter::BOLD], date('F j, Y')],
            [],
            [['Date', XlsxWriter::HEADER], ['Present', XlsxWriter::HEADER], ['Late', XlsxWriter::HEADER],
                ['Absent', XlsxWriter::HEADER], ['Excused', XlsxWriter::HEADER], ['Total', XlsxWriter::HEADER]],
        ];
        foreach (array_reverse(array_keys($byDate)) as $date) { // newest first
            $c = $dayCounts[$date];
            $summary[] = [
                (new DateTimeImmutable($date))->format('D, M d, Y'),
                [$c['present'] ?? 0, XlsxWriter::CENTER], [$c['late'] ?? 0, XlsxWriter::CENTER],
                [$c['absent'] ?? 0, XlsxWriter::CENTER], [$c['excused'] ?? 0, XlsxWriter::CENTER],
                [array_sum($c), XlsxWriter::CENTER],
            ];
        }

        $xlsx = new XlsxWriter();
        $xlsx->addSheet('Summary', $summary, [20, 10, 10, 10, 10, 10]);

        // One grid per month
        $months = [];
        foreach (array_keys($byDate) as $date) {
            $months[substr($date, 0, 7)][] = $date;
        }
        ksort($months);

        foreach ($months as $month => $dates) {
            $header = [['Student', XlsxWriter::HEADER], ['ID number', XlsxWriter::HEADER]];
            foreach ($dates as $date) {
                $header[] = [(new DateTimeImmutable($date))->format('M j'), XlsxWriter::HEADER];
            }
            array_push($header,
                ['Present', XlsxWriter::HEADER], ['Late', XlsxWriter::HEADER],
                ['Absent', XlsxWriter::HEADER], ['Excused', XlsxWriter::HEADER], ['Rate', XlsxWriter::HEADER]);

            $sheet = [$header];

            foreach ($students as $student) {
                $row = [$student['name'], $student['number']];
                $tally = ['present' => 0, 'late' => 0, 'absent' => 0, 'excused' => 0];

                foreach ($dates as $date) {
                    $value = $byDate[$date][$student['id']] ?? null;
                    if ($value === null) {
                        $row[] = ['', XlsxWriter::CENTER]; // not recorded that day
                        continue;
                    }
                    $status = AttendanceStatus::from($value);
                    $tally[$value]++;
                    $row[] = [self::letter($status), self::statusStyle($status)];
                }

                $recorded = array_sum($tally);
                array_push($row,
                    [$tally['present'], XlsxWriter::CENTER], [$tally['late'], XlsxWriter::CENTER],
                    [$tally['absent'], XlsxWriter::CENTER], [$tally['excused'], XlsxWriter::CENTER],
                    [self::rate($tally['present'], $tally['late'], $recorded) . '%', XlsxWriter::CENTER]);

                $sheet[] = $row;
            }

            // Legend under the grid
            $sheet[] = [];
            $sheet[] = [['P = Present, L = Late, A = Absent, E = Excused. Blank = not recorded.', XlsxWriter::PLAIN]];

            $widths = array_merge([26, 18], array_fill(0, count($dates), 7), [9, 7, 8, 9, 7]);
            $xlsx->addSheet((new DateTimeImmutable($month . '-01'))->format('F Y'), $sheet, $widths, 1);
        }

        return $xlsx;
    }
}
