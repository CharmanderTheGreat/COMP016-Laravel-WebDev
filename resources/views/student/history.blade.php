<x-app title="Attendance History">
    @php
        // Sort link: toggles direction if already sorted by this column.
        // fullUrlWithQuery keeps the selected month in the URL.
        $link = fn ($col) => request()->fullUrlWithQuery([
            'sort' => $col,
            'dir'  => ($sort === $col && $dir === 'asc') ? 'desc' : 'asc',
        ]);

        // Arrow only on the active sort column
        $arrow = fn ($col) => $sort === $col ? ($dir === 'asc' ? '↑' : '↓') : '';
    @endphp

    <div class="mb-6 flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="text-3xl font-bold text-pup-800">Attendance History</h1>
            <p class="text-ink/60">Your records, one tab per month.</p>
        </div>

        {{-- Excel file: a Summary sheet + one sheet per month --}}
        <a href="{{ route('students.attendance.export', [], false) }}" class="btn btn-outline btn-sm @if ($months->isEmpty()) btn-disabled @endif">Export to Excel</a>
    </div>
    </div>

    @if ($months->isEmpty())
        <div class="rounded-xl border border-dashed border-base-300 bg-white p-10 text-center text-ink/55">
            No attendance records yet.
        </div>
    @else
        {{-- Month tabs (this is what each Excel sheet will mirror later) --}}
        <div class="mb-4 flex flex-wrap gap-2">
            @foreach ($months as $key => $label)
                <a href="{{ request()->fullUrlWithQuery(['month' => $key]) }}"
                   @class([
                       'rounded-full px-4 py-1.5 text-sm font-medium transition',
                       'bg-pup-700 text-white shadow' => $key === $month,
                       'border border-base-300 bg-white text-ink/70 hover:border-pup-200 hover:text-pup-700' => $key !== $month,
                   ])>{{ $label }}</a>
            @endforeach
        </div>

        {{-- Summary for the selected month --}}
        <div class="mb-4 flex flex-wrap gap-x-6 gap-y-2 text-sm">
            @foreach (\App\Enums\AttendanceStatus::cases() as $status)
                <span class="flex items-center gap-2">
                    <span class="text-ink/60">{{ $status->label() }}</span>
                    <strong>{{ $monthCounts[$status->value] }}</strong>
                </span>
            @endforeach
        </div>

        <div class="overflow-x-auto rounded-xl border border-base-300 bg-white shadow-sm">
            <table class="table">
                <thead>
                    <tr class="text-ink/60">
                        <th><a href="{{ $link('date') }}" class="hover:text-pup-700">Date {{ $arrow('date') }}</a></th>
                        <th><a href="{{ $link('subject') }}" class="hover:text-pup-700">Subject {{ $arrow('subject') }}</a></th>
                        <th class="text-right"><a href="{{ $link('status') }}" class="hover:text-pup-700">Status {{ $arrow('status') }}</a></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($records as $record)
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
