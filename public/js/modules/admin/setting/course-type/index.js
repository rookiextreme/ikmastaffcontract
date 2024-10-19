$('#coursetype-add').on('click', function(){
    resetCourseTypeForm();
    coursetypeModal.show({
        title: 'Tambah Jenis Kursus',
        buttons: [
            {
                selector: '#coursetype-store-add',
                show: true
            },
            {
                selector: '#coursetype-store-update',
                show: false
            }
        ]
    });
})

$(document).on('click','.coursetype-edit', function(){
    resetCourseTypeForm();
    let id = common.getRowId(this, 'data-id');
    let data = common.getForm();
    data.append('id', id);

    coursetypeModal.show({
        title: 'Kemaskini Jenis Kursus',
        buttons: [
            {
                selector: '#coursetype-store-add',
                show: false
            },
            {
                selector: '#coursetype-store-update',
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
                        common.setFormValue('#coursetype-id', r.data.id, 'string');
                    }else{
                        alerting.error(r.data);
                    }
                }
            });
        }
    });
})

$('#coursetype-store-add').on('click', () => coursetypeStoreUpdate('#coursetype-store-add'));
$('#coursetype-store-update').on('click', () => coursetypeStoreUpdate('#coursetype-store-update'));

function coursetypeStoreUpdate(selector){
    common.buttonLoadOnPress(selector);
    let v = new Validscript();

    v.validMix('#name', 'Nama')

    if(v.checkFail()){
        alerting.formRequired();
        common.buttonLoadOff(selector);
        return false;
    }

    v.setNewEntry('id', $('#coursetype-id').val());

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
                        coursetypeModal.hide();
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

$(document).on('click', '.coursetype-delete', function(){
    let data = common.getForm();
    data.append('id', common.getRowId(this, 'data-id'));

    alerting.fireSwal({
        text: 'Padam Jenis Kursus? Semua Kursus Yang Menggunakan Jenis Kursus Akan Dipadam',
        icon: 'error',
        confirmButton: 'Padam',
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
