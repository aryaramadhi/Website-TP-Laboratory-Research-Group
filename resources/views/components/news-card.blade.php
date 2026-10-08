@props([
    'id' => null,
    'title',
    'image',
    'author' => null,
    'date' => null,
    'category' => null,
    'content' => null,
    'excerpt' => null,
    'href' => '#',
    'reverse' => false,
    'readMore' => false,
    'actionText' => null,
    'supportingImage1' => null,
    'supportingImage2' => null,
])

@php
    $imageUrl = (str_starts_with($image, 'http://') || str_starts_with($image, 'https://'))
        ? $image
        : (str_starts_with($image, 'images/') ? asset($image) : asset('storage/' . ltrim($image, '/')));
@endphp

<article {{ $attributes->class(['card', 'news-card', 'editable-item', 'news-card--reverse' => $reverse]) }}
    data-item-id="{{ $id }}"
    data-item-type="news"
    data-item-title="{{ $title }}"
    data-item-author="{{ $author ?? 'Dr. Eng. Tika Erna Putri, S.Si., M.Sc.' }}"
    data-item-date="{{ $date ?? '' }}"
    data-item-category="{{ $category ?? '' }}"
    data-item-content="{{ $content ?? $excerpt }}"
    data-item-description="{{ $content ?? $excerpt }}"
    data-item-excerpt="{{ $excerpt }}"
    data-item-supporting-image-1="{{ $supportingImage1 ?? '' }}"
    data-item-supporting-image-2="{{ $supportingImage2 ?? '' }}"
>
    <div class="news-card__media">
        <a href="{{ $href }}">
            <img src="{{ $imageUrl }}" alt="{{ $title }}" class="news-card__image">
        </a>
        <x-admin-edit-button modal="edit-news-modal" label="Edit Berita" />
    </div>

    <div class="news-card__content">
        <h3 class="news-card__title">
            <a href="{{ $href }}">{{ $title }}</a>
        </h3>

        @if ($excerpt)
            <p class="news-card__excerpt">
                {{ $excerpt }}
                @if ($readMore)
                    <a href="{{ $href }}" class="news-card__more">Baca Selengkapnya</a>
                @endif
            </p>
        @endif

        @if ($actionText)
            <div class="news-card__action">
                <a href="{{ $href }}" class="news-card__link">
                    @if ($reverse)
                        <span>{{ $actionText }}</span>
                        <img src="{{ asset('images/icons/chevron-right.png') }}" alt="" class="news-card__link-icon" aria-hidden="true">
                    @else
                        <img src="{{ asset('images/icons/chevron-right.png') }}" alt="" class="news-card__link-icon" aria-hidden="true">
                        <span>{{ $actionText }}</span>
                    @endif
                </a>
            </div>
        @endif
    </div>
</article>
