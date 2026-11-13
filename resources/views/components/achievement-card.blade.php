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
    $isPdf = str_ends_with(strtolower($image ?? ''), '.pdf');
    $imageUrl = ($image && !$isPdf)
        ? ((str_starts_with($image, 'http://') || str_starts_with($image, 'https://'))
            ? $image
            : (str_starts_with($image, 'images/') ? asset($image) : asset('storage/' . ltrim($image, '/'))))
        : asset('images/home/achievement-1.jpg');
    $visibleDescription = $description
        ? trim(preg_replace('/[ \t]{2,}/', ' ', preg_replace('/\s*\[\[(?:RESEARCH|NEWS|ACHIEVEMENT)_IMAGES\]\]\s*/i', ' ', (string) $description)))
        : '';
@endphp

<article {{ $attributes->class(['card', 'achievement-card', 'editable-item', 'achievement-card--has-meta' => !empty($date)]) }}
    data-item-id="{{ $id }}"
    data-item-type="achievement"
    data-item-title="{{ $title }}"
    data-item-author="{{ $author ?? 'Dr. Eng. Tika Erna Putri, S.Si., M.Sc.' }}"
    data-item-date="{{ $rawDate ?? $date ?? '' }}"
    data-item-content="{{ $content ?? $description }}"
    data-item-description="{{ $description ?? $content }}"
    data-item-image="{{ $image ?? '' }}"
    data-item-document="{{ $isPdf ? asset('storage/' . ltrim($image, '/')) : '' }}"
    data-item-supporting-image-1="{{ $supportingImage1 ?? '' }}"
    data-item-supporting-image-2="{{ $supportingImage2 ?? '' }}"
>
    <div class="achievement-card__media">
        <a href="{{ $href }}" class="achievement-card__media-link">
            <img src="{{ $imageUrl }}" alt="{{ $title }}" class="achievement-card__image" onerror="this.onerror=null;this.src='{{ asset('images/home/achievement-1.jpg') }}';">
        </a>
        @if ($date)
            <div class="achievement-card__badge">{{ $date }}</div>
        @endif
        <x-admin-edit-button modal="edit-achievement-modal" label="Edit Prestasi" />
    </div>

    <a href="{{ $href }}" class="achievement-card__inner">

        <div class="achievement-card__content">
            @if (!$date && !$visibleDescription)
                <div class="achievement-card__divider" aria-hidden="true"></div>
            @endif
            <div class="achievement-card__body">
                <h3 class="achievement-card__title">{{ $title }}</h3>
                @if ($visibleDescription)
                    <p class="achievement-card__description">{{ $visibleDescription }}</p>
                @endif
            </div>
        </div>
    </a>
</article>
