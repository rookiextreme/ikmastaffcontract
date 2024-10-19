$('#update-profile').on('click', function(){
    let v = new Validscript();
    v.validMix('#name', 'Nama')
    v.validInt('#identification-no', 'No. Kad Pengenalan')
    v.validEmail('#email', 'E-mel')
    v.validMix('#address', 'Alamat')
    v.validMix('#city', 'Daerah')
    v.validInt('#postal-code', 'Poskod')
    v.validInt('#country', 'Negara', true)

    if(v.getFormDataByKey('country') == 106){
        v.validInt('#state', 'Negeri', true)
    }

    v.validMix('#mobile-phone', 'No. Telefon')
    v.validInt('#academic', 'Kelulusan Akademik', true)
    v.validInt('#marital', 'Taraf Perkahwinan', true)

    v.validInt('#race', 'Bangsa', true)
    v.validInt('#bumiputera', 'Bumiputera', true)
    v.validInt('#target', 'Kumpulan Bersasar', true)

    if($('#race').find(':selected').attr('data-other') == 1){
        v.validMix('#race-other', 'Lain-lain')
    }

    if($('#bumiputera').find(':selected').attr('data-other') == 1){
        v.validMix('#bumiputera-other', 'Lain-lain')
    }

    if($('#target').find(':selected').attr('data-other') == 1){
        v.validMix('#target-other', 'Lain-lain')
    }

    if (v.checkFail()) {
        alerting.formRequired();
        return false;
    }

    v.setNewEntry('participant_id', participant_id);

    http.fetch({
        url: `${common.getUrl()}${moduleUrl}store-update-profile`,
        data: v.data,
        method: 'POST',
        callback: function(r){
            common.buttonLoadOff('#update-profile');
            if(r.status){
                alerting.fireSwal({
                    text: r.data.message,
                    icon: 'success',
                    buttonColor: 'btn btn-success',
                    confirmButton: 'Close',
                    callback: function(){
                        window.location.href = r.data.url
                    }
                })
            }else{
                alerting.error(r.data);
            }
        }
    })
})

