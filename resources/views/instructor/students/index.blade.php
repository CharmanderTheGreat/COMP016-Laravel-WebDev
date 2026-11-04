<x-layout>
    <x-navbar></x-navbar>

    <div class="max-w-4xl mx-auto p-6">

        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold">Students</h1>
                <p class="text-base-content/60">All enrolled students.</p>
            </div>
            <div class="stats shadow-sm">
                <div class="stat py-2 px-6">
                    <div class="stat-title">Total</div>
                    <div class="stat-value text-primary">{{ $students->count() }}</div>
                </div>
            </div>
        </div>

        <div class="card bg-base-100 shadow-sm">
            <div class="overflow-x-auto">
                <table class="table table-zebra">
                    <thead>
                    <tr>
                        <th class="w-12">#</th>
                        <th>Student no.</th>
                        <th>Name</th>
                        <th>Program</th>
                        <th class="text-right">Year level</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse ($students as $student)
                        <tr class="hover:bg-base-200">
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $student->student_number }}</td>
                            <td class="font-medium">{{ $student->first_name }} {{ $student->last_name }}</td>
                            <td>{{ $student->course }}</td>
                            <td class="text-right">
                                <span class="badge badge-neutral">{{ $student->year_level }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-base-content/60 py-8">
                                No students found.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layout>
