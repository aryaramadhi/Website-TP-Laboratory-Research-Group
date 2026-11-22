@props(['status' => 'selesai'])

@php
    $statusNormalized = strtolower(str_replace(' ', '-', $status));
@endphp

<span {{ $attributes->class(['badge', 'badge--' . $statusNormalized]) }}>
    {{ $slot->isEmpty() ? $status : $slot }}
</span>
