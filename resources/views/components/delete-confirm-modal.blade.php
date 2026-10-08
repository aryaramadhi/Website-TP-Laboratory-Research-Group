@props([
    'id' => 'delete-confirm-modal'
])

<x-modal :id="$id" class="confirm-modal">
    <div class="modal__header">
        <h2 class="modal__title">Konfirmasi Hapus</h2>
        <button type="button" class="modal__close" aria-label="Tutup" data-modal-close>
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
        </button>
    </div>

    <div class="modal__divider" aria-hidden="true"></div>

    <div class="modal__body">
        <p class="confirm-modal__text">Apakah Anda yakin ingin menghapus data ini?</p>

        <div class="modal__actions">
            <button type="button" class="btn-modal-cancel" data-modal-close>
                Batal
            </button>
            <button type="button" class="btn-modal-delete-confirm" data-confirm-delete>
                Hapus
            </button>
        </div>
    </div>
</x-modal>
