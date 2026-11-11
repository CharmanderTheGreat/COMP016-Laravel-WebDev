{{-- Coloured pill for an attendance status. Usage: <x-status-badge :status="$record->status" /> --}}
@props(['status'])

<span {{ $attributes->class(['badge badge-soft font-medium', $status->badgeClass()]) }}>{{ $status->label() }}</span>
