{{--
    Round profile picture, or the person's initials when there is no picture.
    Usage: <x-avatar :student="$student" size="h-10 w-10" text="text-sm" />
           <x-avatar name="Instructor" />   (no student record, e.g. the instructor)
--}}
@props(['student' => null, 'name' => '', 'size' => 'h-10 w-10', 'text' => 'text-sm'])

@php
    $label = $student ? $student->full_name : $name;
    $initials = collect(preg_split('/\s+/', trim($label)))
        ->filter()
        ->map(fn ($part) => mb_substr($part, 0, 1))
        ->take(2)
        ->implode('');
    $url = $student?->photo_url;
@endphp

<div {{ $attributes->class([$size, $text, 'shrink-0 overflow-hidden rounded-full bg-pup-100 font-semibold text-pup-700 ring-2 ring-gold-400 flex items-center justify-center']) }}>
    @if ($url)
        <img src="{{ $url }}" alt="{{ $label }}" class="h-full w-full object-cover" loading="lazy">
    @else
        <span>{{ mb_strtoupper($initials) }}</span>
    @endif
</div>
