{{--
    PUP logo. Put the picture at  public/images/pup-logo.png  (square, transparent PNG, ~256x256 is plenty).
    Until that file exists, a gold check-mark badge is shown instead, so nothing breaks.
    Usage: <x-logo size="h-10 w-10" />
--}}
@props(['size' => 'h-10 w-10'])

@php
    $logoFile = public_path('images/pup-logo.png');
    $hasLogo = file_exists($logoFile);
@endphp

@if ($hasLogo)
    <img src="/images/pup-logo.png?v={{ filemtime($logoFile) }}" alt="PUP logo"
         {{ $attributes->class([$size, 'shrink-0 rounded-full bg-white object-contain p-0.5 shadow ring-2 ring-gold-400']) }}>
@else
    <span {{ $attributes->class([$size, 'grid shrink-0 place-items-center rounded-full bg-gold-400 text-pup-900 shadow']) }}>
        <svg xmlns="http://www.w3.org/2000/svg" class="h-1/2 w-1/2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
    </span>
@endif
