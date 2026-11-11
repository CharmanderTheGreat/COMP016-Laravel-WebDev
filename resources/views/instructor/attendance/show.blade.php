<x-app :title="'Attendance ' . $date->format('M j, Y')">
    <a href="{{ route('instructor.attendance.index', [], false) }}" class="mb-4 inline-block text-sm font-medium text-pup-700 hover:underline">&larr; Back to attendance list</a>

    <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-pup-800">{{ $date->format('F j, Y') }}</h1>
            <p class="text-ink/60">{{ $date->format('l') }} · {{ $attendances->count() }} students recorded</p>
        </div>
        <a href="{{ route('instructor.attendance.create', ['date' => $date->toDateString()], false) }}" class="btn btn-primary btn-sm">Edit this day</a>
    </div>

    {{-- Counts --}}
    <div class="mb-6 grid grid-cols-2 gap-4 lg:grid-cols-4">
        @foreach (\App\Enums\AttendanceStatus::cases() as $status)
            <div class="rounded-xl border border-base-300 bg-white p-4 shadow-sm">
                <div class="flex items-center gap-2 text-sm text-ink/60">
                    {{ $status->label() }}
                </div>
                <div class="mt-1 text-3xl font-bold" style="color: {{ $status->hex() }}">{{ $counts[$status->value] }}</div>
            </div>
        @endforeach
    </div>

    <div class="overflow-x-auto rounded-xl border border-base-300 bg-white shadow-sm">
        <table class="table">
            <thead>
                <tr class="text-ink/60">
                    <th class="w-12">#</th>
                    <th>Student</th>
                    <th>ID number</th>
                    <th class="text-right">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($attendances as $attendance)
                    <tr class="hover:bg-base-200">
                        <td class="text-ink/50">{{ $loop->iteration }}</td>
                        <td>
                            <div class="flex items-center gap-3">
                                <x-avatar :student="$attendance->student" size="h-9 w-9" text="text-xs" />
                                <span class="font-medium">{{ $attendance->student->last_name }}, {{ $attendance->student->first_name }}</span>
                            </div>
                        </td>
                        <td class="font-mono text-sm">{{ $attendance->student->student_number }}</td>
                        <td class="text-right"><x-status-badge :status="$attendance->status" /></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-app>
