{{--
    Shared shell for all students pages: top bar, logout, and the tab menu.
    Usage: <x-students-layout title="Dashboard" active="dashboard"> ... </x-students-layout>
    `active` = dashboard | profile | attendance (highlights the current tab)
--}}
@props(['title' => 'Student', 'active' => 'dashboard'])

<x-layout :title="$title">
    @php($student = auth()->user()->student)

    <div class="max-w-5xl mx-auto px-4 pb-10">
        {{-- Top bar: name + logout --}}
        <div class="navbar bg-base-200 rounded-box mb-4 px-4">
            <div class="flex-1 font-bold">Attendance System</div>
            <div class="flex items-center gap-3">
                <span class="text-sm">{{ $student->full_name }}</span>
                <form action="/logout" method="POST">
                    @csrf
                    <button class="btn btn-sm btn-ghost">Log out</button>
                </form>
            </div>
        </div>

        {{-- Tab menu --}}
        <div role="tablist" class="tabs tabs-border mb-6">
            <a role="tab" href="{{ route('students.dashboard') }}"
               class="tab {{ $active === 'dashboard' ? 'tab-active' : '' }}">Dashboard</a>
            <a role="tab" href="{{ route('students.profile') }}"
               class="tab {{ $active === 'profile' ? 'tab-active' : '' }}">Profile</a>
            <a role="tab" href="{{ route('students.attendance') }}"
               class="tab {{ $active === 'attendance' ? 'tab-active' : '' }}">Attendance History</a>
        </div>

        {{ $slot }}
    </div>
</x-layout>
