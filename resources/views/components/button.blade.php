@props(['href' => null, 'type' => 'button', 'icon' => null])

@if ($href)
    <a href="{{ $href }}" {{ $attributes->class('button') }}>
        <span>{{ $slot }}</span>
        @if ($icon)
            <img src="{{ asset($icon) }}" alt="" class="button__icon" aria-hidden="true">
        @endif
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->class('button') }}>
        <span>{{ $slot }}</span>
        @if ($icon)
            <img src="{{ asset($icon) }}" alt="" class="button__icon" aria-hidden="true">
        @endif
    </button>
@endif
