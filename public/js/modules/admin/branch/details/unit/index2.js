$('#unit-add').on('click', function(){
    resetUnitForm();
    unitModal.show({
        title: 'Tambah Unit',
        buttons: [
            {
                selector: '#unit-store-add',
                show: true
            },
            {
                selector: '#unit-store-update',
                show: false
            }
        ]
    });
})

$(document).on('click','.unit-edit', function(){
    resetUnitForm();
    let id = common.getRowId(this, 'data-id');
    let data = common.getForm();
    data.append('id', id);

    unitModal.show({
        title: 'Kemaskini Unit',
        buttons: [
            {
                selector: '#unit-store-add',
                show: false
            },
            {
                selector: '#unit-store-update',
                show: true
            }
        ],
        callback: function(){
            http.fetch({
                url: `${common.getUrl()}${moduleUrl}unit-get-info`,
                data: data,
                method: 'POST',
                callback: function(r){
                    if(r.status){
                        common.setFormValue('#unit-name', r.data.name, 'string');
                        common.setFormValue('#unit-id', r.data.id, 'string');
                    }else{
                        alerting.error(r.data);
                    }
                }
            });
        }
    });
})

$('#unit-store-add').on('click', () => unitStoreUpdate('#unit-store-add'));
$('#unit-store-update').on('click', () => unitStoreUpdate('#unit-store-add'));

function unitStoreUpdate(selector){
    common.buttonLoadOnPress(selector);
    let v = new Validscript('ms');

    v.validMix('#unit-name', 'Unit', true)

    if(v.checkFail()){
        alerting.formRequired();
        common.buttonLoadOff(selector);
        return false;
    }

    v.setNewEntry('id', $('#unit-id').val());
    v.setNewEntry('branch_id', branch_id);

    http.fetch({
        url: `${common.getUrl()}${moduleUrl}unit-store-update`,
        data: v.data,
        method: 'POST',
        callback: function(r){
            console.log(r);
            if(r.status){
                alerting.fireSwal({
                    text: r.data.message,
                    icon: 'success',
                    buttonColor: 'btn btn-success',
                    confirmButton: 'Close',
                    callback: function(){
                        unitModal.hide();
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

$(document).on('click', '.unit-delete', function(){
    let data = common.getForm();
    data.append('id', common.getRowId(this, 'data-id'));

    alerting.fireSwal({
        text: 'Padam Unit?',
        icon: 'warning',
        confirmButton: 'Ya',
        buttonColor: 'btn btn-warning',
        showCancelButton: true,
        callback: function(){
            http.fetch({
                url: `${common.getUrl()}${moduleUrl}unit-delete`,
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
