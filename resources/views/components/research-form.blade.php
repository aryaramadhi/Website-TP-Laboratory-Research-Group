@props([
    'id' => 'research-modal',
    'title' => null,
    'action' => null,
    'isEdit' => false
])

@php
    $modalTitle = $title ?? ($isEdit ? 'Edit Riset' : 'Tambah Riset Baru');
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

    <form class="modal__form admin-form" data-admin-form="research" action="{{ $isEdit ? '' : '#' }}" method="POST" enctype="multipart/form-data" novalidate>
        @csrf
        @if ($isEdit)
            @method('PUT')
            <input type="hidden" name="research_id" value="">
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
            <div class="form-group">
                <label for="{{ $id }}-title" class="form-label">Judul Riset</label>
                <input
                    type="text"
                    id="{{ $id }}-title"
                    name="title"
                    class="form-input"
                    placeholder="Masukkan judul riset..."
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
            <div class="form-row">
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
                <div class="form-group">
                    <label for="{{ $id }}-date" class="form-label">Tanggal</label>
                    <div class="form-date-group">
                        <input
                            type="date"
                            id="{{ $id }}-date"
                            name="date"
                            class="form-input"
                            placeholder="dd/mm/yyyy"
                            required
                        >
                        <svg class="form-date-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                            <line x1="16" y1="2" x2="16" y2="6"></line>
                            <line x1="8" y1="2" x2="8" y2="6"></line>
                            <line x1="3" y1="10" x2="21" y2="10"></line>
                        </svg>
                    </div>
                    <div class="form-field-error" id="{{ $id }}-date-error" role="alert" style="display: none;">
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
                    <label for="{{ $id }}-image" class="form-label">
                        Gambar Utama {{ $isEdit ? '(Opsional jika tidak diganti)' : '' }}
                    </label>
                    <div class="form-file-group">
                        <input
                            type="file"
                            id="{{ $id }}-image"
                            name="image"
                            class="form-file-input"
                            accept="image/*"
                            {{ $isEdit ? '' : 'required' }}
                            data-file-input
                        >
                        <div class="form-file-custom">
                            <span class="form-file-text" data-file-text>Pilih File</span>
                        </div>
                    </div>
                    <div class="form-field-error" id="{{ $id }}-image-error" role="alert" style="display: none;">
                        <svg class="form-error-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="8" x2="12" y2="12"></line>
                            <line x1="12" y1="16" x2="12.01" y2="16"></line>
                        </svg>
                        <span class="form-error-text"></span>
                    </div>
                </div>
                <div class="form-group">
                    <label for="{{ $id }}-status" class="form-label">Status</label>
                    <div class="form-select-group">
                        <select
                            id="{{ $id }}-status"
                            name="status"
                            class="form-select"
                            required
                        >
                            <option value="Sedang Berjalan" selected>Sedang Berjalan</option>
                            <option value="Selesai">Selesai</option>
                        </select>
                        <svg class="form-select-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <polyline points="6 9 12 15 18 9"></polyline>
                        </svg>
                    </div>
                    <div class="form-field-error" id="{{ $id }}-status-error" role="alert" style="display: none;">
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
                    <label for="{{ $id }}-supporting-image-1" class="form-label">
                        Gambar Pendukung 1 (Kiri) (Opsional)
                    </label>
                    <div class="form-file-group">
                        <input
                            type="file"
                            id="{{ $id }}-supporting-image-1"
                            name="supporting_image_1"
                            class="form-file-input"
                            accept="image/*"
                            data-file-input
                        >
                        <div class="form-file-custom">
                            <span class="form-file-text" data-file-text>Pilih File</span>
                        </div>
                    </div>
                    <div class="form-field-error" id="{{ $id }}-supporting_image_1-error" role="alert" style="display: none;">
                        <svg class="form-error-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="8" x2="12" y2="12"></line>
                            <line x1="12" y1="16" x2="12.01" y2="16"></line>
                        </svg>
                        <span class="form-error-text"></span>
                    </div>
                </div>
                <div class="form-group">
                    <label for="{{ $id }}-supporting-image-2" class="form-label">
                        Gambar Pendukung 2 (Kanan) (Opsional)
                    </label>
                    <div class="form-file-group">
                        <input
                            type="file"
                            id="{{ $id }}-supporting-image-2"
                            name="supporting_image_2"
                            class="form-file-input"
                            accept="image/*"
                            data-file-input
                        >
                        <div class="form-file-custom">
                            <span class="form-file-text" data-file-text>Pilih File</span>
                        </div>
                    </div>
                    <div class="form-field-error" id="{{ $id }}-supporting_image_2-error" role="alert" style="display: none;">
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
                <label for="{{ $id }}-description" class="form-label">Deskripsi</label>
                <textarea
                    id="{{ $id }}-description"
                    name="description"
                    class="form-textarea"
                    placeholder="Tulis deskripsi riset"
                    rows="6"
                    required
                ></textarea>
                <span class="form-helper-text" style="display: block; font-size: 0.8125rem; color: var(--color-primary-400, #64748b); margin-top: 6px;">
                    Gunakan [[RESEARCH_IMAGES]] pada deskripsi untuk menentukan posisi gambar pendukung.
                </span>
                <div class="form-field-error" id="{{ $id }}-description-error" role="alert" style="display: none;">
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
