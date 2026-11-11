<?php

namespace Tests\Feature;

use App\Enums\AttendanceStatus;
use App\Enums\Role;
use App\Models\Attendance;
use App\Models\Student;
use App\Models\StudentPhoto;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DemoAttendanceSeeder;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AttendanceSystemTest extends TestCase
{
    use RefreshDatabase;

    private Subject $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = Subject::create(['code' => 'COMP 016', 'name' => 'Web Development']);
    }

    private function makeStudent(string $number = '2024-00001-SR-0', string $email = 'juan@example.com'): Student
    {
        $user = User::create([
            'name'     => 'Juan Dela Cruz',
            'email'    => $email,
            'password' => 'Student@123',
            'role'     => Role::Student,
        ]);

        return $user->student()->create([
            'first_name'     => 'Juan',
            'last_name'      => 'Dela Cruz',
            'student_number' => $number,
            'course'         => 'BSIT',
            'year_level'     => 3,
            'section'        => 2,
        ]);
    }

    private function makeInstructor(): User
    {
        return User::create([
            'name'     => 'Instructor',
            'email'    => 'instructor@example.com',
            'password' => 'Instructor@123',
            'role'     => Role::Instructor,
        ]);
    }

    // ---------------------------------------------------------------- login / roles

    public function test_home_redirects_to_login(): void
    {
        $this->get('/')->assertRedirect('/login');
    }

    public function test_student_logs_in_with_id_number(): void
    {
        $this->makeStudent();

        // lower-case ID is accepted too (it is normalised to upper-case)
        $this->post('/login', ['login' => '2024-00001-sr-0', 'password' => 'Student@123'])
            ->assertRedirect('/students/dashboard');
    }

    public function test_student_cannot_log_in_with_email(): void
    {
        $this->makeStudent();

        $this->post('/login', ['login' => 'juan@example.com', 'password' => 'Student@123'])
            ->assertSessionHasErrors('login');
    }

    public function test_instructor_logs_in_with_email(): void
    {
        $this->makeInstructor();

        $this->post('/login', ['login' => 'instructor@example.com', 'password' => 'Instructor@123'])
            ->assertRedirect('/instructor/students');
    }

    public function test_roles_cannot_open_each_others_pages(): void
    {
        $student = $this->makeStudent();
        $instructor = $this->makeInstructor();

        $this->actingAs($student->user)->get('/instructor/students')->assertRedirect('/students/dashboard');
        $this->actingAs($instructor)->get('/students/dashboard')->assertRedirect('/instructor/students');
    }

    // ---------------------------------------------------------------- registration + photo

    public function test_registration_stores_the_photo_in_the_database(): void
    {
        $response = $this->post('/register', [
            'first_name'            => 'Ana',
            'last_name'             => 'Reyes',
            'email'                 => 'ana@example.com',
            'student_number'        => '2024-00099-SR-0',
            'course'                => 'BSIT',
            'year_level'            => 3,
            'section'               => 2,
            // A real 1x1 PNG, so the test does not need the GD extension
            'profile_photo'         => UploadedFile::fake()->createWithContent(
                'me.png',
                base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR4nGP4z8DwHwAFAAH/q842iQAAAABJRU5ErkJggg==')
            ),
            'password'              => 'Passw0rd!x',
            'password_confirmation' => 'Passw0rd!x',
        ]);

        $response->assertRedirect('/students/dashboard');

        $student = Student::where('student_number', '2024-00099-SR-0')->firstOrFail();
        $this->assertSame(Student::PHOTO_IN_DB, $student->profile_photo);
        $this->assertNotNull($student->photo);
    }

    public function test_photos_are_private_to_the_owner_and_the_instructor(): void
    {
        $a = $this->makeStudent('2024-00001-SR-0', 'a@example.com');
        $b = $this->makeStudent('2024-00002-SR-0', 'b@example.com');
        StudentPhoto::create(['student_id' => $a->id, 'mime' => 'image/jpeg', 'data' => base64_encode('fake')]);

        $this->actingAs($a->user)->get("/photos/{$a->id}")->assertOk();
        $this->actingAs($b->user)->get("/photos/{$a->id}")->assertForbidden();
        $this->actingAs($this->makeInstructor())->get("/photos/{$a->id}")->assertOk();
    }

    // ---------------------------------------------------------------- recording attendance

    public function test_instructor_records_and_then_edits_a_day_without_duplicates(): void
    {
        $instructor = $this->makeInstructor();
        $one = $this->makeStudent('2024-00001-SR-0', 'one@example.com');
        $two = $this->makeStudent('2024-00002-SR-0', 'two@example.com');
        $date = today()->toDateString();

        $this->actingAs($instructor)->post('/instructor/attendance', [
            'date'     => $date,
            'statuses' => [$one->id => 'present', $two->id => 'absent'],
        ])->assertRedirect(route('instructor.attendance.show', $date));

        $this->assertDatabaseCount('attendances', 2);

        // Edit the same day: Two arrives late
        $this->actingAs($instructor)->post('/instructor/attendance', [
            'date'     => $date,
            'statuses' => [$one->id => 'present', $two->id => 'late'],
        ]);

        $this->assertDatabaseCount('attendances', 2);
        $this->assertSame(
            AttendanceStatus::Late,
            Attendance::where('student_id', $two->id)->firstOrFail()->status
        );
    }

    public function test_future_dates_and_bad_statuses_are_rejected(): void
    {
        $instructor = $this->makeInstructor();
        $student = $this->makeStudent();

        $this->actingAs($instructor)->post('/instructor/attendance', [
            'date'     => today()->addDay()->toDateString(),
            'statuses' => [$student->id => 'present'],
        ])->assertSessionHasErrors('date');

        $this->actingAs($instructor)->post('/instructor/attendance', [
            'date'     => today()->toDateString(),
            'statuses' => [$student->id => 'sleeping'],
        ])->assertSessionHasErrors('statuses.' . $student->id);

        $this->assertDatabaseCount('attendances', 0);
    }

    // ---------------------------------------------------------------- password reset

    public function test_instructor_can_generate_a_temporary_password(): void
    {
        $instructor = $this->makeInstructor();
        $student = $this->makeStudent();

        $response = $this->actingAs($instructor)->post("/instructor/students/{$student->id}/reset-password");

        $response->assertSessionHas('temp_password');
        $temporary = session('temp_password')['password'];

        $this->assertTrue(Hash::check($temporary, $student->user->fresh()->password));
        $this->assertFalse(Hash::check('Student@123', $student->user->fresh()->password));
    }

    // ---------------------------------------------------------------- Excel export

    public function test_student_and_instructor_can_download_excel_files(): void
    {
        if (! class_exists(\ZipArchive::class)) {
            $this->markTestSkipped('The PHP zip extension is not installed.');
        }

        $student = $this->makeStudent();
        Attendance::create([
            'student_id' => $student->id,
            'subject_id' => $this->subject->id,
            'date'       => today()->toDateString(),
            'status'     => AttendanceStatus::Present,
        ]);

        $this->actingAs($student->user)
            ->get('/students/attendance/export')
            ->assertOk()
            ->assertDownload('attendance-2024-00001-SR-0.xlsx');

        $this->actingAs($this->makeInstructor())
            ->get('/instructor/attendance/export')
            ->assertOk()
            ->assertDownload('class-attendance-' . today()->toDateString() . '.xlsx');
    }

    // ---------------------------------------------------------------- instructor profile

    public function test_instructor_can_edit_profile_and_change_password(): void
    {
        $instructor = $this->makeInstructor();
        $this->makeStudent(); // owns juan@example.com

        $this->actingAs($instructor)
            ->put('/instructor/profile', ['name' => 'Prof Cruz', 'email' => 'prof@example.com'])
            ->assertRedirect(route('instructor.profile'));
        $this->assertSame('prof@example.com', $instructor->fresh()->email);

        // an email that belongs to someone else is refused
        $this->actingAs($instructor)
            ->put('/instructor/profile', ['name' => 'Prof Cruz', 'email' => 'juan@example.com'])
            ->assertSessionHasErrors('email');

        // wrong current password
        $this->actingAs($instructor)
            ->put('/instructor/profile/password', [
                'current_password'      => 'not-my-password',
                'password'              => 'NewPass@123',
                'password_confirmation' => 'NewPass@123',
            ])
            ->assertSessionHasErrors('current_password', errorBag: 'password');

        // correct current password
        $this->actingAs($instructor)
            ->put('/instructor/profile/password', [
                'current_password'      => 'Instructor@123',
                'password'              => 'NewPass@123',
                'password_confirmation' => 'NewPass@123',
            ])
            ->assertSessionHasNoErrors();
        $this->assertTrue(Hash::check('NewPass@123', $instructor->fresh()->password));
    }

    // ---------------------------------------------------------------- creating accounts

    public function test_instructor_create_command(): void
    {
        $this->artisan('instructor:create', [
            'email'      => 'prof2@example.com',
            '--name'     => 'Prof Two',
            '--password' => 'Prof@1234x',
        ])->assertSuccessful();

        $this->assertSame(Role::Instructor, User::where('email', 'prof2@example.com')->firstOrFail()->role);

        // it refuses to touch a student's account
        $this->makeStudent();
        $this->artisan('instructor:create', ['email' => 'juan@example.com', '--password' => 'Prof@1234x'])
            ->assertFailed();
    }

    public function test_seeders_create_demo_data_and_can_run_twice(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->assertSame(24, Student::count());

        $this->seed(DemoAttendanceSeeder::class);
        $first = Attendance::count();
        $this->assertGreaterThan(24 * 20, $first);

        // running again must not duplicate or overwrite anything
        $this->seed(DemoAttendanceSeeder::class);
        $this->assertSame($first, Attendance::count());
        $this->assertSame(24, Student::count());
    }

    // ---------------------------------------------------------------- every page renders

    public function test_all_pages_render(): void
    {
        $instructor = $this->makeInstructor();
        $student = $this->makeStudent();

        Attendance::create([
            'student_id' => $student->id,
            'subject_id' => $this->subject->id,
            'date'       => today()->toDateString(),
            'status'     => AttendanceStatus::Late,
        ]);

        $this->get('/login')->assertOk();
        $this->get('/register')->assertOk();

        $this->actingAs($student->user);
        foreach (['/students/dashboard', '/students/attendance', '/students/profile'] as $url) {
            $this->get($url)->assertOk();
        }

        $this->actingAs($instructor);
        foreach ([
            '/instructor/students',
            '/instructor/attendance',
            '/instructor/attendance/create',
            '/instructor/profile',
            '/instructor/attendance/' . today()->toDateString(),
        ] as $url) {
            $this->get($url)->assertOk();
        }
    }
}
