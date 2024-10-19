$('#race').on('change', function(){
    $('#race-other').attr('disabled', $(this).find(':selected').attr('data-other') == 1 ? false : true).val('');
})

$('#bumiputera').on('change', function(){
    $('#bumiputera-other').attr('disabled', $(this).find(':selected').attr('data-other') == 1 ? false : true).val('');
})

$('#target').on('change', function(){
    $('#target-other').attr('disabled', $(this).find(':selected').attr('data-other') == 1 ? false : true).val('');
})

