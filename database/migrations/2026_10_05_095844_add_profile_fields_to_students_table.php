<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Extend the existing `students` table (already pushed to dev, so we add
     * a NEW migration instead of editing the old one).
     * Course/year/section come from "BSIT 3-2":
     *   course = BSIT, year_level = 3 (existing column), section = 2
     */
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->string('first_name')->after('user_id');
            $table->string('last_name')->after('first_name');
            $table->string('course', 20)->after('student_number');
            $table->unsignedTinyInteger('section')->default(1)->after('year_level');
            // Relative path inside storage/app/public (e.g. profile-photos/abc.jpg)
            $table->string('profile_photo')->nullable()->after('section');
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn(['first_name', 'last_name', 'course', 'section', 'profile_photo']);
        });
    }
};