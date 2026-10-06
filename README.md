# COMP016 Laravel Attendance System: Student Side Documentation

Branch: `albert` (not merged with `dev`)
Stack: Laravel 12, PHP 8.2, MySQL (`comp016_laravel`), Blade + Tailwind (browser CDN) + daisyUI 5

This document records everything that was added, changed, and deleted while building the student side of the MVP: student sign up, role-based login, student dashboard, read-only profile, and attendance history (UI only for export).

---

## 1. Scope (MVP decisions)

1. One instructor, one section, one subject. The instructor is **seeded** in the database. There is no instructor sign up.
2. Only students sign up.
3. Sign up fields: first name, last name, ID number (format `2024-00482-SR-0`), email, course, year level, section (from "BSIT 3-2"), profile picture, password (with visible requirements).
4. Login uses a single field. Students log in with their **ID number only**. The instructor has no ID, so the instructor logs in with their **email**. A student's email is only shown in the Profile tab and cannot be used to log in.
5. After login the role decides the landing page: student goes to `/student/dashboard`, instructor goes to `/instructor/dashboard`.
6. Student side has three tabs: Dashboard, Profile (read-only), Attendance History (sortable table, one tab per month).
7. The Excel export is **UI only** for now (a disabled button).
8. The instructor side belongs to the teammate. Only a placeholder page exists.

---

## 2. Audit of the original code (problems found)

| # | Problem found in the original `dev` code | How it was resolved |
|---|---|---|
| 1 | The register form had a Role dropdown, so anyone could sign up as `instructor`. | Dropdown removed. `UserController` hardcodes `Role::Student`. |
| 2 | `bootstrap/app.php` redirected guests to `/auth` and users to `/dashboard`, and neither route existed (404). | Guests go to `/login`. Logged-in users go to their role's home page. |
| 3 | Login and register both redirected everyone to the placeholder `/attendance` page. | Redirect now uses `Role::homePath()`. |
| 4 | No logout route. | `POST /logout` added. |
| 5 | Wrong labels in `register.blade.php` (Name field labeled "Email", legend and button said "Login"). | Register form rewritten. |
| 6 | Tailwind CDN script was loaded twice and the page title was "Document". | Layout cleaned up, title is now a prop. |
| 7 | `Student` and `Attendance` models were empty, and there was no `attendances` table. | Models, migrations, and enum created. |
| 8 | `students` table had only `user_id`, `student_number`, `year_level`. | New migration added the rest. |
| 9 | Dashboard used dummy data. | Now reads real data from `attendances`. |
| 10 | Housekeeping: stray empty file `git` in the project root, no `.env.example`, `SETUP_GUIDE.md` still describes SQLite while `.env` uses MySQL. | Stray file deletion was part of the cleanup. `.env.example` and the guide are still open (see section 12). |

---

## 3. Files created (NEW)

### 3.1 Migrations (`database/migrations/`)

The timestamps in the real file names are whatever `php artisan make:migration` generated (`2026_10_05_095844`, `2026_10_05_095924`, `2026_10_05_095926`). The order matters: `create_students_table` (080841) runs first, then `add_profile_fields`, then `subjects`, then `attendances`, because `attendances` has foreign keys to `students` and `subjects`.

1. **`..._add_profile_fields_to_students_table.php`**
   Adds columns to the existing `students` table: `first_name`, `last_name`, `course` (string 20), `section` (unsigned tiny integer, default 1), `profile_photo` (nullable string, stores a path such as `profile-photos/abc.jpg`). `year_level` already existed. A NEW migration was used instead of editing the old one because the old one was already pushed to `dev` and a teammate's database would never pick up edits to a migration that already ran.
2. **`..._create_subjects_table.php`**
   Creates `subjects`: `id`, `code` (unique, e.g. `COMP 016`), `name`, timestamps.
3. **`..._create_attendances_table.php`**
   Creates `attendances`: `id`, `student_id` (FK, cascade on delete), `subject_id` (FK, cascade on delete), `date`, `status` (string), timestamps. Has a **unique key on (`student_id`, `subject_id`, `date`)** so a student can only have one record per subject per day.

### 3.2 Enums (`app/Enums/`)

