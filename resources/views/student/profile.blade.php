<x-student-layout title="Profile" active="profile">
    {{-- Read-only profile card. Editing can come after the MVP. --}}
    <div class="card bg-base-200 max-w-xl">
        <div class="card-body">
            <div class="flex items-center gap-4 mb-4">
                {{-- Photo, or initials if there is no picture --}}
                @if ($student->photo_url)
                    <div class="avatar">
                        <div class="w-24 rounded-full">
                            <img src="{{ $student->photo_url }}" alt="Profile picture" />
                        </div>
                    </div>
                @else
                    <div class="avatar avatar-placeholder">
                        <div class="bg-neutral text-neutral-content w-24 rounded-full">
                            <span class="text-3xl">{{ substr($student->first_name, 0, 1) }}{{ substr($student->last_name, 0, 1) }}</span>
                        </div>
                    </div>
                @endif

                <div>
                    <h1 class="text-2xl font-bold">{{ $student->full_name }}</h1>
                    <p class="opacity-70">{{ $student->class_label }}</p>
                </div>
            </div>

            <dl class="grid grid-cols-3 gap-y-2">
                <dt class="opacity-70">ID number</dt>
                <dd class="col-span-2">{{ $student->student_number }}</dd>

                <dt class="opacity-70">Email</dt>
                <dd class="col-span-2">{{ $student->user->email }}</dd>

                <dt class="opacity-70">Course</dt>
                <dd class="col-span-2">{{ $student->course }}</dd>

                <dt class="opacity-70">Year level</dt>
                <dd class="col-span-2">{{ $student->year_level }}</dd>

                <dt class="opacity-70">Section</dt>
                <dd class="col-span-2">{{ $student->section }}</dd>
            </dl>
        </div>
    </div>
</x-student-layout>