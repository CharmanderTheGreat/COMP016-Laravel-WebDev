<x-app title="Record Attendance">
    <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-pup-800">Record attendance</h1>
            <p class="text-ink/60">{{ $date->format('l, F j, Y') }}</p>
        </div>

        {{-- Pick another day (jumps to that day, loading anything already saved) --}}
        <form method="GET" action="{{ route('instructor.attendance.create', [], false) }}" class="flex items-end gap-2">
            <div>
                <label for="date" class="mb-1 block text-xs font-medium text-ink/60">Date</label>
                <input id="date" type="date" name="date" value="{{ $date->toDateString() }}"
                       max="{{ today()->toDateString() }}" class="input input-sm" onchange="this.form.submit()" />
            </div>
            <button class="btn btn-sm btn-ghost">Go</button>
        </form>
    </div>

    @error('statuses')
        <div role="alert" class="alert alert-error alert-soft mb-4"><span>{{ $message }}</span></div>
    @enderror
    @error('date')
        <div role="alert" class="alert alert-error alert-soft mb-4"><span>{{ $message }}</span></div>
    @enderror

    @if ($existing->isNotEmpty())
        <div role="status" class="alert alert-info alert-soft mb-4">
            <span>Attendance for this day was already recorded. Saving will update it.</span>
        </div>
    @endif

    @if ($students->isEmpty())
        <div class="rounded-xl border border-dashed border-base-300 bg-white p-10 text-center text-ink/55">
            No students have signed up yet.
        </div>
    @else
        <form id="attendance-form" method="POST" action="{{ route('instructor.attendance.store', [], false) }}">
            @csrf
            <input type="hidden" name="date" value="{{ $date->toDateString() }}">

            {{-- Quick actions + live counts --}}
            <div class="mb-3 flex flex-wrap items-center justify-between gap-3">
                <div class="flex flex-wrap items-center gap-2 text-sm">
                    <span class="text-ink/60">Mark everyone:</span>
                    @foreach (\App\Enums\AttendanceStatus::cases() as $status)
                        <button type="button" data-mark-all="{{ $status->value }}" class="btn btn-xs btn-outline">{{ $status->label() }}</button>
                    @endforeach
                </div>
                <div class="flex flex-wrap gap-x-4 gap-y-1 text-sm">
                    @foreach (\App\Enums\AttendanceStatus::cases() as $status)
                        <span class="flex items-center gap-1.5">
                            {{ $status->label() }}
                            <strong id="count-{{ $status->value }}">0</strong>
                        </span>
                    @endforeach
                </div>
            </div>

            {{-- One row per student; stacks on phones so the 4 buttons always fit --}}
            <div class="divide-y divide-base-300 overflow-hidden rounded-xl border border-base-300 bg-white shadow-sm">
                @foreach ($students as $student)
                    @php($current = $existing->get($student->id)?->value ?? 'present')
                    <div class="flex flex-col gap-3 p-4 hover:bg-base-200 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex items-center gap-3">
                            <x-avatar :student="$student" size="h-10 w-10" text="text-xs" />
                            <div>
                                <div class="font-medium">{{ $student->last_name }}, {{ $student->first_name }}</div>
                                <div class="font-mono text-xs text-ink/55">{{ $student->student_number }}</div>
                            </div>
                        </div>

                        <div class="status-pick join w-full sm:w-auto">
                            @foreach (\App\Enums\AttendanceStatus::cases() as $status)
                                <input type="radio" class="join-item btn btn-sm flex-1 sm:flex-none"
                                       name="statuses[{{ $student->id }}]"
                                       value="{{ $status->value }}"
                                       aria-label="{{ $status->label() }}"
                                       @checked($current === $status->value)>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-5 flex justify-end gap-2">
                <a href="{{ route('instructor.attendance.index', [], false) }}" class="btn btn-ghost">Cancel</a>
                <button class="btn btn-primary">Save attendance</button>
            </div>
        </form>

        <script>
            // Live counters + "mark everyone" buttons (the server validates everything again on save)
            const form = document.getElementById('attendance-form');

            function tally() {
                const counts = { present: 0, late: 0, absent: 0, excused: 0 };
                form.querySelectorAll('input[type=radio]:checked').forEach((r) => counts[r.value]++);
                Object.keys(counts).forEach((k) => {
                    document.getElementById('count-' + k).textContent = counts[k];
                });
            }

            form.addEventListener('change', tally);

            document.querySelectorAll('[data-mark-all]').forEach((button) => {
                button.addEventListener('click', () => {
                    form.querySelectorAll('input[type=radio][value="' + button.dataset.markAll + '"]')
                        .forEach((r) => { r.checked = true; });
                    tally();
                });
            });

            tally();
        </script>
    @endif
</x-app>
