$(document).on('click', '#harta-add', function(){

    hartaModal.show({
        title: 'Tambah Harta',
        buttons: [
            { selector: '#harta-store-add', show: true },
            { selector: '#harta-store-update', show: false }
        ],
        callback: function(){
            resetHartaForm(); // ✅ pindah sini
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
    v.validMix('#harta-year', 'Tahun');

    if(v.checkFail()){
        alerting.formRequired();
        common.buttonLoadOff(selector);
        return;
    }

    v.setNewEntry('id', $('#harta-id').val());
    v.setNewEntry('staff_id', staff_id);

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
                        $('#harta-year').val(r.data.year);
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

function resetHartaForm(){
    $('#harta-id').val('');
    $('#harta-type').val('Kenderaan').trigger('change');
    $('#harta-desc').val('');
    $('#harta-value').val('');
    $('#harta-year').val('');

    // buang status validation lama
    $('#harta-type, #harta-desc, #harta-value, #harta-year')
        .removeClass('is-valid is-invalid');

    $('#harta-modal .valid-feedback, #harta-modal .invalid-feedback').html('');
}