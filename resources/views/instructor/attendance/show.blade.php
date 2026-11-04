<x-layout>
    <x-navbar></x-navbar>

    <div class="max-w-3xl mx-auto p-6">

        <a href="/instructor/attendance" class="btn btn-ghost btn-sm mb-4">
            &larr; Back to attendance
        </a>

        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold">{{ $date->format('F j, Y') }}</h1>
                <p class="text-base-content/60">{{ $date->format('l') }}</p>
            </div>
            <div class="stats shadow-sm">
                <div class="stat py-2 px-6">
                    <div class="stat-title">Present</div>
                    <div class="stat-value text-primary">{{ $attendances->count() }}</div>
                </div>
            </div>
        </div>

        <div class="card bg-base-100 shadow-sm">
            <div class="overflow-x-auto">
                <table class="table table-zebra">
                    <thead>
                    <tr>
                        <th class="w-12">#</th>
                        <th>Student</th>
                        <th>Student no.</th>
                        <th>Year</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($attendances as $attendance)
                        <tr class="hover:bg-base-200">
                            <td>{{ $loop->iteration }}</td>
                            <td class="font-medium">{{ $attendance->student->user->name }}</td>
                            <td>{{ $attendance->student->student_number }}</td>
                            <td>{{ $attendance->student->year_level }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layout>
