# COMP016 Laravel Attendance System

Stack: Laravel 12, PHP 8.2+, MySQL (local database `comp016_laravel`, Railway MySQL in production), Blade, Tailwind (browser CDN build) and daisyUI 5.

Live demo: https://comp016-laravel-webdev-production.up.railway.app

This is a class **demo**: one section, one subject. The instructor records attendance (Present / Late / Absent / Excused) and students see their own records. Both sides can download Excel reports.

**Contents**
1. [Accounts: how instructors and students are created](#1-accounts-how-instructors-and-students-are-created)
2. [Running it locally](#2-running-it-locally)
3. [Deploying on Railway](#3-deploying-on-railway-what-to-know)
4. [What each user sees](#4-what-each-user-sees)
5. [Branding: PUP logo and colours](#5-branding-pup-logo-and-colours)
6. [Excel export](#6-excel-export)
7. [How it works](#7-how-it-works)
8. [Routes](#8-routes)
9. [Database](#9-database)
10. [Project map](#10-project-map)
11. [Hardcoded values](#11-hardcoded-values-to-know-about)
12. [Decisions and limits](#12-decisions-and-limits-demo-scope)
13. [Changelog](#13-changelog)
14. [Checks before pushing](#14-checks-before-pushing)

---

## 1. Accounts: how instructors and students are created

There are two roles, and they are created in **different ways on purpose**:

| Role | How the account is made | Logs in with |
|---|---|---|
| **Student** | Signs up by themselves on the website (`/register`), or is added by the demo seeder | **ID number** (example `2024-00482-SR-0`) |
| **Instructor** | **Never** through the website (otherwise any student could make themselves an instructor). Made by the seeder or by an artisan command | **Email** |

Every account is one row in `users` (name, email, hashed password, `role`). A student also has one row in `students` (ID number, course, year, section, picture).

### 1A. Creating the instructor

**Option 1: the seeder (what `php artisan db:seed` does)**
`database/seeders/InstructorSeeder.php` creates one instructor:

| Email | Password |
|---|---|
| `instructor@example.com` | `Instructor@123` |

To use real details, edit the name / email / password inside that file and run `php artisan db:seed --class=InstructorSeeder`. It only creates the default instructor when **no instructor exists yet**, so it never overwrites a name or password you changed on the Profile page (this matters because Railway runs the seeders on every deploy, see section 3).

**Option 2: the command (recommended for a real instructor)**

```
php artisan instructor:create prof.cruz@school.edu --name="Prof. Juan Cruz"
```

- It asks for the password (typing is hidden). For scripts you can pass `--password=...` instead.
- The password must have at least 8 characters, upper and lower case letters, a number and a symbol. Example: `Prof@2026x`.
- If an instructor with that email already exists, their name and password are updated (handy as a "reset").
- It refuses to use an email that belongs to a **student**, so a student account is never turned into an instructor by accident.
- You can create as many instructors as you want. Note: every instructor sees **all** students and all attendance (there is no "my class" ownership in this demo).

**Option 3: change it from the website (after the first login)**
Log in as the instructor, open **Profile**. There you can change the **name**, the **email** (this is the login), and the **password** (you must type the current password first).

**Running these commands on the Railway database** (the website database is separate from your local one, so a local `db:seed` does not touch it). Pick one:
1. *From your computer (most reliable):* in Railway open the MySQL service and copy the **public** connection details (host, port, database, user, password). Put them temporarily in your local `.env` (`DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`), run the artisan command, then **restore your local `.env`**. Double-check the host before running anything, because commands such as `migrate:fresh` would erase the live data.
2. *Inside the running service:* with the Railway CLI (`railway link`, then `railway ssh`) you get a shell in the deployed app, where you can run `php artisan instructor:create ...`. Railway's tools change from time to time, so check their docs if the command differs.

### 1B. Creating students

**Option 1: the student signs up (normal way)**
1. Open `/register` (the "Sign up" link on the login page).
2. Fill in the form:

| Field | Rule |
|---|---|
| First name, Last name | required, up to 100 characters |
| ID number | format `YYYY-NNNNN-LL-N`, e.g. `2024-00482-SR-0`. Lower case is accepted and converted. Must be unique |
| Email | valid email, must be unique |
| Course | one of `BSIT`, `BSCS`, `BSIS` (list in `Student::COURSES`) |
| Year | 1 to 4 |
| Section | 1 to 9 (`BSIT 3-2` = BSIT, year 3, section 2) |
| Profile picture | required, JPG or PNG, up to 2 MB |
| Password | at least 8 characters, upper and lower case, a number and a symbol; the checklist under the field turns green as you type. Type it again in "Confirm password" |

3. Press **Sign up**. The system creates the user (role `student`, set by the code, never by the form), the student profile and the picture in one step, logs the student in and opens their dashboard.

Common errors: *"ID number must look like 2024-00482-SR-0"* (wrong format), *"The student number has already been taken"* (someone already registered that ID), *"The email has already been taken"*, *"The profile photo must not be greater than 2048 kilobytes"*, and the password rule messages.

**Option 2: demo students from the seeder (many students at once)**
`database/seeders/StudentSeeder.php` creates **24 demo students** (BSIT 3-2) for testing:
- ID numbers `2024-00001-SR-0` up to `2024-00024-SR-0`
- password for all of them: `Student@123`
- emails like `ana.reyes@example.com` (`juan@example.com` and `maria@example.com` for the first two). Students log in with the **ID number**, not the email.

Run it with `php artisan db:seed --class=StudentSeeder`, or `php artisan db:seed` for subject + instructor + students together. It only creates students that are **missing**: a student whose ID number or email already exists is skipped, so passwords reset by the instructor and real students' details are never overwritten. On Railway this happens automatically on every deploy (section 3), so you normally do not need to run it by hand.

To add more demo students, add `['First', 'Last'],` lines at the **end** of the `$names` list in that file (the ID number is made from the position in the list, so adding at the end keeps the existing IDs the same) and run the seeder again.

**Fake attendance for the demo students**
`php artisan db:seed --class=DemoAttendanceSeeder` fills every student with weekday attendance for the last 60 days. Each student gets a different "habit" (some almost always present, some often late or absent), so the Excel report and the dashboards look realistic. It only **adds missing** records and never overwrites real ones, so it is safe on the live database too. Seeded students have no picture, so the initials avatar shows.

**Student forgot the password**
There is no email sending. The instructor opens **Students**, presses **Reset** next to the name, and a temporary password is shown once. Give it to the student. (Students currently cannot change their own password.)

**Starting over locally**
`php artisan migrate:fresh --seed` erases the local database and recreates the subject, the instructor and the 24 demo students. **Never run this against the Railway database.**

---

## 2. Running it locally

1. `composer install`
2. Copy `.env.example` to `.env`, set the MySQL settings (database `comp016_laravel`), then `php artisan key:generate`
3. `php artisan migrate --seed` (subject, instructor, 24 demo students)
4. Optional demo attendance: `php artisan db:seed --class=DemoAttendanceSeeder`
5. `php artisan serve` and open http://127.0.0.1:8000
6. Tests: `php artisan test`

`php artisan storage:link` is **not needed**: profile pictures are stored in the database.

---

## 3. Deploying on Railway (what to know)

- **Migrations and seeding on Railway.** The current Railway builder (FrankenPHP) uses its own start script and **ignores `NIXPACKS_START_CMD`** (the deploy logs show no `migrate` or `db:seed` lines). So set this in the service: **Settings, Deploy, Pre-deploy Command**: `php artisan migrate --force && php artisan db:seed --force`. It runs before every start, with access to the database. The seeders only create what is missing (subject, default instructor, 24 demo students), so running them on every deploy is safe. If you cannot use a pre-deploy command, run the same two commands once from your computer (see section 1).
- `DemoAttendanceSeeder` is **not** part of `DatabaseSeeder` on purpose: it fills fake attendance for every weekday up to today, which would collide with the days the instructor really records. Run it by hand once: `php artisan db:seed --class=DemoAttendanceSeeder --env=production --force` (see section 1).
- Migrations must run on every deploy (see above). Round 2 added one migration (`2026_11_10_100000_...`); it only adds a table and fixes a column default, so it is safe on the live database. Round 3 added **no** migration.
- Railway's disk is wiped on every deploy and there is no Volume, so nothing may be saved as a file. That is why profile pictures live in the database. (The PUP logo is different: it is part of the code, in `public/images/`, so it is redeployed every time.)
- Students who signed up **before** Round 2 lost their picture. They show initials until they upload a new one from **Profile**.
- The CDN versions of Tailwind and daisyUI are pinned in `resources/views/components/layout.blade.php`, and there is no Vite build step, so the deploy does not depend on npm.
- `bootstrap/app.php` trusts Railway's proxy headers, so redirects and links stay `https`.
- Recommended Railway variables: `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://...up.railway.app`. `APP_TIMEZONE` is optional (defaults to `Asia/Manila`).
- The Excel export needs the PHP `zip` extension on the server (almost every PHP image has it).

---

## 4. What each user sees

**Not logged in**
- `/login`: one field "ID number or email", password, remember me.
- `/register`: the student sign up form (see 1B).

**Student** (`/students/...`)
- Dashboard: welcome card with attendance rate (Present + Late out of everything recorded), the four counters, a share bar, the 5 latest records.
- Attendance History: one tab per month, month summary, sortable by date / subject / status, **Export to Excel**.
- Profile: details, plus **change picture**.

**Instructor** (`/instructor/...`)
- Students: searchable list, **Reset** password button.
- Record Attendance: pick a date (today by default, no future dates), set every student to Present / Late / Absent / Excused, "mark everyone" shortcuts, live counters. Opening a day that already has records loads them, and saving **updates** them (no duplicates).
- Attendance List: every recorded day with the four counts, an **Edit** link, and **Export to Excel**.
- Day page: counts and the class list with statuses.
- Profile: change name, email and password.

---

## 5. Branding: PUP logo and colours

**Logo.** Save the picture as `public/images/pup-logo.png` (square, transparent background if possible, about 256 x 256 px is enough). It is used automatically in the top bar, on the login / sign up pages and as the browser-tab icon. Until the file exists, a gold check-mark badge is shown instead. The component is `resources/views/components/logo.blade.php`.

**Colours.** The whole look comes from **two** colours at the top of `resources/views/components/layout.blade.php`:

```
--brand-maroon: #800000;
--brand-gold:   #f2b705;
```

Every other shade (darker or lighter maroon and gold, buttons, header, cards, avatars) is calculated from these two, so changing them recolours everything consistently. To match the logo exactly, pick the maroon and gold from the logo image and replace those two values. Two other places contain the maroon as plain text and are not calculated: the `theme-color` meta tag (same file) and the fallback favicon (same file). The status colours (green, yellow, red, blue) are intentionally separate so statuses are easy to tell apart.

---

## 6. Excel export

Real `.xlsx` files, built by `app/Support/XlsxWriter.php` with **no extra Composer package** (an .xlsx file is a zip of XML files; PHP's built-in `ZipArchive` does the zipping). `app/Support/AttendanceExport.php` decides what goes in the sheets.

- **Student** (`/students/attendance/export`, button on Attendance History): a *Summary* sheet (name, ID, class, counts, attendance rate) and **one sheet per month** with Date, Day, Subject and a coloured Status.
- **Instructor** (`/instructor/attendance/export`, button on Attendance List): a *Summary* sheet (one row per day with the four counts) and **one grid per month**: students down the side, dates across the top, a coloured P / L / A / E in each cell, then Present / Late / Absent / Excused totals and the rate per student.

---

## 7. How it works

**Login** (`SessionsController@store`): if the input matches the student ID format, the matching student's email is looked up and used with the password. Anything else is treated as an email and only accepted for the `instructor` role. Errors are generic. After login the role decides the landing page (`Role::homePath()`).

**Access control**: `EnsureUserHasRole` (alias `role`). A user with the wrong role is redirected to their own home page instead of seeing an error.

**Profile pictures** (`App\Support\ProfilePhoto`, `StudentPhoto`, `PhotoController`): the upload is centre-cropped to a 400 px square JPEG (when the GD extension is available; otherwise it is stored as uploaded), base64-encoded and saved in `student_photos`. `students.profile_photo` holds the marker `db`. The picture is served from `/photos/{student}`, only to its owner and the instructor. Image URLs are relative, so they can never become mixed-content `http://` links.

**Attendance** (`Instructor\AttendanceController`): `store` validates the date (`Y-m-d`, not in the future) and every status (must be an `AttendanceStatus`), then updates the existing row or creates a new one per student, inside a transaction. The unique key `(student_id, subject_id, date)` guarantees one record per student per day.

**Temporary password** (`Instructor\StudentController@resetPassword`): generates a 10-character password, saves it hashed, flashes the plain value once.

**Instructor account** (`Instructor\ProfileController`, `instructor:create` command): see 1A.

---

## 8. Routes

| Method | URL | Name | Who |
|---|---|---|---|
| GET | `/` | | redirects to `/login` |
| GET / POST | `/login` | `login` | guests |
| GET / POST | `/register` | | guests |
| DELETE | `/logout` | | logged in |
| GET | `/photos/{student}` | `photos.show` | owner or instructor |
| GET | `/students/dashboard` | `students.dashboard` | student |
| GET | `/students/profile` | `students.profile` | student |
| POST | `/students/profile/photo` | `students.profile.photo` | student |
| GET | `/students/attendance` | `students.attendance` | student |
| GET | `/students/attendance/export` | `students.attendance.export` | student |
| GET | `/instructor/students` | `instructor.students.index` | instructor |
| POST | `/instructor/students/{student}/reset-password` | `instructor.students.reset-password` | instructor |
| GET | `/instructor/profile` | `instructor.profile` | instructor |
| PUT | `/instructor/profile` | `instructor.profile.update` | instructor |
| PUT | `/instructor/profile/password` | `instructor.profile.password` | instructor |
| GET | `/instructor/attendance` | `instructor.attendance.index` | instructor |
| GET | `/instructor/attendance/create?date=` | `instructor.attendance.create` | instructor |
| GET | `/instructor/attendance/export` | `instructor.attendance.export` | instructor |
| POST | `/instructor/attendance` | `instructor.attendance.store` | instructor |
| GET | `/instructor/attendance/{Y-m-d}` | `instructor.attendance.show` | instructor |

---

## 9. Database

- `users`: `id`, `name`, `email` (unique), `password`, `role` (`student` | `instructor`, default `student`), `remember_token`, timestamps
- `students`: `id`, `user_id` (FK), `first_name`, `last_name`, `student_number` (unique), `course`, `year_level`, `section`, `profile_photo` (`db` or null), timestamps
- `student_photos`: `id`, `student_id` (FK, unique), `mime`, `data` (base64), timestamps
- `subjects`: `id`, `code` (unique), `name`, timestamps
- `attendances`: `id`, `student_id`, `subject_id`, `date`, `status` (`present` | `late` | `absent` | `excused`), timestamps, unique `(student_id, subject_id, date)`
- Laravel's own `sessions`, `cache`, `jobs` tables

---

## 10. Project map

```
app/
  Console/Commands/ CreateInstructor (php artisan instructor:create)
  Enums/            Role, AttendanceStatus (label, badge class, colour)
  Http/Controllers/ SessionsController, StudentController, PhotoController,
                    Auth/UserController (sign up),
                    Instructor/AttendanceController, Instructor/StudentController,
                    Instructor/ProfileController
  Http/Middleware/  EnsureUserHasRole
  Models/           User, Student, StudentPhoto, Subject, Attendance
  Support/          ProfilePhoto (resize + store pictures),
                    XlsxWriter (makes .xlsx), AttendanceExport (what goes in the sheets)
database/seeders/   SubjectSeeder, InstructorSeeder, StudentSeeder (24 students),
                    DemoAttendanceSeeder (fake attendance), DatabaseSeeder
public/images/      pup-logo.png goes here
resources/views/
  components/       layout (head, colours), app (logged-in shell), auth (login/sign up shell),
                    logo, avatar, status-badge
  auth/ student/ instructor/   the pages
  vendor/pagination/daisy.blade.php
tests/Feature/AttendanceSystemTest.php
```

---

## 11. Hardcoded values to know about

| What | Where |
|---|---|
| One subject: the first row of `subjects` is used everywhere | `Subject::first()` in `StudentController` and `AttendanceController@export`, `Subject::firstOrFail()` in `AttendanceController@store` |
| Course list `BSIT, BSCS, BSIS` | `Student::COURSES` |
| ID format `2024-00482-SR-0` | `Student::ID_REGEX` |
| Seeded instructor and demo students | `database/seeders/` |
| Time zone `Asia/Manila` | `config/app.php` (`APP_TIMEZONE`) |
| Brand colours | top of `components/layout.blade.php` |
| Password rules (min 8, mixed case, number, symbol) | `Auth\UserController`, `Instructor\ProfileController`, `CreateInstructor` **and** the checklist script in `auth/register.blade.php` (change all of them together) |

---

## 12. Decisions and limits (demo scope)

- No login throttling, no email verification, students cannot change their own password. Skipped on purpose because this is a demo. The seeded passwords are public (they are in this repo): change them before real use.
- Tailwind and daisyUI come from a CDN (pinned versions). A Vite build would be cleaner and a bit faster, but it adds an npm build to the Railway deploy, so it was left out. Moving to Vite later means: `npm run build` on deploy and replacing the CDN tags in `layout.blade.php`.
- There is no way to delete a day or a student from the UI, and every instructor sees every student.
- The attendance history loads a student's records and filters them in PHP. Fine for one subject; revisit if the data grows.

---

## 13. Changelog

### Round 3 (Nov 11, 2026)
- Removed the coloured dots next to the Present / Late / Absent / Excused counters.
- PUP logo support (`public/images/pup-logo.png`) in the top bar, auth pages and favicon.
- Colour system rebuilt: two brand colours, all other shades derived, gradients use maroon only.
- **Excel export** for students and the instructor (real .xlsx, no new package).
- **Instructor profile**: edit name, email and password.
- `php artisan instructor:create` command.
- 24 demo students (was 2); `DemoAttendanceSeeder` now gives each student a different habit, covers 60 days, and never overwrites existing records.
- Detailed account documentation (section 1).
- Seeders no longer overwrite existing accounts (if `db:seed` runs on every deploy, the old seeders would have reset the instructor password and the demo students).

### Round 2 (Nov 10, 2026): polishing
- Navbar brand "Idea" removed; the brand links to the user's real home page.
- Role column default `students` (invalid) fixed by a migration; bad rows corrected.
- Time zone `Asia/Manila` instead of UTC.
- Profile pictures stored in the database (they no longer vanish on every Railway deploy); students can change their picture.
- Redirects and links stay `https` behind Railway's proxy.
- Instructor can record Present, Late, Absent, Excused, pick any past date, and edit a saved day.
- Attendance List with counts per day; day page with each student's status; student pages show the status column and attendance rate.
- Temporary password reset for students; students list with search and pagination.
- New maroon and gold design, one shared layout for both roles, mobile friendly.
- Cleanup (empty methods, placeholder views, typos, example tests) and the first real tests.

### Round 1 (Oct 5, 2026): student side
Sign up, role-based login, student dashboard, profile, attendance history, and the instructor attendance pages from the teammate's branch.

---

## 14. Checks before pushing

```
php artisan migrate
php artisan test
php artisan route:list
```

Manual smoke test:
1. Sign up a student (with a picture) and log out.
2. Log in as the instructor, record a day with mixed statuses, reopen the same day and change one.
3. Open **Profile** as the instructor, change the name, save.
4. Download the Excel file from Attendance List and open it.
5. Log in as the student: check the dashboard, the history, and download the student Excel file.
6. As the instructor, reset that student's password and log in with the temporary one.
