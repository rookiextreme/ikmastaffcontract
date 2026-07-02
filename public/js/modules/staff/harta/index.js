$(document).on('click', '#harta-add', function(){

    resetHartaForm();

    $('input[name="harta_owner_type"]:checked').trigger('change');

    hartaModal.show({
        title: 'Tambah Harta',
        buttons: [
            { selector: '#harta-store-add', show: true },
            { selector: '#harta-store-update', show: false }
        ],
        callback: function(){

            if ($("#harta-year")[0]?._flatpickr) {
                $("#harta-year")[0]._flatpickr.destroy();
            }

            $("#harta-year").flatpickr({
                dateFormat: "d-m-Y",
                allowInput: true,
                maxDate: "today"
            });
        }
    });
});

$(document).on('click', '#harta-store-add', function(){
    storeHarta('#harta-store-add');
});

$(document).on('click', '#harta-store-update', function(){
    storeHarta('#harta-store-update');
});

function storeHarta(selector){
    common.buttonLoadOnPress(selector);

    let v = new Validscript('ms');
    v.validString('#harta-type', 'Jenis Harta', true);
    v.validMix('#harta-desc', 'Keterangan');
    v.validMix('#harta-value', 'Nilai');
    v.validMix('#harta-year', 'Tarikh Pemilikan');

    let ownerType = $('input[name="harta_owner_type"]:checked').val();

    if(ownerType === 'family'){
        v.validInt('#harta-family-id', 'Nama Ahli Keluarga', true);
    }

    if(ownerType === 'other'){
        v.validMix('#harta-owner-name', 'Nama Pemilik');
        v.validMix('#harta-owner-relation', 'Hubungan');
    }

    if(v.checkFail()){
        alerting.formRequired();
        common.buttonLoadOff(selector);
        return;
    }

    v.setNewEntry('id', $('#harta-id').val());
    v.setNewEntry('staff_id', staff_id);
v.setNewEntry('_token', csrfToken);

    v.setNewEntry('owner_type', $('input[name="harta_owner_type"]:checked').val());
    v.setNewEntry('family_id', $('#harta-family-id').val());
    v.setNewEntry('owner_name', $('#harta-owner-name').val());
    v.setNewEntry('owner_relation', $('#harta-owner-relation').val());
    v.setNewEntry('financial_source', $('#harta-financial-source').val());
    if ($('#harta-attachment')[0].files.length > 0) {
    v.data.append('attachment', $('#harta-attachment')[0].files[0]);
}

    http.fetch({
        url: `${common.getUrl()}${moduleUrl}store-update-harta`,
        data: v.data,
        method: 'POST',
        callback: function(r){
            if(r.status){
                alerting.fireSwal({
                    text: r.data.message,
                    icon: 'success',
                    buttonColor: 'btn btn-success',
                    confirmButton: 'Close',
                    callback: function(){
                        resetHartaForm();
                        hartaModal.hide();
                        table.reload();
                    }
                })
            } else {
                alerting.error(r.data);
            }

            common.buttonLoadOff(selector);
        }
    });
}

$(document).on('click', '.harta-edit', function(){
    resetHartaForm();

    let id = common.getRowId(this, 'data-id');
    let data = common.getForm();
    data.append('id', id);

    hartaModal.show({
        title: 'Kemaskini Harta',
        buttons: [
            { selector: '#harta-store-add', show: false },
            { selector: '#harta-store-update', show: true }
        ],
        callback: function(){

            if ($("#harta-year")[0]?._flatpickr) {
                $("#harta-year")[0]._flatpickr.destroy();
            }

            $("#harta-year").flatpickr({
                dateFormat: "d-m-Y",
                allowInput: true,
                maxDate: "today"
            });

            http.fetch({
                url: `${common.getUrl()}${moduleUrl}get-info-harta`,
                data: data,
                method: 'POST',
                callback: function(r){
                    if(r.status){
                        $('#harta-id').val(r.data.id);
                        $('#harta-type').val(r.data.type).trigger('change');
                        $('#harta-desc').val(r.data.description);
                        $('#harta-value').val(r.data.value);
                        $('#harta-financial-source').val(r.data.financial_source);
                        $('#harta-year').val(r.data.year);

                        if(r.data.owner_type === 'family'){
                            $('#owner-family').prop('checked', true).trigger('change');
                            $('#harta-family-id').val(r.data.family_id).trigger('change');
                            $('#harta-owner-name').val('');
                            $('#harta-owner-relation').val('');
                        }else if(r.data.owner_type === 'other'){
                            $('#owner-other').prop('checked', true).trigger('change');
                            $('#harta-owner-name').val(r.data.owner_name);
                            $('#harta-owner-relation').val(r.data.owner_relation);
                            $('#harta-family-id').val('');
                        }else{
                            $('#owner-self').prop('checked', true).trigger('change');
                            $('#harta-family-id').val('');
                            $('#harta-owner-name').val('');
                            $('#harta-owner-relation').val('');
                        }
                    }else{
                        alerting.error(r.data);
                    }
                }
            });
        }
    });
});

