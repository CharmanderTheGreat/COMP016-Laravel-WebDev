<?php

namespace Database\Seeders;

use App\Models\Subject;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    /** The single subject for the MVP. Change code/name to the real ones. */
    public function run(): void
    {
        Subject::updateOrCreate(
            ['code' => 'COMP 016'],
            ['name' => 'Web Development']
        );
    }
}