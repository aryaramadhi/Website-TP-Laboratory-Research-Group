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
                            @php
                                $author = trim((string) ($pub->penulis ?? $pub->author ?? ''));
                                $yearVal = trim((string) ($pub->tahun ?? $pub->year ?? ''));
                                $title = trim((string) ($pub->judul_karya_hibah ?? $pub->title ?? ''));
                                $journal = trim((string) ($pub->nama_jurnal_skema ?? $pub->journal ?? ''));
                                $rawUrl = trim((string) ($pub->tautan_eksternal ?? ($pub->link ?: ($pub->doi ?? ''))));
                                if ($rawUrl !== '' && preg_match('/[\x00-\x1F\x7F<>"\'`\s]/', $rawUrl)) {
                                    $rawUrl = '';
                                } elseif ($rawUrl !== '' && preg_match('/^10\.\d{4,9}\/[-._;()\/:A-Za-z0-9]+$/', $rawUrl)) {
                                    $rawUrl = 'https://doi.org/' . $rawUrl;
                                }
                                $parsedScheme = strtolower((string) parse_url($rawUrl, PHP_URL_SCHEME));
                                $validUrl = ($rawUrl !== '' && in_array($parsedScheme, ['http', 'https'], true) && filter_var($rawUrl, FILTER_VALIDATE_URL))
                                    ? $rawUrl
                                    : null;
                                $ensurePeriod = static fn (string $val): string => $val !== ''
                                    ? (preg_match('/[.?!]$/', $val) ? $val : $val . '.')
                                    : '';
                            @endphp
                            <li class="publication-accordion__entry editable-item"
                                data-item-id="{{ $pub->publikasi_hibah_id ?? $pub->id }}"
                                data-item-type="publication"
                                data-item-title="{{ $title }}"
                                data-item-author="{{ $author }}"
                                data-item-journal="{{ $journal }}"
                                data-item-year="{{ $yearVal }}"
                                data-item-doi="{{ $pub->tautan_eksternal ?? $pub->doi ?? $pub->link ?? '' }}"
                                data-item-link="{{ $pub->tautan_eksternal ?? $pub->link ?? $pub->doi ?? '' }}"
                                data-item-description="{{ $pub->deskripsi_pencapaian ?? $pub->description ?? '' }}"
                            >
                                <x-admin-edit-button modal="edit-publication-modal" label="Edit Publikasi" />
                                <p class="publication-accordion__entry-citation">@if ($author !== '' && $yearVal !== ''){{ $author }} ({{ $yearVal }}).@elseif ($author !== ''){{ $ensurePeriod($author) }}@elseif ($yearVal !== '')({{ $yearVal }}).@endif @if ($title !== ''){{ $ensurePeriod($title) }} @endif @if ($journal !== ''){{ $ensurePeriod($journal) }} @endif @if ($validUrl)<a href="{{ $validUrl }}" target="_blank" rel="noopener noreferrer" class="publication-accordion__entry-link">{{ $validUrl }}</a>@endif</p>
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
