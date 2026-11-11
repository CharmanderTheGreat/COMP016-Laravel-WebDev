<x-app title="Profile">
    <h1 class="mb-6 text-3xl font-bold text-pup-800">My Profile</h1>

    <div class="grid gap-6 md:grid-cols-3">
        {{-- Picture card --}}
        <div class="rounded-2xl border border-base-300 bg-white p-6 text-center shadow-sm">
            <x-avatar :student="$student" size="mx-auto h-32 w-32" text="text-4xl" />
            <h2 class="mt-4 text-xl font-bold">{{ $student->full_name }}</h2>
            <p class="text-sm text-ink/60">{{ $student->class_label }}</p>

            {{-- Change picture (also fixes accounts whose old picture was lost) --}}
            <form action="{{ route('students.profile.photo', [], false) }}" method="POST" enctype="multipart/form-data" class="mt-5 space-y-2 text-left">
                @csrf
                <label class="block text-xs font-medium text-ink/60" for="profile_photo">
                    {{ $student->photo_url ? 'Change picture' : 'Add a picture' }} (JPG/PNG, max 2MB)
                </label>
                <input id="profile_photo" name="profile_photo" type="file" accept="image/png,image/jpeg" class="file-input file-input-sm w-full" required />
                @error('profile_photo')
                    <p class="text-xs text-error">{{ $message }}</p>
                @enderror
                <button class="btn btn-primary btn-sm w-full">Upload</button>
            </form>
        </div>

        {{-- Details card --}}
        <div class="rounded-2xl border border-base-300 bg-white p-6 shadow-sm md:col-span-2">
            <h2 class="mb-4 text-lg font-bold text-pup-800">Student information</h2>

            <dl class="divide-y divide-base-300">
                @foreach ([
                    'ID number'  => $student->student_number,
                    'Email'      => $student->user->email,
                    'Course'     => $student->course,
                    'Year level' => $student->year_level,
                    'Section'    => $student->section,
                ] as $label => $value)
                    <div class="grid grid-cols-3 gap-4 py-3">
                        <dt class="text-sm text-ink/60">{{ $label }}</dt>
                        <dd class="col-span-2 font-medium">{{ $value }}</dd>
                    </div>
                @endforeach
            </dl>
        </div>
    </div>
</x-app>
