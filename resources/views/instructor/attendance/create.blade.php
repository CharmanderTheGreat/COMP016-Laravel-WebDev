<x-layout>
    <x-navbar></x-navbar>

    <div class="max-w-3xl mx-auto p-6">
        <h1 class="text-2xl font-bold">Take attendance</h1>
        <p class="text-base-content/60 mb-6">{{ $date->format('F j, Y') }}</p>

        @error('student_ids')
        <div role="alert" class="alert alert-error mb-4"><span>{{ $message }}</span></div>
        @enderror

        <form method="POST" action="/instructor/attendance/create">
            @csrf

            <div class="card bg-base-100 shadow-sm">
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead>
                        <tr><th class="w-12">Present</th><th>Student</th><th>Student no.</th></tr>
                        </thead>
                        <tbody>
                        @foreach ($students as $student)
                            <tr class="hover:bg-base-200">
                                <td>
                                    <input type="checkbox" name="student_ids[]"
                                           value="{{ $student->id }}"
                                           id="student-{{ $student->id }}"
                                           class="checkbox checkbox-primary">
                                </td>
                                <td>
                                    <label for="student-{{ $student->id }}" class="cursor-pointer">
                                        {{ $student->user->name }}
                                    </label>
                                </td>
                                <td>{{ $student->student_number }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="flex justify-end gap-2 mt-4">
                <a href="/instructor/attendance" class="btn btn-ghost">Cancel</a>
                <button class="btn btn-primary">Save attendance</button>
            </div>
        </form>
    </div>
</x-layout>