$(document).on('click', '.harta-pelupusan', function(){
    let id = common.getRowId(this, 'data-id');

    $('#harta-disposal-id').val('');
    $('#harta-disposal-method').val('');
    $('#harta-disposal-date').val('');
    $('#harta-disposal-value').val('')
    $('#harta-disposal-other').val('');
    $('#section-disposal-other').hide();

    $('#harta-disposal-method, #harta-disposal-date, #harta-disposal-value, #harta-disposal-other')
        .removeClass('is-valid is-invalid');

    $('#harta-pelupusan-modal .valid-feedback, #harta-pelupusan-modal .invalid-feedback').html('');
    $('#harta-pelupusan-modal .fv-plugins-message-container').html('');
    $('#harta-pelupusan-modal .fv-plugins-icon').remove();

    $('#harta-disposal-id').val(id);

    hartaPelupusanModal.show({
        title: 'Pelupusan Harta',
        buttons: [],
        callback: function(){
            if ($("#harta-disposal-date")[0]?._flatpickr) {
                $("#harta-disposal-date")[0]._flatpickr.destroy();
            }

            $("#harta-disposal-date").flatpickr({
                dateFormat: "d-m-Y",
                allowInput: true,
                maxDate: "today"
            });
        }
    });
});

$(document).on('click', '#harta-store-disposal', function(){
    common.buttonLoadOnPress('#harta-store-disposal');

    let v = new Validscript('ms');

    let method = $('#harta-disposal-method').val();

    if(method === ''){
        v.validMix('#harta-disposal-method', 'Kaedah Pelupusan');
    }

    v.validMix('#harta-disposal-date', 'Tarikh Pelupusan');

    if(method === 'Lain-lain'){
        v.validMix('#harta-disposal-other', 'Nyatakan Kaedah Pelupusan');
    }

    if(v.checkFail()){
        alerting.formRequired();
        common.buttonLoadOff('#harta-store-disposal');
        return;
    }

    v.setNewEntry('id', $('#harta-disposal-id').val());
v.setNewEntry('_token', csrfToken);    v.setNewEntry(
        'disposal_method',
        method === 'Lain-lain' ? $('#harta-disposal-other').val() : method
    );
    v.setNewEntry('disposal_date', $('#harta-disposal-date').val());
    v.setNewEntry('disposal_value', $('#harta-disposal-value').val());

    http.fetch({
        url: `${common.getUrl()}${moduleUrl}store-harta-pelupusan`,
        data: v.data,
        method: 'POST',
        callback: function(r){
            if(r.status){
                alerting.fireSwal({
                    text: r.data.message,
                    icon: 'success',
                    buttonColor: 'btn btn-success',
                    confirmButton: 'Close',
                    callback: function(){
                        hartaPelupusanModal.hide();
                        table.reload();
                    }
                })
            }else{
                alerting.error(r.data);
            }

            common.buttonLoadOff('#harta-store-disposal');
        }
    });
});

// 👉 TAMBAH SINI
$(document).on('change', '#harta-disposal-method', function(){
    if($(this).val() === 'Lain-lain'){
        $('#section-disposal-other').show();
    }else{
        $('#section-disposal-other').hide();
        $('#harta-disposal-other').val('');
    }
});

