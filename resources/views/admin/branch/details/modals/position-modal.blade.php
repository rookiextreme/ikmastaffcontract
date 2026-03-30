<input type="hidden" id="position-id" value="">
<div class="modal fade" tabindex="-1" id="position-modal">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title"></h3>
                <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal" aria-label="Close">
                    <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                </div>
            </div>

            <div class="modal-body">
                <div class="row">
                    {{-- Jawatan --}}
                    <div class="col-md-12 vals-row mb-4">
                        <label for="position-name" class="required form-label">Jawatan</label>
                        <select class="form-control" id="position-name" name="position-name" data-control="select2">
                            <option>Sila Pilih</option>
                            @foreach($positions as $p)
                                <option value="{{ $p->id }}">{{ $p->name }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>

                    {{-- Unit (HQ Sahaja) --}}
                    <div class="col-md-6">
    <label for="position_unit" class="form-label">Unit</label>
    <select class="form-control" id="position_unit">
        <option value="">Sila Pilih</option>
        @foreach($units as $unit)
            <option value="{{ $unit->id }}">{{ $unit->name }}</option>
        @endforeach
    </select>
</div>


                    {{-- Gred --}}
                    <div class="col-md-12 vals-row mb-4">
                        <label for="position-grade" class="required form-label">Gred</label>
                        <select class="form-control" id="position-grade" name="position-grade" data-control="select2">
                            <option>Sila Pilih</option>
                            @foreach($grades as $g)
                                <option value="{{ $g->id }}">{{ $g->name }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>

                    {{-- Bilangan Asas Cuti --}}
                    <div class="col-md-12 vals-row mb-4">
                        <label for="position-holiday" class="required form-label">Bilangan Asas Cuti</label>
                        <input type="text" class="form-control text-uppercase" id="position-holiday" value="">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-success" id="position-store-add">
                    <span class="indicator-label">Simpan</span>
                    <span class="indicator-progress">Sedang Diproses... 
                        <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                    </span>
                </button>
                <button type="button" class="btn btn-warning" id="position-store-update">
                    <span class="indicator-label">Kemaskini</span>
                    <span class="indicator-progress">Sedang Dikemaskini... 
                        <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>