1. **`AttendanceStatus.php`** (new): cases `Present`, `Late`, `Absent`, `Excused`. Helper methods: `label()` (display text), `badgeClass()` (daisyUI badge color: success, warning, error, info), `textClass()` (text color for the dashboard counters).

### 3.3 Models (`app/Models/`)

1. **`Subject.php`** (new): `fillable` = `code`, `name`. Has many `attendances`.

### 3.4 Middleware (`app/Http/Middleware/`)

1. **`EnsureUserHasRole.php`** (new): used in routes as `role:student` or `role:instructor`. If the logged-in user's role does not match, the user is redirected to **their own** home page (not shown an error). The file must live in `app/Http/Middleware/` (same level as `Controllers`, not inside it) because its namespace is `App\Http\Middleware`.

### 3.5 Seeders (`database/seeders/`)

1. **`SubjectSeeder.php`**: creates the single subject. Uses `updateOrCreate` on `code`, so running it twice does not duplicate.
2. **`InstructorSeeder.php`**: creates the single instructor account. Uses `updateOrCreate` on `email`. The password is plain text in the seeder and gets hashed automatically by the `User` model's `hashed` cast.
3. **`DemoAttendanceSeeder.php`**: **demo data only**. For every registered student it creates weekday attendance records for the last ~45 days with weighted random statuses (mostly present). It is NOT part of `DatabaseSeeder` and must be run manually: `php artisan db:seed --class=DemoAttendanceSeeder`. It requires `SubjectSeeder` to have run.

### 3.3 Views (`resources/views/`)

1. **`components/student-layout.blade.php`**: shared shell for all student pages. Top navbar, tab menu, and a Log out button (a POST form with `@csrf`). Takes props `title` and `active` (`dashboard` | `profile` | `attendance`) to highlight the current tab.
2. **`student/dashboard.blade.php`**: hero header and recent attendance (see section 7).
3. **`student/profile.blade.php`**: read-only profile card.
4. **`student/history.blade.php`**: month tabs, sortable table, disabled export button.
5. **`instructor/dashboard.blade.php`**: placeholder for the teammate to replace entirely. Only a heading and a Log out button.

---

## 4. Files modified (replaced with new full contents)

1. **`app/Enums/Role.php`**: added `homePath()`, which returns `/student/dashboard` for students and `/instructor/dashboard` for instructors. Login, register, middleware, and guest redirect all rely on this one method.
2. **`app/Models/User.php`**: added the `student()` relationship (`hasOne`). `role` is still cast to the `Role` enum and `password` to `hashed`. `role` stays in `$fillable` but is only ever set by our own code (register hardcodes student, seeder sets instructor), never from request input.
3. **`app/Models/Student.php`**: was empty. Now has `ID_REGEX`, `COURSES`, `$fillable`, relationships (`user`, `attendances`), and computed attributes `full_name`, `class_label` (`BSIT 3-2`), and `photo_url` (public URL of the picture or `null`).
4. **`app/Models/Attendance.php`**: was empty. Now has `$fillable`, casts (`date` to Carbon, `status` to `AttendanceStatus`), and relationships to `student` and `subject`.
5. **`bootstrap/app.php`**: registers the `role` middleware alias, `redirectGuestsTo('/login')`, and `redirectUsersTo()` with a closure that returns the user's role home path.
6. **`app/Http/Controllers/Auth/UserController.php`**: student registration (see section 8.1).
7. **`app/Http/Controllers/SessionsController.php`**: login and logout (see section 8.2). `destroy()` is the new logout method.
8. **`app/Http/Controllers/StudentController.php`**: the old `index()` (dummy data) was replaced by `dashboard()`, `profile()`, `history()`, plus a private `currentStudent()` helper that returns the logged-in user's own student record (403 if missing).
9. **`routes/web.php`**: fully replaced (see section 9).
10. **`database/seeders/DatabaseSeeder.php`**: now calls `SubjectSeeder` and `InstructorSeeder`. The default "Test User" factory call was removed.
11. **`resources/views/components/layout.blade.php`**: removed the duplicated Tailwind script, `title` is now a prop (default "Attendance System"), viewport no longer blocks zoom, and `<main>` is full width so each page controls its own max width.
12. **`resources/views/auth/register.blade.php`**: rebuilt as the full student sign up form (see section 7).
13. **`resources/views/auth/login.blade.php`**: single "ID number or email" field with a hint, password, remember me, link to sign up, generic error alert.

