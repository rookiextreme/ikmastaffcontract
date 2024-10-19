$('#update-session-basic').on('click', function(){
    common.buttonLoadOnPress('#update-session-basic');

    let v = new Validscript('ms');

    v.validRegularDate('#session-start-date', 'Tarikh Mula')
    v.validRegularDate('#session-end-date', 'Tarikh Akhir')
    v.validInt('#session-course-type', 'Jenis Kursus', true)
    v.validInt('#session-course-category', 'Kategori Kursus', true)
    v.validMix('#session-location', 'Lokasi')
    v.validMix('#session-learning-outcome', 'Hasil Pembelajaran')

    if($('#session-thumbnail').val()){
        v.validUpload('#session-thumbnail', 'Thumbnail', ['png', 'jpeg', 'png', 'jpg'], 'session_thumbnail')
    }
    if(v.checkFail()){
        alerting.formRequired();
        common.buttonLoadOff(selector);
        return false;
    }

    v.setNewEntry('session_name', $('#session-name').val())
    v.setNewEntry('seo_description', $('#seo-description').val())
    v.setNewEntry('taggings_select', JSON.stringify($('#taggings').val()))
    v.setNewEntry('description', quill.root.innerHTML.trim())
    v.setNewEntry('id', )
    http.fetch({
        url: `${common.getUrl()}${moduleUrl}store-update-setup`,
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
                        window.location.reload()
                    }
                })
            }else{
                alerting.error(r.data);
            }

            common.buttonLoadOff(selector);
        }
    })
})

$('#update-session-position').on('click', function(){
    let positionAvailable = $('#position-available').val()

    let v = new Validscript('ms');
    v.validMix('#positions', 'Jenis Peserta')

    let withPrice = false;

    let koperasi = $('#koperasi').val();
    let agensi = $('#agensi').val();
    let usahawan = $('#usahawan').val();
    let individu = $('#individu').val();

    if(typeof koperasi != 'undefined'){
        v.validDoubleInt('#koperasi', 'Harga');
        v.setNewEntry('koperasi_id', $('#koperasi').attr('data-id'));
        v.setNewEntry('koperasi_free', $('#koperasi-check').prop('checked') == true ? 1 : 0);
        v.setNewEntry('is_koperasi', true);
        withPrice = true;
    }

    if(typeof agensi != 'undefined'){
        v.validDoubleInt('#agensi', 'Harga');
        v.setNewEntry('agensi_id', $('#agensi').attr('data-id'));
        v.setNewEntry('agensi_free', $('#agensi-check').prop('checked') == true ? 1 : 0);
        v.setNewEntry('is_agensi', true);
        withPrice = true;
    }

    if(typeof usahawan != 'undefined'){
        v.validDoubleInt('#usahawan', 'Harga');
        v.setNewEntry('usahawan_id', $('#usahawan').attr('data-id'));
        v.setNewEntry('usahawan_free', $('#usahawan-check').prop('checked') == true ? 1 : 0);
        v.setNewEntry('is_usahawan', true);
        withPrice = true;
    }

    if(typeof individu != 'undefined'){
        v.validDoubleInt('#individu', 'Harga');
        v.setNewEntry('individu_id', $('#individu').attr('data-id'));
        v.setNewEntry('individu_free', $('#individu-check').prop('checked') == true ? 1 : 0);
        v.setNewEntry('is_individu', true);
        withPrice = true;
    }

    if(v.checkFail()){
        alerting.formRequired();
        common.buttonLoadOff(selector);
        return false;
    }


    v.setNewEntry('positions_select', JSON.stringify($('#positions').val()));
    v.setNewEntry('with_price', withPrice == true ? 1 : 0);
    http.fetch({
        url: `${common.getUrl()}${moduleUrl}store-update-setup-positions`,
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
                        window.location.reload()
                    }
                })
            }else{
                alerting.error(r.data);
            }

            common.buttonLoadOff(selector);
        }
    })
})

