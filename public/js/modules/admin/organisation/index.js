$('#organisation-add').on('click', function(){
    resetOrganisationForm();
    organisationModal.show({
        title: `Tambah ${type}`,
        buttons: [
            {
                selector: '#organisation-store-add',
                show: true
            },
            {
                selector: '#organisation-store-update',
                show: false
            }
        ]
    });
})

$(document).on('click','.organisation-edit', function(){
    resetOrganisationForm();
    let id = common.getRowId(this, 'data-id');
    let data = common.getForm();
    data.append('id', id);

    organisationModal.show({
        title: `Kemaskini ${type}`,
        buttons: [
            {
                selector: '#organisation-store-add',
                show: false
            },
            {
                selector: '#organisation-store-update',
                show: true
            }
        ],
        callback: function(){
            http.fetch({
                url: `${common.getUrl()}${moduleUrl}get-info`,
                data: data,
                method: 'POST',
                callback: function(r){
                    if(r.status){
                        common.setFormValue('#name', r.data.name, 'string');
                        common.setFormValue('#sub-type', r.data.organisation_sub_type_id, 'dropdown');
                        common.setFormValue('#telephone', r.data.phone_no, 'string');
                        common.setFormValue('#fax', r.data.fax_no, 'string');
                        common.setFormValue('#address', r.data.address, 'string');
                        common.setFormValue('#city', r.data.city, 'string');
                        common.setFormValue('#postcode', r.data.postal_code, 'string');
                        common.setFormValue('#state', r.data.state_id, 'dropdown');
                        common.setFormValue('#location-code', r.data.location_code, 'string');
                        common.setFormValue('#worker', r.data.worker, 'string');
                        common.setFormValue('#website', r.data.website, 'string');
                        common.setFormValue('#organisation-id', r.data.id, 'string');
                    }else{
                        alerting.error(r.data);
                    }
                }
            });
        }
    });
})

$('#organisation-store-add').on('click', () => organisationStoreUpdate('#organisation-store-add'));
$('#organisation-store-update').on('click', () => organisationStoreUpdate('#organisation-store-update'));

function organisationStoreUpdate(selector){
    common.buttonLoadOnPress(selector);
    let v = new Validscript('ms');

    if(organisation_type_id == 1){
        v.validInt('#sub-type', 'Jenis', true)
    }
    v.validMix('#name', 'Nama')
    v.validInt('#telephone', 'No. Telefon')

    if($('#fax').val() != ''){
        v.validInt('#fax', 'No. Fax')
    }else{
        v.setNewEntry('fax', $('#fax').val());
    }

    v.validMix('#address', 'Alamat')
    v.validMix('#city', 'Daerah')
    v.validInt('#postcode', 'Poskod')
    v.validInt('#state', 'Negeri', true)
    v.validMix('#location-code', 'Kod Lokasi')

    if(organisation_type_id != 1){
        v.validInt('#worker', 'Jumlah Pekerja')
        v.validMix('#website', 'Laman Website')
    }

    if(v.checkFail()){
        alerting.formRequired();
        common.buttonLoadOff(selector);
        return false;
    }

    v.setNewEntry('organisation_type', organisation_type_id);
    v.setNewEntry('id', $('#organisation-id').val());

    http.fetch({
        url: `${common.getUrl()}${moduleUrl}store-update`,
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
                        organisationModal.hide();
                        table.reload();
                    }
                })
            }else{
                alerting.error(r.data);
            }

            common.buttonLoadOff(selector);
        }
    })
}

$(document).on('click', '.organisation-delete', function(){
    let data = common.getForm();
    data.append('id', common.getRowId(this, 'data-id'));
    data.append('type', type)

    alerting.fireSwal({
        text: `Padam ${type} Ini?`,
        icon: 'error',
        confirmButton: 'Ya',
        buttonColor: 'btn btn-warning',
        showCancelButton: true,
        callback: function(){
            http.fetch({
                url: `${common.getUrl()}${moduleUrl}delete`,
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
})
