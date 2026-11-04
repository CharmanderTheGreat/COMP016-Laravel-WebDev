<x-layout title="Student Sign Up">
    {{-- enctype is REQUIRED for the profile picture upload --}}
    <form action="/register" method="POST" enctype="multipart/form-data" class="mx-auto max-w-md px-4 pb-10">
        @csrf

        <fieldset class="fieldset bg-base-200 border-base-300 rounded-box border p-4">
            <legend class="fieldset-legend">Student Sign Up</legend>

            {{-- Server-side validation errors --}}
            @if ($errors->any())
                <div class="alert alert-error mb-2">
                    <ul class="list-disc pl-4">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Name --}}
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="label" for="first_name">First name</label>
                    <input id="first_name" name="first_name" type="text" class="input w-full"
                           value="{{ old('first_name') }}" required />
                </div>
                <div>
                    <label class="label" for="last_name">Last name</label>
                    <input id="last_name" name="last_name" type="text" class="input w-full"
                           value="{{ old('last_name') }}" required />
                </div>
            </div>

            {{-- ID number + email --}}
            <label class="label" for="student_number">ID number</label>
            <input id="student_number" name="student_number" type="text" class="input w-full"
                   placeholder="2024-00482-SR-0" value="{{ old('student_number') }}" required />

            <label class="label" for="email">Email</label>
            <input id="email" name="email" type="email" class="input w-full"
                   value="{{ old('email') }}" required />

            {{-- Course / year / section (BSIT 3-2 = BSIT, year 3, section 2) --}}
            <div class="grid grid-cols-3 gap-2">
                <div>
                    <label class="label" for="course">Course</label>
                    <select id="course" name="course" class="select w-full" required>
                        <option value="" disabled @selected(!old('course'))>Course</option>
                        @foreach (\App\Models\Student::COURSES as $c)
                            <option value="{{ $c }}" @selected(old('course') === $c)>{{ $c }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="label" for="year_level">Year</label>
                    <select id="year_level" name="year_level" class="select w-full" required>
                        <option value="" disabled @selected(!old('year_level'))>Year</option>
                        @foreach ([1, 2, 3, 4] as $y)
                            <option value="{{ $y }}" @selected((int) old('year_level') === $y)>{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="label" for="section">Section</label>
                    <select id="section" name="section" class="select w-full" required>
                        <option value="" disabled @selected(!old('section'))>Sec</option>
                        @foreach (range(1, 9) as $s)
                            <option value="{{ $s }}" @selected((int) old('section') === $s)>{{ $s }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Profile picture (required, jpg/png, max 2MB) --}}
            <label class="label" for="profile_photo">Profile picture (JPG/PNG, max 2MB)</label>
            <input id="profile_photo" name="profile_photo" type="file"
                   accept="image/png,image/jpeg" class="file-input w-full" required />

            {{-- Password + live requirements checklist --}}
            <label class="label" for="password">Password</label>
            <input id="password" name="password" type="password" class="input w-full" required />

            <ul id="pw-rules" class="text-xs space-y-1 my-1">
                <li data-rule="length">At least 8 characters</li>
                <li data-rule="upper">One uppercase letter (A-Z)</li>
                <li data-rule="lower">One lowercase letter (a-z)</li>
                <li data-rule="number">One number (0-9)</li>
                <li data-rule="symbol">One special symbol (!@#$...)</li>
                <li data-rule="match">Passwords match</li>
            </ul>

            <label class="label" for="password_confirmation">Confirm password</label>
            <input id="password_confirmation" name="password_confirmation" type="password"
                   class="input w-full" required />

            <button class="btn btn-neutral mt-4">Sign up</button>

            <p class="text-sm text-center mt-2">
                Already have an account? <a href="/login" class="link">Log in</a>
            </p>
        </fieldset>
    </form>

    <script>
        // Live password checklist: turns each rule green once it is satisfied.
        // (The server re-checks everything; this is only for user feedback.)
        const pw = document.getElementById('password');
        const confirmPw = document.getElementById('password_confirmation');

        const rules = {
            length: (v) => v.length >= 8,
            upper:  (v) => /[A-Z]/.test(v),
            lower:  (v) => /[a-z]/.test(v),
            number: (v) => /[0-9]/.test(v),
            symbol: (v) => /[^A-Za-z0-9]/.test(v),
            match:  (v) => v.length > 0 && v === confirmPw.value,
        };

        function updateChecklist() {
            document.querySelectorAll('#pw-rules li').forEach((li) => {
                const ok = rules[li.dataset.rule](pw.value);
                li.classList.toggle('text-success', ok);
                li.textContent = (ok ? '✓ ' : '○ ') + li.textContent.replace(/^[✓○] /, '');
            });
        }

        pw.addEventListener('input', updateChecklist);
        confirmPw.addEventListener('input', updateChecklist);
        updateChecklist(); // set the initial state
    </script>
</x-layout>