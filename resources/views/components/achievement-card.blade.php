@props([
    'id' => null,
    'title',
    'image',
    'href' => '#',
    'date' => null,
    'rawDate' => null,
    'author' => null,
    'description' => null,
    'content' => null,
    'supportingImage1' => null,
    'supportingImage2' => null,
])

@php
    $imageUrl = (str_starts_with($image, 'http://') || str_starts_with($image, 'https://'))
        ? $image
        : (str_starts_with($image, 'images/') ? asset($image) : asset('storage/' . ltrim($image, '/')));
@endphp

<article {{ $attributes->class(['card', 'achievement-card', 'editable-item', 'achievement-card--has-meta' => !empty($date)]) }}
    data-item-id="{{ $id }}"
    data-item-type="achievement"
    data-item-title="{{ $title }}"
    data-item-author="{{ $author ?? 'Dr. Eng. Tika Erna Putri, S.Si., M.Sc.' }}"
    data-item-date="{{ $rawDate ?? $date ?? '' }}"
    data-item-content="{{ $content ?? $description }}"
    data-item-description="{{ $description ?? $content }}"
    data-item-supporting-image-1="{{ $supportingImage1 ?? '' }}"
    data-item-supporting-image-2="{{ $supportingImage2 ?? '' }}"
>
    <div class="achievement-card__media">
        <a href="{{ $href }}" class="achievement-card__media-link">
            <img src="{{ $imageUrl }}" alt="{{ $title }}" class="achievement-card__image">
        </a>
        @if ($date)
            <div class="achievement-card__badge">{{ $date }}</div>
        @endif
        <x-admin-edit-button modal="edit-achievement-modal" label="Edit Prestasi" />
    </div>

    <a href="{{ $href }}" class="achievement-card__inner">

        <div class="achievement-card__content">
            @if (!$date && !$description)
                <div class="achievement-card__divider" aria-hidden="true"></div>
            @endif
            <div class="achievement-card__body">
                <h3 class="achievement-card__title">{{ $title }}</h3>
                @if ($description)
                    <p class="achievement-card__description">{{ $description }}</p>
                @endif
            </div>
        </div>
    </a>
</article>
