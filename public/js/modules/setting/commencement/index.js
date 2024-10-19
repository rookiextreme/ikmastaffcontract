$('#commencement-add').on('click', function(){
    resetCommencementForm();
    commencementModal.show({
        title: 'Add Commencement Date',
        buttons: [
            {
                selector: '#commencement-store-add',
                show: true
            },
            {
                selector: '#commencement-store-update',
                show: false
            }
        ]
    });
})

$(document).on('click','.commencement-edit', function(){
    resetCommencementForm();
    let id = common.getRowId(this, 'data-id');
    let data = common.getForm();
    data.append('id', id);

    commencementModal.show({
        title: 'Update Commencement Date',
        buttons: [
            {
                selector: '#commencement-store-add',
                show: false
            },
            {
                selector: '#commencement-store-update',
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
                        common.setFormValue('#name', r.data.date, 'string');
                        common.setFormValue('#commencement-id', r.data.id, 'string');
                    }else{
                        alerting.error(r.data);
                    }
                }
            });
        }
    });
})

$('#commencement-store-add').on('click', () => feeTypeStoreUpdate('#commencement-store-add'));
$('#commencement-store-update').on('click', () => feeTypeStoreUpdate('#commencement-store-update'));

function feeTypeStoreUpdate(selector){
    common.buttonLoadOnPress(selector);
    let v = new Validscript();

    v.validMix('#name', 'Commencement Date')

    if(v.checkFail()){
        alerting.formRequired();
        common.buttonLoadOff(selector);
        return false;
    }

    v.setNewEntry('id', $('#commencement-id').val());

    http.fetch({
        url: `${common.getUrl()}${moduleUrl}store-update`,
        data: v.data,
        method: 'POST',
        callback: function(r){
            if(r.status){
                commencementModal.hide();
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

            common.buttonLoadOff(selector);
        }
    })
}

$(document).on('click', '.commencement-delete', function(){
    let data = common.getForm();
    data.append('id', common.getRowId(this, 'data-id'));

    alerting.fireSwal({
        text: 'Delete Commencement Date?',
        icon: 'error',
        confirmButton: 'Delete',
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
