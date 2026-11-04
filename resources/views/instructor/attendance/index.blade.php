<x-layout>
    <x-navbar></x-navbar>


    <div class="max-w-3xl mx-auto p-6">

        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold">Attendance</h1>
                <p class="text-base-content/60">Select a day to see who was present.</p>
            </div>
            <a href="/instructor/attendance/create" class="btn btn-primary">
                Take attendance
            </a>
        </div>

        @if (session('success'))
            <div role="alert" class="alert alert-success mb-4">
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <div class="card bg-base-100 shadow-sm">
            <div class="overflow-x-auto">
                <table class="table table-zebra">
                    <thead>
                    <tr>
                        <th>Date</th>
                        <th class="text-right">Present</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse ($days as $day)
                        <tr class="hover:bg-base-200">
                            <td>
                                <a class="link link-hover font-medium"
                                   href="{{ route('instructor.attendance.show', $day->date->toDateString()) }}">
                                    {{ $day->date->format('l, F j, Y') }}
                                </a>
                            </td>
                            <td class="text-right">
                                <span class="badge badge-neutral">{{ $day->present_count }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="text-center text-base-content/60 py-8">
                                No attendance recorded yet.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-4">
            {{ $days->links() }}
        </div>
    </div>
</x-layout>
