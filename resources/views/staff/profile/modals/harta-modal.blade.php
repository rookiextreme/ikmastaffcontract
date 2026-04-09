<input type="hidden" id="harta-id">

<div class="modal fade" id="harta-modal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h3 class="modal-title"></h3>
                <div class="btn btn-icon" data-bs-dismiss="modal">
                    ✕
                </div>
            </div>

            <div class="modal-body">

                <div class="mb-4">
                    <label class="required form-label">Jenis Harta</label>
                    <select class="form-control" id="harta-type">
                        <option value="Kenderaan">Kenderaan</option>
                        <option value="Rumah">Rumah</option>
                        <option value="Tanah">Tanah</option>
                        <option value="Simpanan">Simpanan</option>
                        <option value="Lain lain">Lain-lain</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="required form-label">Keterangan</label>
                    <input type="text" class="form-control" id="harta-desc">
                </div>

                <div class="mb-4">
                    <label class="required form-label">Nilai (RM)</label>
                    <input type="number" class="form-control" id="harta-value">
                </div>

                <div class="mb-4">
                    <label class="required form-label">Tahun Perolehan</label>
                    <input type="text" class="form-control" id="harta-year">
                </div>

            </div>

            <div class="modal-footer">
                <button class="btn btn-danger" data-bs-dismiss="modal">Tutup</button>

                <button class="btn btn-success" id="harta-store-add">Simpan</button>
                <button class="btn btn-warning" id="harta-store-update">Kemaskini</button>
            </div>

        </div>
    </div>
</div>