@props([
    'publicationsByYear' => collect(),
])

<div {{ $attributes->class(['accordion', 'publication-accordion']) }} data-accordion>
    @forelse ($publicationsByYear as $year => $publications)
        <div class="publication-accordion__item" data-accordion-item data-year="{{ $year }}">
            <button
                type="button"
                class="publication-accordion__header"
                aria-expanded="false"
                data-accordion-trigger
            >
                <img
                    src="{{ asset('images/icons/accordion-chevron.png') }}"
                    alt=""
                    class="publication-accordion__icon"
                    aria-hidden="true"
                >
                <span class="publication-accordion__year">{{ $year }}</span>
            </button>

            <div class="publication-accordion__body" data-accordion-content hidden>
                <div class="publication-accordion__content">
                    <ul class="publication-accordion__list">
                        @foreach ($publications as $pub)
                            <li class="publication-accordion__entry editable-item"
                                data-item-id="{{ $pub->id }}"
                                data-item-type="publication"
                                data-item-title="{{ $pub->title }}"
                                data-item-author="{{ $pub->author }}"
                                data-item-journal="{{ $pub->journal }}"
                                data-item-year="{{ $pub->year }}"
                                data-item-doi="{{ $pub->doi ?? $pub->link }}"
                                data-item-link="{{ $pub->link ?? $pub->doi }}"
                                data-item-description="{{ $pub->description }}"
                            >
                                <x-admin-edit-button modal="edit-publication-modal" label="Edit Publikasi" />
                                <h4 class="publication-accordion__entry-title">
                                    <a
                                        href="{{ $pub->link ?: ($pub->doi ?: '#') }}"
                                        @if ($pub->link || $pub->doi) target="_blank" rel="noopener noreferrer" @endif
                                        class="publication-accordion__entry-link"
                                    >
                                        {{ $pub->title }}
                                    </a>
                                </h4>
                                <p class="publication-accordion__entry-authors">
                                    {{ $pub->author }}
                                </p>
                                <p class="publication-accordion__entry-venue">
                                    {{ $pub->journal }} &bull; {{ $pub->year }}
                                </p>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @empty
        <div class="card-empty-state">
            <p>Belum ada publikasi ilmiah yang dipublikasikan.</p>
        </div>
    @endforelse
</div>
