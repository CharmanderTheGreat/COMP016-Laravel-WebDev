<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /** Seeds the data the app needs to run: the subject and the instructor. */
    public function run(): void
    {
        $this->call([
            SubjectSeeder::class,
            InstructorSeeder::class,
        ]);
    }
}