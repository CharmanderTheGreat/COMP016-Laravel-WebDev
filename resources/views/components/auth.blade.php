{{--
    Shell for the login / sign up pages (not logged in).
    Usage: <x-auth title="Log in" heading="Welcome back" subtitle="..." :wide="false"> ... form ... </x-auth>
--}}
@props(['title' => 'Log in', 'heading' => '', 'subtitle' => null, 'wide' => false])

<x-layout :title="$title">
    <div class="relative min-h-screen overflow-hidden bg-gradient-to-br from-pup-900 to-pup-700">
        {{-- Decorative circles --}}
        <div class="pointer-events-none absolute -left-24 -top-24 h-80 w-80 rounded-full bg-gold-400/20"></div>
        <div class="pointer-events-none absolute -bottom-32 -right-20 h-96 w-96 rounded-full bg-white/5"></div>
        <div class="pointer-events-none absolute right-10 top-24 h-24 w-24 rounded-full border-4 border-gold-400/30"></div>

        <div class="relative mx-auto flex min-h-screen flex-col justify-center px-4 py-10 {{ $wide ? 'max-w-xl' : 'max-w-md' }}">
            <div class="mb-6 text-center text-white">
                <x-logo size="h-20 w-20" class="mx-auto mb-3" />
                <div class="font-display text-3xl font-bold">Attendance System</div>
                <div class="mt-1 text-xs font-medium uppercase tracking-[0.25em] text-gold-300">COMP 016 · Web Development</div>
            </div>

            <div class="rounded-2xl border-t-4 border-gold-400 bg-white p-6 shadow-2xl sm:p-8">
                <h2 class="font-display text-2xl font-bold text-pup-800">{{ $heading }}</h2>
                @if ($subtitle)
                    <p class="mt-1 text-sm text-ink/60">{{ $subtitle }}</p>
                @endif

                <div class="mt-6">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </div>
</x-layout>
