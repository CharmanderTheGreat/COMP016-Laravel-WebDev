{{--
    Shell for every logged-in page (student AND instructor): top bar, nav links, user chip, logout.
    Usage: <x-app title="Dashboard"> ... page content ... </x-app>
--}}
@props(['title' => 'Attendance'])

@php
    $user = auth()->user();
    $isInstructor = $user->role === \App\Enums\Role::Instructor;
    $student = $isInstructor ? null : $user->student;
    $displayName = $student ? $student->full_name : $user->name;

    // Each link: label, URL, and the URL patterns that mark it as the current page
    $links = $isInstructor
        ? [
            ['label' => 'Record Attendance', 'url' => route('instructor.attendance.create', [], false), 'match' => ['instructor/attendance/create']],
            ['label' => 'Attendance List',   'url' => route('instructor.attendance.index', [], false),  'match' => ['instructor/attendance', 'instructor/attendance/2*']],
            ['label' => 'Students',          'url' => route('instructor.students.index', [], false),    'match' => ['instructor/students*']],
            ['label' => 'Profile',           'url' => route('instructor.profile', [], false),           'match' => ['instructor/profile*']],
        ]
        : [
            ['label' => 'Dashboard',          'url' => route('students.dashboard', [], false),  'match' => ['students/dashboard']],
            ['label' => 'Attendance History', 'url' => route('students.attendance', [], false), 'match' => ['students/attendance']],
            ['label' => 'Profile',            'url' => route('students.profile', [], false),    'match' => ['students/profile*']],
        ];
@endphp

<x-layout :title="$title">
    <header class="sticky top-0 z-40 border-b-4 border-gold-400 bg-pup-700 text-white shadow-md">
        <div class="mx-auto flex h-16 max-w-6xl items-center gap-3 px-4">

            {{-- Mobile menu --}}
            <div class="dropdown lg:hidden">
                <div tabindex="0" role="button" class="btn btn-ghost btn-square text-white" aria-label="Open menu">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" /></svg>
                </div>
                <ul tabindex="0" class="menu dropdown-content z-50 mt-3 w-60 rounded-box bg-white p-2 text-ink shadow-xl">
                    @foreach ($links as $link)
                        <li>
                            <a href="{{ $link['url'] }}" @class(['font-semibold bg-pup-50 text-pup-700' => request()->is(...$link['match'])])>{{ $link['label'] }}</a>
                        </li>
                    @endforeach
                </ul>
            </div>

            {{-- Brand --}}
            <a href="{{ $user->role->homePath() }}" class="flex items-center gap-2.5">
                <x-logo size="h-10 w-10" />
                <span class="leading-tight">
                    <span class="block font-display text-lg font-bold">Attendance</span>
                    <span class="block text-[11px] font-medium uppercase tracking-widest text-gold-300">COMP 016</span>
                </span>
            </a>

            {{-- Desktop nav --}}
            <nav class="ml-6 hidden items-center gap-1 lg:flex" aria-label="Main">
                @foreach ($links as $link)
                    <a href="{{ $link['url'] }}"
                       @class([
                           'rounded-lg px-3 py-2 text-sm font-medium transition',
                           'bg-white/15 text-gold-300' => request()->is(...$link['match']),
                           'text-white/85 hover:bg-white/10 hover:text-white' => ! request()->is(...$link['match']),
                       ])>{{ $link['label'] }}</a>
                @endforeach
            </nav>

            {{-- User chip + logout --}}
            <div class="ml-auto flex items-center gap-3">
                <div class="hidden text-right leading-tight sm:block">
                    <div class="text-sm font-semibold">{{ $displayName }}</div>
                    <div class="text-xs text-white/70">{{ $isInstructor ? 'Instructor' : $student?->class_label }}</div>
                </div>
                <x-avatar :student="$student" :name="$displayName" />
                <form method="POST" action="/logout">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-sm border-white/30 bg-transparent text-white hover:bg-white/10">Log out</button>
                </form>
            </div>
        </div>
    </header>

    <main class="mx-auto w-full max-w-6xl px-4 py-8">
        {{-- One-time messages after saving something --}}
        @if (session('success'))
            <div role="alert" class="alert alert-success alert-soft mb-6">
                <span>{{ session('success') }}</span>
            </div>
        @endif

        {{ $slot }}
    </main>

    <footer class="pb-10 text-center text-xs text-ink/50">
        COMP 016 · Web Development · Attendance System
    </footer>
</x-layout>
