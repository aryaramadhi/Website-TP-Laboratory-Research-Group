@props(['id'])

<div id="{{ $id }}" {{ $attributes->class('modal') }} data-modal hidden aria-hidden="true">
    <div class="modal__backdrop" data-modal-close></div>
    <div class="modal__dialog" role="dialog" aria-modal="true" tabindex="-1">
        {{ $slot }}
    </div>
</div>
