$('#coursetagging-add').on('click', function(){
    resetCourseTaggingForm();
    coursetaggingModal.show({
        title: 'Tambah Tagging',
        buttons: [
            {
                selector: '#coursetagging-store-add',
                show: true
            },
            {
                selector: '#coursetagging-store-update',
                show: false
            }
        ]
    });
})

$(document).on('click','.coursetagging-edit', function(){
    resetCourseTaggingForm();
    let id = common.getRowId(this, 'data-id');
    let data = common.getForm();
    data.append('id', id);

    coursetaggingModal.show({
        title: 'Kemaskini Tagging',
        buttons: [
            {
                selector: '#coursetagging-store-add',
                show: false
            },
            {
                selector: '#coursetagging-store-update',
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
                        common.setFormValue('#coursetagging-id', r.data.id, 'string');
                    }else{
                        alerting.error(r.data);
                    }
                }
            });
        }
    });
})

$('#coursetagging-store-add').on('click', () => courseTaggingStoreUpdate('#coursetagging-store-add'));
$('#coursetagging-store-update').on('click', () => courseTaggingStoreUpdate('#coursetagging-store-update'));

function courseTaggingStoreUpdate(selector){
    common.buttonLoadOnPress(selector);
    let v = new Validscript();

    v.validMix('#name', 'Nama')

    if(v.checkFail()){
        alerting.formRequired();
        common.buttonLoadOff(selector);
        return false;
    }

    v.setNewEntry('id', $('#coursetagging-id').val());

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
                        coursetaggingModal.hide();
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

$(document).on('click', '.coursetagging-delete', function(){
    let data = common.getForm();
    data.append('id', common.getRowId(this, 'data-id'));

    alerting.fireSwal({
        text: 'Padam Tag Ini?',
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
