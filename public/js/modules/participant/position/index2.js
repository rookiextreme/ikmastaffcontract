$('#update-position').on('click', function(){
    let orgSetupType = $('#org-setup-type').val();

    let v = new Validscript('ms');
    v.validInt('#organisation-type', 'Jenis Organisasi')

    if(orgSetupType == 'sub_type_dropdown'){
        v.validInt('#organisation-sub-type', 'Sub Organisasi')
        let org = $('#organisation').val();
        let pos = $('#position').val();
        if(typeof org != 'undefined'){
            v.validInt('#organisation', 'Organisasi')
        }
        if(typeof pos != 'undefined'){
            v.validInt('#position', 'Jawatan')
        }
    }else if(orgSetupType == 'select_org_dropdown'){
        v.validInt('#organisation', 'Organisasi')
        v.validInt('#position', 'Jawatan')
    }else if(orgSetupType == 'company_input'){
        v.validMix('#company-name', 'Nama Syarikat')
    }

    if (v.checkFail()) {
        alerting.formRequired()
        return false
    }

    v.setNewEntry('setupType', orgSetupType)
    v.setNewEntry('user_id', user_id)

    alerting.fireSwal({
        text: 'Adakah Anda Pasti Dengan Maklumat Berikut?',
        icon: 'warning',
        confirmButton: 'Ya',
        buttonColor: 'btn btn-warning',
        showCancelButton: true,
        callback: function(){
            http.fetch({
                url: `${common.getUrl()}${moduleUrl}store-update-position`,
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
                                window.location.href = `${common.getUrl()}${moduleUrl}${user_id}/${page}`
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
