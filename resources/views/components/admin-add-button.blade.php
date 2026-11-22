@props([
    'modal' => null,
    'label' => 'Tambah'
])

<button
    type="button"
    {{ $attributes->merge(['class' => 'button admin-add-btn']) }}
    @if($modal) data-open-modal="{{ $modal }}" @endif
>
    <span>{{ $slot->isEmpty() ? $label : $slot }}</span>
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        <line x1="12" y1="5" x2="12" y2="19"></line>
        <line x1="5" y1="12" x2="19" y2="12"></line>
    </svg>
</button>
