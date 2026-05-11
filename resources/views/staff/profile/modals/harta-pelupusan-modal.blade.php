<input type="hidden" id="harta-disposal-id">

<div class="modal fade" id="harta-pelupusan-modal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h3 class="modal-title">Pelupusan Harta</h3>
                <div class="btn btn-icon" data-bs-dismiss="modal">✕</div>
            </div>

            <div class="modal-body">

                <div class="mb-4">
                    <label class="required form-label">Kaedah Pelupusan</label>
                    <select class="form-control" id="harta-disposal-method">
                        <option value="">- PILIHAN -</option>
                        <option value="Dihadiah">Dihadiah</option>
                        <option value="Lelongan">Lelongan</option>
                        <option value="Bencana Alam">Bencana Alam</option>
                        <option value="Dijual">Dijual</option>
                        <option value="Dicuri">Dicuri</option>
                        <option value="Lain-lain">Lain-lain</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="required form-label">Tarikh Pelupusan</label>
                    <input type="text" class="form-control" id="harta-disposal-date" placeholder="dd-mm-yyyy">
                </div>

                 <div class="mb-4">
                    <label class="form-label">Nilai Pelupusan (RM)</label>

                    <input type="number"
                           step="0.01"
                           min="0"
                           class="form-control"
                           id="harta-disposal-value"
                           name="disposal_value"
                           placeholder="Contoh: 3000.00">
                </div>

                <div class="mb-4" id="section-disposal-other" style="display:none;">
    <label class="form-label">Nyatakan Kaedah</label>
    <input type="text" class="form-control" id="harta-disposal-other">
</div>

            </div>

            <div class="modal-footer">
                <button class="btn btn-danger" data-bs-dismiss="modal">Tutup</button>
                <button class="btn btn-success" id="harta-store-disposal">Simpan</button>
            </div>

        </div>
    </div>
</div>