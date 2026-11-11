<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 1. Profile pictures move into the database (Railway has no persistent disk).
     * 2. The old `add_role_to_users_table` migration defaulted `role` to "students"
     *    (plural), which is not a valid Role enum value. Fix the default and any bad rows.
     *
     * Only ADDS a table and tweaks a default, so it is safe to run on the live database.
     */
    public function up(): void
    {
        Schema::create('student_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('mime', 50);
            $table->longText('data'); // base64 of a small (max 400x400) JPEG
            $table->timestamps();
        });

        DB::table('users')->where('role', 'students')->update(['role' => 'student']);

        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('student')->change();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_photos');
    }
};
