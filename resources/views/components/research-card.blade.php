@props([
    'id' => null,
    'title',
    'image',
    'status' => 'Sedang Berjalan',
    'author' => null,
    'date' => null,
    'content' => null,
    'excerpt' => null,
    'href' => '#',
    'reverse' => false,
    'badgePosition' => null,
    'supportingImage1' => null,
    'supportingImage2' => null,
])

@php
    $badgePos = $badgePosition ?? ($reverse ? 'left' : 'right');
    $imageUrl = (str_starts_with($image, 'http://') || str_starts_with($image, 'https://'))
        ? $image
        : (str_starts_with($image, 'images/') ? asset($image) : asset('storage/' . ltrim($image, '/')));
@endphp

<article {{ $attributes->class(['card', 'research-card', 'editable-item', 'research-card--reverse' => $reverse]) }}
    data-item-id="{{ $id }}"
    data-item-type="research"
    data-item-title="{{ $title }}"
    data-item-author="{{ $author ?? 'Dr. Eng. Tika Erna Putri, S.Si., M.Sc.' }}"
    data-item-date="{{ $date ?? '' }}"
    data-item-status="{{ $status }}"
    data-item-description="{{ $content ?? $excerpt }}"
    data-item-excerpt="{{ $excerpt }}"
    data-item-supporting-image-1="{{ $supportingImage1 ?? '' }}"
    data-item-supporting-image-2="{{ $supportingImage2 ?? '' }}"
>
    <div class="research-card__media">
        <img src="{{ $imageUrl }}" alt="{{ $title }}" class="research-card__image">
        <div class="research-card__badge research-card__badge--{{ $badgePos }}">
            <x-status-badge :status="$status" />
        </div>
        <x-admin-edit-button modal="edit-research-modal" label="Edit Riset" />
    </div>

    <div class="research-card__content">
        <h3 class="research-card__title">
            <a href="{{ $href }}">{{ $title }}</a>
        </h3>

        @if ($excerpt)
            <p class="research-card__excerpt">
                {{ $excerpt }}
            </p>
        @endif

        <div class="research-card__action">
            <a href="{{ $href }}" class="research-card__link">
                @if (!$reverse)
                    <img src="{{ asset('images/icons/chevron-right.png') }}" alt="" class="research-card__link-icon" aria-hidden="true">
                    <span>Lihat Detail Riset</span>
                @else
                    <span>Lihat Detail Riset</span>
                    <img src="{{ asset('images/icons/chevron-right.png') }}" alt="" class="research-card__link-icon" aria-hidden="true">
                @endif
            </a>
        </div>
    </div>
</article>
