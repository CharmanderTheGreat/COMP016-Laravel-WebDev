<?php

namespace App\Enums;

enum Role: string
{
    case Student = "student";
    case Instructor = "instructor";

    /**
     * Where this role lands after login/register.
     * This is the "auto-detect role" part: login just redirects here.
     */
    public function homePath(): string
    {
        return match ($this) {
            self::Student => '/student/dashboard',
            self::Instructor => '/instructor/dashboard',
        };
    }
}