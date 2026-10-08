@props([
    'modal' => null,
    'label' => 'Edit',
    'item' => null
])

<button
    type="button"
    {{ $attributes->merge(['class' => 'admin-edit-btn']) }}
    @if($modal) data-open-modal="{{ $modal }}" @endif
    @if($item) data-edit-item="{{ is_array($item) ? json_encode($item) : $item }}" @endif
    aria-label="{{ $label }}"
>
    <svg class="admin-edit-btn__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path>
        <path d="M15 5l4 4"></path>
    </svg>
</button>
