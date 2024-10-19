$('#session-add').on('click', function(){
    resetSessionForm();
    sessionModal.show({
        title: 'Tambah Tarikh',
        buttons: [
            {
                selector: '#session-store-add',
                show: true
            },
            {
                selector: '#session-store-update',
                show: false
            }
        ]
    });
})

$(document).on('click','.session-edit', function(){
    resetSessionForm();
    let id = common.getRowId(this, 'data-id');
    let data = common.getForm();
    data.append('id', id);

    sessionModal.show({
        title: 'Kemaskini Tarikh',
        buttons: [
            {
                selector: '#session-store-add',
                show: false
            },
            {
                selector: '#session-store-update',
                show: true
            }
        ],
        callback: function(){
            http.fetch({
                url: `${common.getUrl()}${moduleUrl}get-info-session`,
                data: data,
                method: 'POST',
                callback: function(r){
                    if(r.status){
                        common.setFormValue('#session-start-date', r.data.date_start, 'string');
                        common.setFormValue('#session-end-date', r.data.date_end, 'string');
                        common.setFormValue('#session-id', r.data.id, 'string');
                    }else{
                        alerting.error(r.data);
                    }
                }
            });
        }
    });
})

$('#session-store-add').on('click', () => sessionStoreUpdate('#session-store-add'));
$('#session-store-update').on('click', () => sessionStoreUpdate('#session-store-update'));

function sessionStoreUpdate(selector){
    common.buttonLoadOnPress(selector);
    let v = new Validscript();

    v.validRegularDate('#session-start-date', 'Tarikh Mula')
    v.validRegularDate('#session-end-date', 'Tarikh Akhir')

    if(v.checkFail()){
        alerting.formRequired();
        common.buttonLoadOff(selector);
        return false;
    }

    v.setNewEntry('id', $('#session-id').val());

    http.fetch({
        url: `${common.getUrl()}${moduleUrl}store-update-session`,
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
                        if(r.data.update){
                            sessionModal.hide();
                            table.reload();
                        }else{
                            window.location.href = r.data.url;
                        }
                    }
                })
            }else{
                alerting.error(r.data);
            }

            common.buttonLoadOff(selector);
        }
    })
}

$(document).on('click', '.session-delete', function(){
    let data = common.getForm();
    data.append('id', common.getRowId(this, 'data-id'));

    alerting.fireSwal({
        text: 'Padam Tarikh?',
        icon: 'error',
        confirmButton: 'Padam',
        buttonColor: 'btn btn-warning',
        showCancelButton: true,
        callback: function(){
            http.fetch({
                url: `${common.getUrl()}${moduleUrl}delete-session`,
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
