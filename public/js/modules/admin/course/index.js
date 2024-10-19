$('#course-add').on('click', function(){
    resetCourseForm();
    courseModal.show({
        title: 'Tambah Kursus',
        buttons: [
            {
                selector: '#course-store-add',
                show: true
            },
            {
                selector: '#course-store-update',
                show: false
            }
        ]
    });
})

$(document).on('click','.course-edit', function(){
    resetCourseForm();
    let id = common.getRowId(this, 'data-id');
    let data = common.getForm();
    data.append('id', id);

    courseModal.show({
        title: 'Kemaskini Kursus',
        buttons: [
            {
                selector: '#course-store-add',
                show: false
            },
            {
                selector: '#course-store-update',
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
                        common.setFormValue('#course-id', r.data.id, 'string');
                    }else{
                        alerting.error(r.data);
                    }
                }
            });
        }
    });
})

$('#course-store-add').on('click', () => courseStoreUpdate('#course-store-add'));
$('#course-store-update').on('click', () => courseStoreUpdate('#course-store-update'));

function courseStoreUpdate(selector){
    common.buttonLoadOnPress(selector);
    let v = new Validscript();

    v.validMix('#name', 'Nama')

    if(v.checkFail()){
        alerting.formRequired();
        common.buttonLoadOff(selector);
        return false;
    }

    v.setNewEntry('id', $('#course-id').val());

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
                        if(r.data.update){
                            courseModal.hide();
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

$(document).on('click', '.course-delete', function(){
    let data = common.getForm();
    data.append('id', common.getRowId(this, 'data-id'));

    alerting.fireSwal({
        text: 'Padam Kursus? Semua Sesi Akan Dipadam',
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
