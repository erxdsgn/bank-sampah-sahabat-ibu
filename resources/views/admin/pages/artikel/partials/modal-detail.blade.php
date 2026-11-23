{{-- MODAL DETAIL --}}
    <div class="modal-overlay" id="modalDetail">
        <div class="modal-box">
            <div class="modal-head">
                <h3 class="modal-title">
                    <svg viewBox="0 0 24 24"><path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12z"></path><circle cx="12" cy="12" r="2.5"></circle></svg>
                    Detail Konten
                </h3>
                <button class="modal-close" type="button" onclick="tutupModal('modalDetail')">&times;</button>
            </div>
            <div class="modal-body">
                <div id="detailGambar"></div>
                <div class="detail-title" id="detailJudul">-</div>
                <div class="detail-date" id="detailTanggal">-</div>
                <div class="detail-text" id="detailKonten">-</div>
            </div>
            <div class="modal-foot">
                <button class="btn btn--ghost" type="button" onclick="tutupModal('modalDetail')">Tutup</button>
            </div>
        </div>
    </div>
