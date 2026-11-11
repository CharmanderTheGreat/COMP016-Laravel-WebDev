<?php

namespace App\Enums;

/**
 * The four attendance statuses, plus how each one is displayed.
 * Class names are written out in full so Tailwind/daisyUI can detect them.
 */
enum AttendanceStatus: string
{
    case Present = 'present';
    case Late = 'late';
    case Absent = 'absent';
    case Excused = 'excused';

    /** Human-readable label. */
    public function label(): string
    {
        return match ($this) {
            self::Present => 'Present',
            self::Late => 'Late',
            self::Absent => 'Absent',
            self::Excused => 'Excused',
        };
    }

    /** daisyUI badge colour (used in tables). */
    public function badgeClass(): string
    {
        return match ($this) {
            self::Present => 'badge-success',
            self::Late => 'badge-warning',
            self::Absent => 'badge-error',
            self::Excused => 'badge-info',
        };
    }

    /** Solid colour used for dots, numbers and the stacked bar. */
    public function hex(): string
    {
        return match ($this) {
            self::Present => '#2f7d4f',
            self::Late => '#d99f00',
            self::Absent => '#d92d20',
            self::Excused => '#2563a8',
        };
    }
}
