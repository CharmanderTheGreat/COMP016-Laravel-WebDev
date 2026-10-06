<x-student-layout title="Dashboard" active="dashboard">
    {{-- Hero header: welcome + the four attendance counters --}}
    <section class="rounded-box bg-base-200 p-6 mb-6">
        <h1 class="text-3xl font-bold">Welcome, {{ $student->first_name }}</h1>
        <p class="opacity-70">
            {{ $student->class_label }}
            @if ($subject) · {{ $subject->code }} – {{ $subject->name }} @endif
        </p>

        <div class="stats shadow w-full mt-4">
            @foreach (\App\Enums\AttendanceStatus::cases() as $status)
                <div class="stat">
                    <div class="stat-title">{{ $status->label() }}</div>
                    <div class="stat-value {{ $status->textClass() }}">{{ $counts[$status->value] }}</div>
                </div>
            @endforeach
        </div>
    </section>

    {{-- Recent records (full list lives in the Attendance History tab) --}}
    <div class="flex items-center justify-between mb-2">
        <h2 class="text-xl font-semibold">Recent attendance</h2>
        <a href="{{ route('students.attendance') }}" class="link link-hover text-sm">View all</a>
    </div>

    @if ($recent->isEmpty())
        <div class="alert">No attendance records yet.</div>
    @else
        <div class="overflow-x-auto">
            <table class="table table-zebra">
                <thead>
                    <tr><th>Date</th><th>Subject</th><th>Status</th></tr>
                </thead>
                <tbody>
                    @foreach ($recent as $record)
                        <tr>
                            <td>{{ $record->date->format('M d, Y') }}</td>
                            <td>{{ $record->subject->code }}</td>
                            <td>
                                <span class="badge {{ $record->status->badgeClass() }}">
                                    {{ $record->status->label() }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</x-student-layout>
