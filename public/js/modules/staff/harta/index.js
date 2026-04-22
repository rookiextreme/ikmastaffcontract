$(document).on('click', '#harta-add', function(){

    resetHartaForm();

    $('input[name="harta_owner_type"]:checked').trigger('change');

    hartaModal.show({
        title: 'Tambah Harta',
        buttons: [
            { selector: '#harta-store-add', show: true },
            { selector: '#harta-store-update', show: false }
        ],

        // ✅ FIX: date picker d-m-Y + destroy lama
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
    v.validMix('#harta-year', 'Tarikh Pemilikan'); // ✅ tukar label

    let ownerType = $('input[name="harta_owner_type"]:checked').val();

    if(ownerType === 'family'){
        v.validInt('#harta-family-id', 'Nama Ahli Keluarga', true);
    }

    if(ownerType === 'other'){
        v.validMix('#harta-owner-name', 'Nama Pemilik');
    }

    if(v.checkFail()){
        alerting.formRequired();
        common.buttonLoadOff(selector);
        return;
    }

    v.setNewEntry('id', $('#harta-id').val());
    v.setNewEntry('staff_id', staff_id);

    v.setNewEntry('owner_type', $('input[name="harta_owner_type"]:checked').val());
    v.setNewEntry('family_id', $('#harta-family-id').val());
    v.setNewEntry('owner_name', $('#harta-owner-name').val());

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

        // ✅ FIX: destroy + d-m-Y
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

                        // ✅ penting: set value selepas init
                        $('#harta-year').val(r.data.year);

                        if(r.data.owner_type === 'family'){
                            $('#owner-family').prop('checked', true).trigger('change');
                            $('#harta-family-id').val(r.data.family_id).trigger('change');
                            $('#harta-owner-name').val('');
                        }else if(r.data.owner_type === 'other'){
                            $('#owner-other').prop('checked', true).trigger('change');
                            $('#harta-owner-name').val(r.data.owner_name);
                            $('#harta-family-id').val('');
                        }else{
                            $('#owner-self').prop('checked', true).trigger('change');
                            $('#harta-family-id').val('');
                            $('#harta-owner-name').val('');
                        }
                    }else{
                        alerting.error(r.data);
                    }
                }
            });
        }
    });
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

        $('#harta-owner-name').val('');
    } else if (type === 'other') {
        $('#section-harta-self').hide();
        $('#section-harta-family').hide();
        $('#section-harta-owner-name').show();

        $('#harta-family-id').val('');
    } else {
        $('#section-harta-self').show();
        $('#section-harta-family').hide();
        $('#section-harta-owner-name').hide();

        $('#harta-family-id').val('');
        $('#harta-owner-name').val('');
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
    $('#harta-owner-self-name').val($('#login-user-name').val());

    $('#harta-type').val('Kenderaan').trigger('change');
    $('#harta-desc').val('');
    $('#harta-value').val('');
    $('#harta-year').val('');

    $('#section-harta-self').show();
    $('#section-harta-family').hide();
    $('#section-harta-owner-name').hide();

    $('#harta-type, #harta-desc, #harta-value, #harta-year, #harta-family-id, #harta-owner-name')
        .removeClass('is-valid is-invalid');

    $('#harta-modal .valid-feedback, #harta-modal .invalid-feedback').html('');
    $('#harta-modal .fv-plugins-message-container').html('');
    $('#harta-modal .fv-plugins-icon').remove();
}