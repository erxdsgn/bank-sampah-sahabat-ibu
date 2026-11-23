{{-- PREVIEW MOBILE --}}
        <section class="card preview-card">
            <div class="preview-head">
                <h3 class="card-title">Preview Aplikasi Mobile</h3>
            </div>

            <div class="form-group preview-select-group">
                <div class="combo" id="comboPreview">
                    <input type="text" id="cariPreview" class="combo-input" placeholder="Ketik judul konten..." autocomplete="off" {{ $artikel->isEmpty() ? 'disabled' : '' }}>
                    <div class="combo-list" id="listPreview"></div>
                </div>
                <select id="previewSelector" class="combo-hidden" tabindex="-1" aria-hidden="true">
                    @forelse($artikel as $item)
                        <option value="{{ $item->id_artikel }}" @selected($preview && $preview->id_artikel === $item->id_artikel)>
                            {{ $item->judul }} {{ $item->tanggal_publish ? '' : '(Draft)' }}
                        </option>
                    @empty
                        <option value="">Belum ada konten</option>
                    @endforelse
                </select>
            </div>

            <div class="phone">
                <div class="phone-header">← &nbsp; Baca Artikel</div>
                <div class="phone-body" id="phonePreviewBody"></div>
            </div>
        </section>
