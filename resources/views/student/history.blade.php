<x-student-layout title="Attendance History" active="attendance">
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

    <div class="flex items-center justify-between mb-4">
        <h1 class="text-2xl font-bold">Attendance History</h1>

        {{-- UI only for now: the real Excel export (one sheet per month) comes later --}}
        <div class="tooltip tooltip-left" data-tip="Coming soon">
            <button class="btn btn-outline btn-sm" disabled>Export to Excel</button>
        </div>
    </div>

    @if ($months->isEmpty())
        <div class="alert">No attendance records yet.</div>
    @else
        {{-- Month tabs (this is what each Excel sheet will mirror later) --}}
        <div role="tablist" class="tabs tabs-box mb-4 flex-wrap">
            @foreach ($months as $key => $label)
                <a role="tab"
                   href="{{ request()->fullUrlWithQuery(['month' => $key]) }}"
                   class="tab {{ $key === $month ? 'tab-active' : '' }}">{{ $label }}</a>
            @endforeach
        </div>

        <div class="overflow-x-auto">
            <table class="table table-zebra">
                <thead>
                    <tr>
                        <th><a href="{{ $link('date') }}" class="link link-hover">Date {{ $arrow('date') }}</a></th>
                        <th><a href="{{ $link('subject') }}" class="link link-hover">Subject {{ $arrow('subject') }}</a></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($records as $record)
                        <tr>
                            <td>{{ $record->date->format('M d, Y') }}</td>
                            <td>{{ $record->subject->code }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</x-student-layout>