---

## 5. Files deleted / removed

1. **`resources/views/student/index.blade.php`**: replaced by `student/dashboard.blade.php`. Nothing references it anymore.
2. **`git`** (empty file in the project root): created by accident, no purpose.
3. **`database/migrations/2026_10_05_075943_add_student_fields_to_users_table.php`**: an earlier local migration that conflicted with the new plan (student fields live in the `students` table, not `users`). Deleted before running the migrations.
4. **Old routes** removed from `routes/web.php`: `GET /attendance` (placeholder) and the old `GET /student/dashboard` that used `StudentController@index`. Also the unused `AuthController` import (that class does not exist).

## 5.1 Files copied from another branch

1. **`database/migrations/2026_10_05_080841_create_students_table.php`** was copied from `dev` with `git checkout dev -- <path>` so the `students` table exists without merging `dev`. The file is identical to `dev`'s version, so a later merge should not conflict on it.

## 5.2 Files intentionally left untouched

`AttendanceController.php`, `resources/views/attendance/index.blade.php`, `resources/views/index.blade.php`, `README.md`, `SETUP_GUIDE.md`, `.env`, `config/*`, `vite.config.js`, `package.json`, `composer.json`. The attendance controller and view are no longer routed. The teammate can reuse them or remove them.

---

## 6. Database schema (final)

**users**: `id`, `name`, `email` (unique), `email_verified_at`, `password`, `role` (string, default `student`), `remember_token`, timestamps.
`name` is stored as "First Last" for compatibility with Laravel defaults.

**students**: `id`, `user_id` (FK to users, cascade), `first_name`, `last_name`, `student_number` (unique), `course`, `year_level`, `section` (default 1), `profile_photo` (nullable), timestamps.

**subjects**: `id`, `code` (unique), `name`, timestamps.

**attendances**: `id`, `student_id` (FK, cascade), `subject_id` (FK, cascade), `date`, `status`, timestamps. Unique (`student_id`, `subject_id`, `date`).

Relationships: User has one Student. Student has many Attendance. Subject has many Attendance. Attendance belongs to Student and Subject.

---

## 7. What the user sees (page by page)

