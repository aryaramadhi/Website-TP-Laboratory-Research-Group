@props([
    'id' => 'publication-modal',
    'title' => null,
    'action' => null,
    'isEdit' => false
])

@php
    $modalTitle = $title ?? ($isEdit ? 'Edit Publikasi' : 'Tambah Publikasi Baru');
@endphp

<x-modal :id="$id" class="admin-modal">
    <div class="modal__header">
        <h2 class="modal__title">{{ $modalTitle }}</h2>
        <button type="button" class="modal__close" aria-label="Tutup" data-modal-close>
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
        </button>
    </div>

    <div class="modal__divider" aria-hidden="true"></div>

    <form class="modal__form admin-form" data-admin-form="publication" action="{{ $isEdit ? '' : '#' }}" method="POST" novalidate>
        @csrf
        @if ($isEdit)
            @method('PUT')
            <input type="hidden" name="publication_id" value="">
        @endif

        <div class="modal__body">
            <div class="form-field-error" id="{{ $id }}-general-error" role="alert" style="display: none; margin-bottom: 16px;">
                <svg class="form-error-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
                <span class="form-error-text"></span>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="{{ $id }}-title" class="form-label">Judul Publikasi</label>
                    <input
                        type="text"
                        id="{{ $id }}-title"
                        name="title"
                        class="form-input"
                        placeholder="Masukkan judul publikasi..."
                        required
                    >
                    <div class="form-field-error" id="{{ $id }}-title-error" role="alert" style="display: none;">
                        <svg class="form-error-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="8" x2="12" y2="12"></line>
                            <line x1="12" y1="16" x2="12.01" y2="16"></line>
                        </svg>
                        <span class="form-error-text"></span>
                    </div>
                </div>
                <div class="form-group">
                    <label for="{{ $id }}-author" class="form-label">Penulis</label>
                    <input
                        type="text"
                        id="{{ $id }}-author"
                        name="author"
                        class="form-input"
                        placeholder="Masukkan nama penulis"
                        required
                    >
                    <div class="form-field-error" id="{{ $id }}-author-error" role="alert" style="display: none;">
                        <svg class="form-error-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="8" x2="12" y2="12"></line>
                            <line x1="12" y1="16" x2="12.01" y2="16"></line>
                        </svg>
                        <span class="form-error-text"></span>
                    </div>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="{{ $id }}-journal" class="form-label">Nama Jurnal</label>
                    <input
                        type="text"
                        id="{{ $id }}-journal"
                        name="journal"
                        class="form-input"
                        placeholder="Masukkan nama jurnal"
                        required
                    >
                    <div class="form-field-error" id="{{ $id }}-journal-error" role="alert" style="display: none;">
                        <svg class="form-error-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="8" x2="12" y2="12"></line>
                            <line x1="12" y1="16" x2="12.01" y2="16"></line>
                        </svg>
                        <span class="form-error-text"></span>
                    </div>
                </div>
                <div class="form-group">
                    <label for="{{ $id }}-year" class="form-label">Tahun</label>
                    <input
                        type="text"
                        id="{{ $id }}-year"
                        name="year"
                        class="form-input"
                        placeholder="cth: 2026"
                        required
                    >
                    <div class="form-field-error" id="{{ $id }}-year-error" role="alert" style="display: none;">
                        <svg class="form-error-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="8" x2="12" y2="12"></line>
                            <line x1="12" y1="16" x2="12.01" y2="16"></line>
                        </svg>
                        <span class="form-error-text"></span>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label for="{{ $id }}-doi" class="form-label">DOI / tautan</label>
                <input
                    type="url"
                    id="{{ $id }}-doi"
                    name="doi"
                    class="form-input"
                    placeholder="https://doi.org/..."
                >
                <div class="form-field-error" id="{{ $id }}-doi-error" role="alert" style="display: none;">
                    <svg class="form-error-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                    <span class="form-error-text"></span>
                </div>
            </div>

            <div class="modal__actions">
                <button type="button" class="btn-modal-cancel" data-modal-close>
                    Batal
                </button>
                @if ($isEdit)
                    <button type="button" class="btn-modal-delete" data-delete-trigger>
                        Hapus
                    </button>
                @endif
                <button type="submit" class="btn-modal-submit">
                    Simpan
                </button>
            </div>
        </div>
    </form>
</x-modal>
