$('#organisation-type').on('change', function(){
    window.location.href = `${common.getUrl()}${moduleUrl}${user_id}/${page}?org_type=${$(this).val()}`
})

$('#organisation-sub-type').on('change', function(){
    window.location.href = `${common.getUrl()}${moduleUrl}${user_id}/${page}?org_type=${$('#organisation-type').val()}&org_sub_type=${$(this).val()}`
});

$('#organisation').select2({
    ajax: {
        url: `${common.getUrl()}${moduleUrl}get-organisation-list`,
        dataType: 'json',
        data: function (params) {
            let query = {
                search: params.term,
                org_subtype: $('#org-sub-type').val(),
                org_type: $('#organisation-type').val()
            }
            return query;
        },
        processResults: function (data) {
            return {
                results: data.items
            };
        }
    },
});

let positionData = common.getForm();
positionData.append('user_id', user_id);

let positionTable = new DatatableInit({
    url: `${common.getUrl()}${moduleUrl}get-position-list`,
    data: positionData,
    method: 'POST',
    selector: '#position-list',
    prev: '#position-prev',
    next: '#position-next',
    columns: [
        {
            data: 'position'
        },
        {
            data: 'organisation'
        },
        {
            data: 'status'
        },
    ]
})
positionTable.setupChangePage('#position-prev', '#position-next');
positionTable.run();
