<!-- =========================================================
             MODAL HAPUS
        ========================================================== -->
        <div class="modal-overlay" id="modalHapus">
            <div class="modal-box modal-box--sm">

                <div class="modal-body modal-body--center">

                    <div class="confirm-icon">
                        <svg viewBox="0 0 24 24">
                            <polyline points="3 6 5 6 21 6"></polyline>
                            <path d="M19 6 18 20a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"></path>
                            <path d="M10 11v6M14 11v6"></path>
                        </svg>
                    </div>

                    <h3 class="confirm-title">Hapus Produk?</h3>

                    <p class="confirm-text">
                        Anda akan menghapus produk
                        <strong id="hapusNama">-</strong>
                        dari katalog.
                        Tindakan ini tidak dapat dibatalkan.
                    </p>

                </div>


                <div class="modal-foot modal-foot--center">
                    <button class="btn btn--ghost" type="button" onclick="tutupModal('modalHapus')">
                        Batal
                    </button>

                    <button class="btn btn--danger" type="button" onclick="konfirmasiHapus()">
                        Ya, Hapus
                    </button>
                </div>

            </div>
        </div>
