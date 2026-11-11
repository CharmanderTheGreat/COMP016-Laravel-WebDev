<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Student extends Model
{
    /** ID format: 2024-00482-SR-0 (year-5 digits-2 letters-1 digit). */
    public const ID_REGEX = '/^\d{4}-\d{5}-[A-Z]{2}-\d$/';

    /** Courses shown in the sign up dropdown. Edit this list as needed. */
    public const COURSES = ['BSIT', 'BSCS', 'BSIS'];

    /**
     * Value of `profile_photo` meaning "the picture lives in the student_photos table".
     * Older rows hold a file path from the pre-Railway days; those files no longer exist,
     * so they are treated as "no photo" and show initials instead.
     */
    public const PHOTO_IN_DB = 'db';

    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'student_number',
        'course',
        'year_level',
        'section',
        'profile_photo',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function photo(): HasOne
    {
        return $this->hasOne(StudentPhoto::class);
    }

    /** $student->full_name => "Juan Dela Cruz" */
    protected function fullName(): Attribute
    {
        return Attribute::get(fn () => "{$this->first_name} {$this->last_name}");
    }

    /** $student->class_label => "BSIT 3-2" */
    protected function classLabel(): Attribute
    {
        return Attribute::get(
            fn () => "{$this->course} {$this->year_level}-{$this->section}"
        );
    }

    /**
     * $student->photo_url => relative URL of the picture route, or null when there is none.
     * Relative on purpose: it can never turn into a mixed-content http:// link behind Railway's proxy.
     */
    protected function photoUrl(): Attribute
    {
        return Attribute::get(
            fn () => $this->profile_photo === self::PHOTO_IN_DB
                ? route('photos.show', $this, false)
                : null
        );
    }
}
