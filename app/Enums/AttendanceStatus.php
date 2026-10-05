<?php

namespace App\Enums;

/**
 * The four attendance statuses, plus how each one is displayed.
 * Class names are full strings so Tailwind/daisyUI can detect them.
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

    /** Text colour (used in the hero counters). */
    public function textClass(): string
    {
        return match ($this) {
            self::Present => 'text-success',
            self::Late => 'text-warning',
            self::Absent => 'text-error',
            self::Excused => 'text-info',
        };
    }
}