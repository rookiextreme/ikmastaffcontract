$('#normal-payment').on('click', function(){
    let v = new Validscript('ms');

    v.validInt('#payment-method-select', 'Cara Pembayaran', true)
    v.validUpload('#payment-proof', 'Bukti Pembayaran', ['pdf', 'jpeg', 'jpg', 'png'], 'payment_proof')

    if (v.checkFail()) {
        alerting.formRequired()
        return false
    }
    v.setNewEntry('payment_id', payment_id)

    http.fetch({
        url: `${common.getUrl()}${moduleUrl}proceed-normal-payment`,
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
                        window.location.reload();
                    }
                })
            }else{
                alerting.error(r.data);
            }
        }
    })
})

$('#free-payment').on('click', function(){
    let v = new Validscript('ms');

    if (v.checkFail()) {
        alerting.formRequired()
        return false
    }
    v.setNewEntry('payment_id', payment_id)
    v.setNewEntry('free', 1)

    http.fetch({
        url: `${common.getUrl()}${moduleUrl}proceed-normal-payment`,
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
                        window.location.reload();
                    }
                })
            }else{
                alerting.error(r.data);
            }
        }
    })
})

$('#confirm-payment').on('click', function(){
    let data = common.getForm();
    data.append('id', payment_id);

    alerting.fireSwal({
        text: 'Sahkan Pembayaran Ini?',
        icon: 'warning',
        confirmButton: 'Ya',
        buttonColor: 'btn btn-warning',
        showCancelButton: true,
        callback: function(){
            http.fetch({
                url: `${common.getUrl()}${moduleUrl}confirm-payment`,
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
                }
            });
        }
    })
})

$('#reject-payment').on('click', function(){
    let data = common.getForm();
    data.append('id', payment_id);

    alerting.fireSwal({
        text: 'Batalkan Pembayaran Ini?',
        icon: 'warning',
        confirmButton: 'Ya',
        buttonColor: 'btn btn-warning',
        showCancelButton: true,
        callback: function(){
            http.fetch({
                url: `${common.getUrl()}${moduleUrl}reject-payment`,
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
                }
            });
        }
    })
})

