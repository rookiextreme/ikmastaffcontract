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
                    <label class="required form-label">Pemilik Harta</label>

                    <div class="d-flex gap-5 mt-2">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input harta-owner-type" type="radio" name="harta_owner_type" id="owner-self" value="self" checked>
                            <label class="form-check-label" for="owner-self">Sendiri</label>
                        </div>

                        <div class="form-check form-check-inline">
                            <input class="form-check-input harta-owner-type" type="radio" name="harta_owner_type" id="owner-family" value="family">
                            <label class="form-check-label" for="owner-family">Ahli Keluarga</label>
                        </div>

                        <div class="form-check form-check-inline">
                            <input class="form-check-input harta-owner-type" type="radio" name="harta_owner_type" id="owner-other" value="other">
                            <label class="form-check-label" for="owner-other">Lain-lain</label>
                        </div>
                    </div>
                </div>

                <div class="mb-4" id="section-harta-self" style="display:none;">
    <label class="form-label">Nama Pemilik</label>
<input type="text" class="form-control" id="harta-owner-self-name" value="{{ $login_user_name ?? '' }}" readonly>
</div>
                <div class="mb-4" id="section-harta-family" style="display:none;">
                    <label class="form-label">Nama Ahli Keluarga</label>
                    <select class="form-control" id="harta-family-id">
                        <option value="">- Pilih -</option>
                        @foreach(($families ?? []) as $family)
                            <option value="{{ $family->id }}">
                                {{ $family->name }} ({{ $family->relation }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-4" id="section-harta-owner-name" style="display:none;">
                    <label class="form-label">Nama Pemilik</label>
                    <input type="text" class="form-control" id="harta-owner-name">
                </div>

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
                    <label class="required form-label">Tarikh Pemilikan</label>
<input type="text" class="form-control" id="harta-year" placeholder="dd-mm-yyyy">
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