<?php

namespace App\Enums;

enum Role: string
{
    case Student = 'student';
    case Instructor = 'instructor';

    /**
     * Where this role lands after login/register (the "auto-detect role" part).
     */
    public function homePath(): string
    {
        return match ($this) {
            self::Student => '/students/dashboard',
            self::Instructor => '/instructor/students',
        };
    }
}
