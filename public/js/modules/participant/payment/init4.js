$('#cooperation-select').on('change', function() {
    let val = $(this).val();

    if(val){
        window.location.href = `${common.getUrl()}${moduleUrl}${payment_id}/pay?coop=${val}`
    }else{
        window.location.href = `${common.getUrl()}${moduleUrl}${payment_id}/pay`
    }
})

$('#payment-method-select').on('change', function() {
    let val = $(this).val();

    if(val){
        if($(this).find(':selected').attr('data-online') == true){
            $('#kiple-form').attr('style', '');
            $('#normal-pay-form').attr('style', 'display:none');
            $('#payment-upload-form').attr('style', 'display:none');
        }else{
            $('#kiple-form').attr('style', 'display:none');
            $('#normal-pay-form').attr('style', '');
            $('#payment-upload-form').attr('style', '');
        }
    }else{
        $('#kiple-form').attr('style', 'display:none');
        $('#normal-pay-form').attr('style', 'display:none');
        $('#payment-upload-form').attr('style', 'display:none');
    }
})