$('#session-activate').on('click', function(){
    let data = common.getForm();

    http.fetch({
        url: `${common.getUrl()}${moduleUrl}activate-session`,
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
                        window.location.reload()
                    }
                })
            }else{
                alerting.error(r.data);
            }

            common.buttonLoadOff(selector);
        }
    })
})

$(document).on('click', '.pick-suk', function(){
    let data = common.getForm();
    data.append('id', common.getRowId(this, 'data-id'));

    alerting.fireSwal({
        text: 'Pilih Sebagai SUK Kursus Ini?',
        icon: 'warning',
        confirmButton: 'Ya',
        buttonColor: 'btn btn-warning',
        showCancelButton: true,
        callback: function(){
            http.fetch({
                url: `${common.getUrl()}${moduleUrl}pick-suk`,
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

$(document).on('click', '.unpick-suk', function(){
    let data = common.getForm();
    data.append('id', common.getRowId(this, 'data-id'));

    alerting.fireSwal({
        text: 'Padam SUK Dari Kursus Ini?',
        icon: 'error',
        confirmButton: 'Ya',
        buttonColor: 'btn btn-warning',
        showCancelButton: true,
        callback: function(){
            http.fetch({
                url: `${common.getUrl()}${moduleUrl}unpick-suk`,
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

$('.download-participant').on('click', function(){
    let type = $(this).attr('data-type');

    let data = common.getForm();
    data.append('type', type);

    http.fetch({
        url: `${common.getUrl()}${moduleUrl}download-participant`,
        data: data,
        method: 'POST',
        callback: function(r){
            if(r.status){
                window.location.href = `${common.getUrl()}${moduleUrl}download-participant?download=1`;
            }else{
                alerting.error(r.data);
            }
        }
    });
})

$('#send-test-email').on('click', function(){
    common.buttonLoadOnPress('#send-test-email');

    let v = new Validscript('ms');

    v.validEmail('#session-email-blast-testmail', 'E-Mel Pengujian')

    if(v.checkFail()){
        alerting.formRequired();
        common.buttonLoadOff('#send-test-email');
        return false;
    }

    v.setNewEntry('template', emailBlastTemplate.root.innerHTML.trim())
    v.setNewEntry('session_id', session_id)
    http.fetch({
        url: `${common.getUrl()}${moduleUrl}email-blast-test`,
        data: v.data,
        method: 'POST',
        callback: function(r){
            if(r.status){
                alerting.fireSwal({
                    text: r.data.message,
                    icon: 'success',
                    buttonColor: 'btn btn-success',
                    confirmButton: 'Close',
                })
            }else{
                alerting.error(r.data);
            }

            common.buttonLoadOff('#send-test-email');
        }
    })
})

$('#update-email-blast-template').on('click', function(){
    common.buttonLoadOnPress('#update-email-blast-template');

    let v = new Validscript('ms');

    if(v.checkFail()){
        alerting.formRequired();
        common.buttonLoadOff('#update-email-blast-template');
        return false;
    }

    v.setNewEntry('template', emailBlastTemplate.root.innerHTML.trim())
    v.setNewEntry('session_id', session_id)

    alerting.fireSwal({
        text: 'Adakah Anda Pasti?',
        icon: 'warning',
        confirmButton: 'Ya',
        buttonColor: 'btn btn-warning',
        showCancelButton: true,
        callback: function(){
            common.buttonLoadOff('#update-email-blast-template');
            http.fetch({
                url: `${common.getUrl()}${moduleUrl}email-blast-real`,
                data: v.data,
                method: 'POST',
                callback: function(r){
                    if(r.status){
                        alerting.fireSwal({
                            text: r.data.message,
                            icon: 'success',
                            buttonColor: 'btn btn-success',
                            confirmButton: 'Close',
                        })
                    }else{
                        alerting.error(r.data);
                    }
                }
            })
        }
    })
})
