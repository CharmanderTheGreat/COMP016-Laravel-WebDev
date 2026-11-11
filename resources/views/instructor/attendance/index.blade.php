<x-app title="Attendance List">
    <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-pup-800">Attendance</h1>
            <p class="text-ink/60">Pick a day to see the full class list.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('instructor.attendance.export', [], false) }}" class="btn btn-outline">Export to Excel</a>
            <a href="{{ route('instructor.attendance.create', [], false) }}" class="btn btn-primary">Record attendance</a>
        </div>
    </div>

    <div class="overflow-x-auto rounded-xl border border-base-300 bg-white shadow-sm">
        <table class="table">
            <thead>
                <tr class="text-ink/60">
                    <th>Date</th>
                    @foreach (\App\Enums\AttendanceStatus::cases() as $status)
                        <th class="text-center">{{ $status->label() }}</th>
                    @endforeach
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($days as $day)
                    <tr class="hover:bg-base-200">
                        <td>
                            <a class="font-medium text-pup-700 hover:underline"
                               href="{{ route('instructor.attendance.show', $day->date->toDateString(), false) }}">
                                {{ $day->date->format('l, F j, Y') }}
                            </a>
                        </td>
                        <td class="text-center"><span class="font-semibold" style="color: {{ \App\Enums\AttendanceStatus::Present->hex() }}">{{ (int) $day->present_count }}</span></td>
                        <td class="text-center"><span class="font-semibold" style="color: {{ \App\Enums\AttendanceStatus::Late->hex() }}">{{ (int) $day->late_count }}</span></td>
                        <td class="text-center"><span class="font-semibold" style="color: {{ \App\Enums\AttendanceStatus::Absent->hex() }}">{{ (int) $day->absent_count }}</span></td>
                        <td class="text-center"><span class="font-semibold" style="color: {{ \App\Enums\AttendanceStatus::Excused->hex() }}">{{ (int) $day->excused_count }}</span></td>
                        <td class="text-right">
                            <a class="btn btn-ghost btn-xs" href="{{ route('instructor.attendance.create', ['date' => $day->date->toDateString()], false) }}">Edit</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="py-12 text-center text-ink/55">
                            No attendance recorded yet.
                            <a href="{{ route('instructor.attendance.create', [], false) }}" class="font-semibold text-pup-700 hover:underline">Record the first one</a>.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $days->links() }}
    </div>
</x-app>
