<x-app title="Students">
    <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-pup-800">Students</h1>
            <p class="text-ink/60">All enrolled students.</p>
        </div>
        <div class="rounded-xl border border-base-300 bg-white px-6 py-2 text-center shadow-sm">
            <div class="text-xs uppercase tracking-wider text-ink/55">Total</div>
            <div class="text-3xl font-bold text-pup-700">{{ $total }}</div>
        </div>
    </div>

    {{-- Temporary password (shown once after a reset) --}}
    @if (session('temp_password'))
        @php($temp = session('temp_password'))
        <div role="alert" class="alert alert-warning alert-soft mb-6 flex-wrap">
            <span>
                New temporary password for <strong>{{ $temp['name'] }}</strong> ({{ $temp['student_number'] }}):
                <code class="mx-1 select-all rounded bg-white px-2 py-1 font-mono text-base font-bold">{{ $temp['password'] }}</code>
                Give it to the student. It is shown only once.
            </span>
        </div>
    @endif

    {{-- Search --}}
    <form method="GET" action="{{ route('instructor.students.index', [], false) }}" class="mb-4 flex gap-2">
        <input type="search" name="q" value="{{ $search }}" placeholder="Search name, ID number or email" class="input w-full max-w-sm" />
        <button class="btn btn-primary">Search</button>
        @if ($search !== '')
            <a href="{{ route('instructor.students.index', [], false) }}" class="btn btn-ghost">Clear</a>
        @endif
    </form>

    <div class="overflow-x-auto rounded-xl border border-base-300 bg-white shadow-sm">
        <table class="table">
            <thead>
                <tr class="text-ink/60">
                    <th>Student</th>
                    <th>ID number</th>
                    <th>Class</th>
                    <th class="hidden md:table-cell">Email</th>
                    <th class="text-right">Password</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($students as $student)
                    <tr class="hover:bg-base-200">
                        <td>
                            <div class="flex items-center gap-3">
                                <x-avatar :student="$student" size="h-10 w-10" />
                                <span class="font-medium">{{ $student->last_name }}, {{ $student->first_name }}</span>
                            </div>
                        </td>
                        <td class="font-mono text-sm">{{ $student->student_number }}</td>
                        <td><span class="badge badge-soft badge-primary">{{ $student->class_label }}</span></td>
                        <td class="hidden text-sm text-ink/70 md:table-cell">{{ $student->user->email }}</td>
                        <td class="text-right">
                            <form method="POST" action="{{ route('instructor.students.reset-password', $student, false) }}"
                                  onsubmit="return confirm('Generate a new temporary password for {{ addslashes($student->full_name) }}? Their old password will stop working.')">
                                @csrf
                                <button class="btn btn-ghost btn-xs text-pup-700">Reset</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-10 text-center text-ink/55">No students found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $students->links() }}
    </div>
</x-app>