1. **`/login`**: a card titled "Log in". Field "ID number or email" (placeholder `2024-00482-SR-0`) with the hint "Students: use your ID number. Instructor: use your email." Password field, "Remember me" checkbox, "Log in" button, "New student? Sign up" link. A failed login shows one generic red alert: "Invalid ID number / email or password."
2. **`/register`**: card titled "Student Sign Up". Red error list at the top if validation fails (fields keep their values using `old()`). Row 1: First name and Last name. Then ID number, Email. Then three dropdowns: Course, Year (1-4), Section (1-9). Then the profile picture file input (JPG/PNG, max 2MB). Then Password with a **live checklist** of six rules (8 characters, uppercase, lowercase, number, symbol, passwords match). Each rule shows `○` and turns green with `✓` when satisfied. Then Confirm password, "Sign up" button, and a "Log in" link.
3. **Student pages (shared shell)**: top bar with "Attendance System", the student's full name, and a "Log out" button. Under it, tabs: Dashboard, Profile, Attendance History (the current one is highlighted).
4. **`/student/dashboard`**: a hero section with "Welcome, {first name}", the class label (e.g. `BSIT 3-2`) and the subject (`COMP 016 – Web Development`), and four counters: Present (green), Late (yellow), Absent (red), Excused (blue). Below it, "Recent attendance" with the 5 newest records (date, subject, status badge) and a "View all" link. If there are no records, a "No attendance records yet." notice shows instead.
5. **`/student/profile`**: read-only card. Profile picture (or the student's initials in a circle if no picture), full name, class label, and a details list: ID number, Email, Course, Year level, Section. There are no edit controls.
6. **`/student/attendance`**: title "Attendance History" and a **disabled** "Export to Excel" button with a "Coming soon" tooltip. Below it, month tabs (only months that actually have records, newest first, the newest is selected by default). The table has Date, Subject, Status columns. Clicking a header sorts by that column and toggles ascending/descending, with an arrow on the active column. Status is a colored badge. Empty state: "No attendance records yet."
7. **`/instructor/dashboard`**: placeholder heading "Instructor Dashboard (coming soon)" and a Log out button.

---

## 8. How the logic works

### 8.1 Registration (`Auth\UserController@store`)

1. The ID number is trimmed and uppercased first (`2024-00482-sr-0` becomes `2024-00482-SR-0`).
2. Validation: first/last name required (max 100), email valid and unique in `users`, ID number matches `Student::ID_REGEX` and is unique in `students`, course must be in `Student::COURSES`, year 1-4, section 1-9, picture is an image (jpg/jpeg/png, max 2048 KB), password min 8 with mixed case, numbers, symbols, and confirmed.
3. The picture is stored in `storage/app/public/profile-photos` (disk `public`).
4. A database transaction creates the `User` (role **hardcoded** to `Role::Student`) and the linked `Student`. If either fails, neither is saved.
5. The user is logged in, the session is regenerated, and the user is redirected to their dashboard.

### 8.2 Login (`SessionsController@store`)

1. The single `login` field is trimmed.
2. If it matches the student ID format, the matching student's email is looked up and used with the password.
3. Otherwise it is treated as an email, and the credentials include `role = instructor`. This extra condition is what **blocks students from logging in with their email**.
4. On success: the session is regenerated and the user goes to `intended()` or their role's home path.
5. On failure: one generic message, and only the `login` input is kept.

### 8.3 Access control

1. Not logged in and opening a protected page: redirected to `/login`.
2. Logged in and opening `/login` or `/register`: redirected to own dashboard.
3. Logged in with the wrong role (for example, a student opening `/instructor/dashboard`): redirected to their own home page by `EnsureUserHasRole`.
4. Every student page reads only the **logged-in student's own** records (`currentStudent()`), never an ID from the URL.

### 8.4 Dashboard and history

1. Dashboard counts come from one grouped SQL query (`COUNT(*) GROUP BY status`). All four statuses always appear (0 if none).
2. History loads the student's records, builds the list of months that have records, picks the month from `?month=YYYY-MM` (falling back to the newest), then sorts by `?sort=date|subject|status&dir=asc|desc`. Sort columns are **whitelisted** so arbitrary URL input is ignored.

---

## 9. Routes (final)

| Method | URL | Middleware | Handler |
|---|---|---|---|
| GET | `/` | none | redirects to `/login` |
| GET | `/register` | guest | `UserController@create` |
| POST | `/register` | guest | `UserController@store` |
| GET | `/login` (name: `login`) | guest | `SessionsController@create` |
| POST | `/login` | guest | `SessionsController@store` |
| POST | `/logout` | auth | `SessionsController@destroy` |
| GET | `/student/dashboard` | auth, role:student | `StudentController@dashboard` |
| GET | `/student/profile` | auth, role:student | `StudentController@profile` |
| GET | `/student/attendance` | auth, role:student | `StudentController@history` |
| GET | `/instructor/dashboard` | auth, role:instructor | closure returning `instructor.dashboard` view |

---

## 10. Hardcoded values and what to replace

| Value | Where | Replace when |
|---|---|---|
| Role for new sign ups = `student` | `UserController@store` | Never (intentional security rule) |
| Courses `BSIT`, `BSCS`, `BSIS` | `Student::COURSES` | Edit to the real course list |
| ID format `####-#####-XX-#` | `Student::ID_REGEX` | Edit if the real format differs (for example, fixed `SR`) |
| Year 1-4, section 1-9 | `UserController` validation and `register.blade.php` | Edit to the real ranges |
| Picture rules: jpg/jpeg/png, 2048 KB, folder `profile-photos` | `UserController` | Adjust if needed |
| Password rules (min 8, mixed case, number, symbol) | `UserController` and the JS checklist in `register.blade.php` | Change **both** places together |
| Subject `COMP 016` / `Web Development` | `SubjectSeeder` | Put the real subject code and name |
| Instructor `instructor@example.com` / `Instructor@123` / name `Instructor` | `InstructorSeeder` | Put the real prof email, name, and a real password. **Do not keep the default password.** |
| Home paths `/student/dashboard`, `/instructor/dashboard` | `Role::homePath()` | Only if routes are renamed |
| Recent attendance limit = 5 | `StudentController@dashboard` | Optional |
| Four statuses and their colors | `AttendanceStatus` | If statuses change |
| Demo data: 45 days, weekdays only, weighted statuses | `DemoAttendanceSeeder` | Never run on production data |
| Tailwind browser CDN + daisyUI 5 CDN | `layout.blade.php` | Move to Vite build before a production deploy |
| Brand text "Attendance System" | `student-layout.blade.php` and layout default title | Branding |
| "Export to Excel" disabled button | `student/history.blade.php` | When the export is implemented |
| Instructor dashboard placeholder | `instructor/dashboard.blade.php` | Teammate replaces the whole file |

---

## 11. Security notes

1. Self-registration as instructor is impossible: the role is not read from the request.
2. Student emails cannot be used to log in.
3. Passwords are hashed through the `User` model cast.
4. `session()->regenerate()` on login prevents session fixation. Logout invalidates the session and regenerates the token.
5. All forms include `@csrf`. Logout is POST-only.
6. Login errors are generic and do not reveal whether the ID or the password was wrong.
7. Sorting is whitelisted. Students can only read their own data.
8. `.env` is in `.gitignore`. Never share it or a zip containing it (it holds the DB password).

---

## 12. Known issues and TODO

1. **Excel export** is UI only. A real `.xlsx` with one sheet per month will need a package (such as `maatwebsite/excel`, which requires `ext-zip` and `ext-gd`), and both developers must run `composer install` afterwards.
2. **Instructor side** (dashboard, recording attendance into `attendances`) belongs to the teammate. Until then, attendance exists only through `DemoAttendanceSeeder`.
3. **`tests/Feature/ExampleTest.php`** asserts that `GET /` returns 200, but `/` now redirects to `/login`, so `php artisan test` fails on that test. Fix: change `$response->assertStatus(200);` to `$response->assertRedirect('/login');`.
4. **`.env.example` is missing** and `SETUP_GUIDE.md` still says SQLite, but the project uses MySQL. Both should be updated for new clones.
5. `repomix-output.xml` is tracked in the repo and can be removed.
6. Profile editing, password change, and photo replacement are not built (profile is read-only by design for the MVP).
7. `php artisan storage:link` must be run once per machine, otherwise profile pictures do not display.
8. Accounts created before this change (with the old register form) have no `students` row. Opening the student dashboard for them returns 403. Use `php artisan migrate:fresh --seed` locally.

---

## 13. Setup for a teammate pulling this work

1. `composer install`
2. Make sure `.env` has the MySQL settings (database `comp016_laravel`).
3. `php artisan migrate` (or `php artisan migrate:fresh --seed` on a local database)
4. `php artisan db:seed` (safe to repeat, both seeders use `updateOrCreate`)
5. `php artisan storage:link`
6. `php artisan serve`, then open `http://127.0.0.1:8000`
7. Optional demo data: register a student, then `php artisan db:seed --class=DemoAttendanceSeeder`

---

## 14. Troubleshooting log (errors hit during setup)

1. **`Class "App\Models\Student" not found`**: the model file was not in place yet. Fixed by adding the model files.
2. **`Table 'comp016_laravel.students' doesn't exist`**: the `create_students_table` migration only existed on `dev`. Fixed with `git checkout dev -- database/migrations/2026_10_05_080841_create_students_table.php`, then `php artisan migrate:fresh --seed`.
3. **`Target class [App\Http\Middleware\EnsureUserHasRole] does not exist`**: the middleware file was created in `app/Http/Controllers/Middleware/`. Moved to `app/Http/Middleware/`, then `composer dump-autoload`.
4. **`Cannot declare class App\Models\Student, because the name is already in use`**: the `Student` code had been pasted into `Attendance.php`. Fixed by putting the correct `Attendance` code in that file. Rule of thumb: the class name inside a file must match the file name.
5. **`include(.../app/Models/User.php): Failed to open stream: No such file or directory`**: `git status` showed ` D app/Models/User.php`, so the file had been deleted from the working folder. Fixed by recreating the file with the new contents. `git restore` was deliberately NOT used because it would have brought back the OLD `User.php` without the `student()` relationship. Lesson: commit work-in-progress often (`git add .` then `git commit -m "wip"`) so a deleted file can always be restored.

---

## 15. Verification commands (Windows CMD, from the project root)

1. Syntax check every PHP file. Nothing printed means no errors:
   ```
   for /r app %f in (*.php) do @php -l "%f" | findstr /v /c:"No syntax errors"
   for /r database %f in (*.php) do @php -l "%f" | findstr /v /c:"No syntax errors"
   for /r routes %f in (*.php) do @php -l "%f" | findstr /v /c:"No syntax errors"
   php -l bootstrap\app.php
   ```
2. Compile every Blade view (catches Blade syntax errors), then clear:
   ```
   php artisan view:cache
   php artisan view:clear
   ```
3. List routes and compare with section 9. Expect **13 routes**: the 10 rows from section 9 plus 3 routes that Laravel adds itself (`storage/{path}` GET and PUT, and `up`):
   ```
   php artisan route:list
   ```
4. All 8 migrations should be `Ran`:
   ```
   php artisan migrate:status
   ```
5. Check that every class loads from the right file:
   ```
   php artisan tinker --execute="foreach ([App\Models\User::class, App\Models\Student::class, App\Models\Subject::class, App\Models\Attendance::class, App\Enums\Role::class, App\Enums\AttendanceStatus::class, App\Http\Middleware\EnsureUserHasRole::class, App\Http\Controllers\StudentController::class, App\Http\Controllers\SessionsController::class, App\Http\Controllers\Auth\UserController::class] as $c) { echo $c . ' => ' . (class_exists($c) ? 'OK' : 'MISSING') . PHP_EOL; }"
   ```
6. Check that each file declares the class that matches its name:
   ```
   findstr /n /b /c:"class " /c:"enum " app\Models\*.php app\Enums\*.php app\Http\Middleware\*.php
   ```
7. Check that deleted files are really gone:
   ```
   if exist resources\views\student\index.blade.php (echo STILL THERE) else (echo OK)
   if exist git (echo STILL THERE) else (echo OK)
   dir app\Http\Controllers
   ```
   `dir app\Http\Controllers` must not list a `Middleware` folder.
8. Check seeded data:
   ```
   php artisan tinker --execute="echo 'subjects=' . App\Models\Subject::count() . ' instructors=' . App\Models\User::where('role','instructor')->count() . ' students=' . App\Models\Student::count() . ' attendances=' . App\Models\Attendance::count();"
   ```
9. Manual smoke test:
   1. Register a student and confirm the live password checklist and redirect to the dashboard.
   2. Log out, log in with the **ID number** (should work), then try the **email** (should fail).
   3. Open Profile and confirm the email and picture show.
   4. Run `DemoAttendanceSeeder`, then check the counters, the month tabs, and header sorting.
   5. Log in as the instructor with the seeded email and confirm the placeholder page. While logged in as the instructor, open `/student/dashboard` and confirm you are sent back to `/instructor/dashboard`.

---

## 16. Verification results (October 5, 2026)

Results of the commands in section 15, as run on the `albert` branch:

1. `php -l` on `app`, `database`, `routes`, and `bootstrap\app.php`: no syntax errors.
2. `php artisan view:cache`: all Blade templates compiled successfully.
3. `php artisan route:list`: 13 routes, matching section 9 plus the 3 framework routes.
4. `php artisan migrate:status`: all 8 migrations `Ran`.
5. `composer dump-autoload`: the PSR-4 warning about `Student` inside `Attendance.php` disappeared after the file was corrected.
6. Contents check: `SessionsController` contains the `Role::Instructor` condition, `login.blade.php` contains the ID placeholder, and `student/index.blade.php` and the stray `git` file are gone.
7. Still to confirm in the browser: student login by ID works and by email fails, instructor login lands on the placeholder page, and a logged-in instructor opening `/student/dashboard` is redirected back to `/instructor/dashboard`.
