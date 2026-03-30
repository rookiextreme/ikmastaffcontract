<div class="modal fade" id="admin-adjust-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Tukar Cuti (Admin - Auto Approve)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                {{-- hidden --}}
                <input type="hidden" id="adjust-leave-id">

                <div class="row">

                    {{-- Kategori --}}
                    <div class="col-md-6 mb-4">
                        <div class="vals-row">
                            <label class="form-label required">Kategori Baru</label>
                            <select class="form-control" id="adjust-leave-category" data-control="select2">
                                <option value="">Sila Pilih</option>
                                @foreach($leaveCategory as $lc)
                                    <option value="{{ $lc->id }}"
                                        data-mc="{{ $lc->is_mc }}"
                                        data-full="{{ $lc->is_full_day }}"
                                        data-half="{{ $lc->is_half_day }}"
                                        data-group="{{ $lc->is_group_leave ?? 0 }}">
                                        {{ $lc->name }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                    {{-- Jenis Cuti Kelompok (Admin Adjust) --}}
<div class="col-md-6 mb-4" id="adjust-group-leave-type-wrap" style="display:none;">
    <div class="vals-row">
        <label class="form-label required">Jenis Cuti Kelompok</label>
        <select class="form-control" id="adjust-leave-group-type" data-control="select2">
            <option value="">Sila Pilih</option>
            @foreach($groupLeaveTypes as $t)
                <option value="{{ $t->id }}">{{ $t->name }}</option>
            @endforeach
        </select>
        <div class="invalid-feedback"></div>
    </div>
</div>


                    {{-- Tarikh --}}
                    <div class="col-md-6 mb-4">
                        <div class="vals-row">
                            <label class="form-label required">Tarikh Baru (Julat)</label>
                            <input type="text" class="form-control" id="adjust-leave-date-range" value="">
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>

                    {{-- Nota --}}
                    <div class="col-md-12 mb-4">
                        <div class="vals-row">
                            <label class="form-label">Catatan Admin (optional)</label>
                            <textarea class="form-control" id="adjust-admin-note" rows="3"></textarea>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>

                </div>

               <div class="alert alert-warning mb-0 d-flex align-items-start gap-2">
    <i class="fas fa-info-circle mt-1"></i>
    <div>
        <b>Nota Pentadbiran:</b><br>
        Sistem akan membatalkan rekod cuti asal dan menggantikannya dengan rekod baharu
        yang akan <b>diluluskan secara automatik</b>.
    </div>
     <div class="form-check mt-4">
        <input class="form-check-input" type="checkbox" id="admin-adjust-confirm">
        <label class="form-check-label" for="admin-adjust-confirm">
            Saya faham bahawa tindakan ini akan membatalkan rekod cuti sedia ada
            dan menggantikannya dengan rekod baharu.
        </label>
    </div>
</div>

            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-success" id="admin-adjust-submit">
                    Simpan & Auto Lulus
                </button>
            </div>

        </div>
    </div>
</div>
