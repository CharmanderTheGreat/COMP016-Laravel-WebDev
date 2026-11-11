<x-app title="Dashboard">
    {{-- Hero: welcome + attendance rate --}}
    <section class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-pup-900 to-pup-700 p-6 text-white shadow-lg sm:p-8">
        <div class="pointer-events-none absolute -right-10 -top-10 h-44 w-44 rounded-full bg-gold-400/20"></div>

        <div class="relative flex flex-wrap items-center gap-5">
            <x-avatar :student="$student" size="h-20 w-20" text="text-2xl" />
            <div>
                <p class="text-sm font-medium text-gold-300">Welcome back</p>
                <h1 class="text-3xl font-bold sm:text-4xl">{{ $student->first_name }}</h1>
                <p class="mt-1 text-sm text-white/75">
                    {{ $student->class_label }}
                    @if ($subject) · {{ $subject->code }} – {{ $subject->name }} @endif
                </p>
            </div>

            <div class="ml-auto text-right">
                <div class="text-5xl font-bold text-gold-300">{{ $rate === null ? '–' : $rate . '%' }}</div>
                <div class="text-xs uppercase tracking-wider text-white/70">Attendance rate</div>
                <div class="text-[11px] text-white/50">Present + Late</div>
            </div>
        </div>
    </section>

    {{-- The four counters --}}
    <section class="mt-6 grid grid-cols-2 gap-4 lg:grid-cols-4">
        @foreach (\App\Enums\AttendanceStatus::cases() as $status)
            <div class="rounded-xl border border-base-300 bg-white p-4 shadow-sm">
                <div class="flex items-center gap-2 text-sm text-ink/60">
                    {{ $status->label() }}
                </div>
                <div class="mt-1 text-4xl font-bold" style="color: {{ $status->hex() }}">{{ $counts[$status->value] }}</div>
            </div>
        @endforeach
    </section>

    {{-- Stacked bar: share of each status --}}
    @if ($total > 0)
        <div class="mt-4 flex h-3 overflow-hidden rounded-full bg-base-300" role="img" aria-label="Share of each attendance status">
            @foreach (\App\Enums\AttendanceStatus::cases() as $status)
                @if ($counts[$status->value] > 0)
                    <div style="width: {{ ($counts[$status->value] / $total) * 100 }}%; background: {{ $status->hex() }}" title="{{ $status->label() }}: {{ $counts[$status->value] }}"></div>
                @endif
            @endforeach
        </div>
    @endif

    {{-- Recent records (full list lives in Attendance History) --}}
    <div class="mb-3 mt-10 flex items-end justify-between">
        <h2 class="text-2xl font-bold text-pup-800">Recent attendance</h2>
        <a href="{{ route('students.attendance', [], false) }}" class="text-sm font-semibold text-pup-700 hover:underline">View all &rarr;</a>
    </div>

    @if ($recent->isEmpty())
        <div class="rounded-xl border border-dashed border-base-300 bg-white p-10 text-center text-ink/55">
            No attendance records yet. They will show up here once your instructor records a class.
        </div>
    @else
        <div class="overflow-x-auto rounded-xl border border-base-300 bg-white shadow-sm">
            <table class="table">
                <thead>
                    <tr class="text-ink/60"><th>Date</th><th>Subject</th><th class="text-right">Status</th></tr>
                </thead>
                <tbody>
                    @foreach ($recent as $record)
                        <tr>
                            <td class="font-medium">{{ $record->date->format('D, M d, Y') }}</td>
                            <td>{{ $record->subject->code }}</td>
                            <td class="text-right"><x-status-badge :status="$record->status" /></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</x-app>