$(document).on('click', '.harta-delete', function(){
    let data = common.getForm();
    data.append('id', common.getRowId(this, 'data-id'));

    alerting.fireSwal({
        text: 'Padam Data Ini?',
        icon: 'error',
        confirmButton: 'Padam',
        buttonColor: 'btn btn-warning',
        showCancelButton: true,
        callback: function(){
            http.fetch({
                url: `${common.getUrl()}${moduleUrl}delete-harta`,
                data: data,
                method: 'POST',
                callback: function(r){
                    if(r.status){
                        alerting.fireSwal({
                            text: r.data.message,
                            icon: 'success',
                            buttonColor: 'btn btn-success',
                            confirmButton: 'Close',
                            callback: function(){
                                table.reload();
                            }
                        })
                    }else{
                        alerting.error(r.data);
                    }
                }
            });
        }
    })
});

$(document).on('change', '.harta-owner-type', function () {
    let type = $('input[name="harta_owner_type"]:checked').val();

    if (type === 'family') {
        $('#section-harta-self').hide();
        $('#section-harta-family').show();
        $('#section-harta-owner-name').hide();
        $('#section-harta-owner-relation').hide();

        $('#harta-owner-name').val('');
        $('#harta-owner-relation').val('');
    } else if (type === 'other') {
        $('#section-harta-self').hide();
        $('#section-harta-family').hide();
        $('#section-harta-owner-name').show();
        $('#section-harta-owner-relation').show();

        $('#harta-family-id').val('');
    } else {
        $('#section-harta-self').show();
        $('#section-harta-family').hide();
        $('#section-harta-owner-name').hide();
        $('#section-harta-owner-relation').hide();

        $('#harta-family-id').val('');
        $('#harta-owner-name').val('');
        $('#harta-owner-relation').val('');
        $('#harta-owner-self-name').val($('#login-user-name').val());
    }
});

function resetHartaForm(){
    $('#harta-id').val('');
    $('#owner-self').prop('checked', true);
    $('#owner-family').prop('checked', false);
    $('#owner-other').prop('checked', false);

    $('#harta-family-id').val('');
    $('#harta-owner-name').val('');
    $('#harta-owner-relation').val('');
    $('#harta-owner-self-name').val($('#login-user-name').val());

    $('#harta-type').val('Kenderaan').trigger('change');
    $('#harta-desc').val('');
    $('#harta-value').val('');
    $('#harta-financial-source').val('');
    $('#harta-year').val('');
    $('#harta-attachment').val('');
    if ($('#harta-year')[0]?._flatpickr) {
    $('#harta-year')[0]._flatpickr.clear();
}

    $('#section-harta-self').show();
    $('#section-harta-family').hide();
    $('#section-harta-owner-name').hide();
    $('#section-harta-owner-relation').hide();

    $('#harta-type, #harta-desc, #harta-value, #harta-year, #harta-family-id, #harta-owner-name, #harta-owner-relation')
        .removeClass('is-valid is-invalid');

    $('#harta-modal .valid-feedback, #harta-modal .invalid-feedback').html('');
    $('#harta-modal .fv-plugins-message-container').html('');
    $('#harta-modal .fv-plugins-icon').remove();
}
$(document).on('click', '#harta-submit', function(){
    let data = common.getForm();
    data.append('staff_id', staff_id);
    data.append('_token', csrfToken);

    alerting.fireSwal({
        text: 'Adakah anda pasti untuk hantar perisytiharan harta? Selepas dihantar, rekod tidak boleh dikemaskini.',
        icon: 'warning',
        confirmButton: 'Hantar',
        buttonColor: 'btn btn-primary',
        showCancelButton: true,
        callback: function(){
            http.fetch({
                url: `${common.getUrl()}${moduleUrl}submit-harta`,
                data: data,
                method: 'POST',
                callback: function(r){
                    if(r.status){
                        alerting.fireSwal({
                            text: r.data.message,
                            icon: 'success',
                            buttonColor: 'btn btn-success',
                            confirmButton: 'Close',
                            callback: function(){
                                table.reload();
                            }
                        });
                    }else{
                        alerting.error(r.data.message ?? r.data);
                    }
                }
            });
        }
    });
});
$(document).on('click', '#harta-approve', function(){
    let data = common.getForm();
    data.append('staff_id', staff_id);
    data.append('_token', csrfToken);
    data.append('admin_remark', '');

    alerting.fireSwal({
        text: 'Adakah anda pasti untuk sahkan perisytiharan harta ini?',
        icon: 'warning',
        confirmButton: 'Sahkan',
        buttonColor: 'btn btn-success',
        showCancelButton: true,
        callback: function(){
            http.fetch({
                url: `${common.getUrl()}${moduleUrl}approve-harta`,
                data: data,
                method: 'POST',
                callback: function(r){
                    if(r.status){
                        alerting.fireSwal({
                            text: r.data.message,
                            icon: 'success',
                            buttonColor: 'btn btn-success',
                            confirmButton: 'Close',
                            callback: function(){
                                table.reload();
                            }
                        });
                    }else{
                        alerting.error(r.data.message ?? r.data);
                    }
                }
            });
        }
    });
});

