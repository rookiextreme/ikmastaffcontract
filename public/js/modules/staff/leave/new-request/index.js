$('#store-update-leave-new-request').on('click', function(){
    common.buttonLoadOnPress('#store-update-leave-new-request')
    let v = new Validscript()
    v.validMix('#leave-date-range', 'Julat Cuti')
    v.validInt('#leave-category', 'Kategori Cuti', true)
    if (v.checkFail()) {
        alerting.formRequired();
        return false;
    }
    v.setNewEntry('staff_id', staff_id)

    http.fetch({
        url: `${common.getUrl()}${moduleUrl}store-update-new-request`,
        data: v.data,
        method: 'POST',
        callback: function(r){
            common.buttonLoadOff('#store-update-leave-new-request');
            if(r.status){
                alerting.fireSwal({
                    text: r.data.message,
                    icon: 'success',
                    buttonColor: 'btn btn-success',
                    confirmButton: 'Close',
                    callback: function(){
                        window.location.reload();
                    }
                })
            }else{
                alerting.error(r.data);
            }
        }
    })
})
