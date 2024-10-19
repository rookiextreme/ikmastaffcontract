$('#coursecategory-add').on('click', function(){
    resetCourseCategoryForm();
    coursecategoryModal.show({
        title: 'Tambah Jenis Kursus',
        buttons: [
            {
                selector: '#coursecategory-store-add',
                show: true
            },
            {
                selector: '#coursecategory-store-update',
                show: false
            }
        ]
    });
})

$(document).on('click','.coursecategory-edit', function(){
    resetCourseCategoryForm();
    let id = common.getRowId(this, 'data-id');
    let data = common.getForm();
    data.append('id', id);

    coursecategoryModal.show({
        title: 'Kemaskini Jenis Kursus',
        buttons: [
            {
                selector: '#coursecategory-store-add',
                show: false
            },
            {
                selector: '#coursecategory-store-update',
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
                        common.setFormValue('#coursecategory-id', r.data.id, 'string');
                    }else{
                        alerting.error(r.data);
                    }
                }
            });
        }
    });
})

$('#coursecategory-store-add').on('click', () => coursecategoryStoreUpdate('#coursecategory-store-add'));
$('#coursecategory-store-update').on('click', () => coursecategoryStoreUpdate('#coursecategory-store-update'));

function coursecategoryStoreUpdate(selector){
    common.buttonLoadOnPress(selector);
    let v = new Validscript();

    v.validMix('#name', 'Nama')

    if(v.checkFail()){
        alerting.formRequired();
        common.buttonLoadOff(selector);
        return false;
    }

    v.setNewEntry('id', $('#coursecategory-id').val());

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
                        coursecategoryModal.hide();
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

$(document).on('click', '.coursecategory-delete', function(){
    let data = common.getForm();
    data.append('id', common.getRowId(this, 'data-id'));

    alerting.fireSwal({
        text: 'Padam Kategori Kursus? Semua Kursus Yang Menggunakan Kategori Kursus Akan Dipadam',
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