$(document).on('click', '#harta-return', function(){
    let reason = prompt('Sila masukkan sebab dikembalikan:');

    if(!reason){
        alerting.error('Sebab dikembalikan wajib diisi.');
        return;
    }

    let data = common.getForm();
    data.append('staff_id', staff_id);
    data.append('_token', csrfToken);
    data.append('admin_remark', reason);

    http.fetch({
        url: `${common.getUrl()}${moduleUrl}return-harta`,
        data: data,
        method: 'POST',
        callback: function(r){
            if(r.status){
                alerting.fireSwal({
                    text: r.data.message,
                    icon: 'success',
                    buttonColor: 'btn btn-success',
                    confirmButton: 'Close',
                    callback: function(){
                        table.reload();
                    }
                });
            }else{
                alerting.error(r.data.message ?? r.data);
            }
        }
    });
});
$(document).on('click', '.harta-approve-disposal', function(){
    let id = common.getRowId(this, 'data-id');

    let data = common.getForm();
    data.append('id', id);
    data.append('_token', csrfToken);
    data.append('disposal_admin_remark', '');

    alerting.fireSwal({
        text: 'Adakah anda pasti untuk sahkan pelupusan harta ini?',
        icon: 'warning',
        confirmButton: 'Sahkan',
        buttonColor: 'btn btn-success',
        showCancelButton: true,
        callback: function(){
            http.fetch({
                url: `${common.getUrl()}${moduleUrl}approve-harta-pelupusan`,
                data: data,
                method: 'POST',
                callback: function(r){
                    if(r.status){
                        alerting.fireSwal({
                            text: r.data.message,
                            icon: 'success',
                            buttonColor: 'btn btn-success',
                            confirmButton: 'Close',
                            callback: function(){
                                table.reload();
                            }
                        });
                    }else{
                        alerting.error(r.data.message ?? r.data);
                    }
                }
            });
        }
    });
});
$(document).on('click', '.harta-reject-disposal', function(){
    let id = common.getRowId(this, 'data-id');

    let data = common.getForm();
    data.append('id', id);
    data.append('_token', csrfToken);

    alerting.fireSwal({
        text: 'Adakah anda pasti untuk tolak permohonan pelupusan ini?',
        icon: 'warning',
        confirmButton: 'Tolak',
        buttonColor: 'btn btn-danger',
        showCancelButton: true,
        callback: function(){
            http.fetch({
                url: `${common.getUrl()}${moduleUrl}reject-harta-pelupusan`,
                data: data,
                method: 'POST',
                callback: function(r){
                    if(r.status){
                        alerting.fireSwal({
                            text: r.data.message,
                            icon: 'success',
                            buttonColor: 'btn btn-success',
                            confirmButton: 'Close',
                            callback: function(){
                                table.reload();
                            }
                        });
                    }else{
                        alerting.error(r.data.message ?? r.data);
                    }
                }
            });
        }
    });
});