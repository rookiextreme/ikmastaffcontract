let hartaModal = new Modals({selector: '#harta-modal'});
let hartaPelupusanModal = new Modals({selector: '#harta-pelupusan-modal'});

let hartaData = common.getForm(false)
hartaData.append('staff_id', staff_id)

let table = new DatatableInit({
    url: `${common.getUrl()}${moduleUrl}harta-list`,
    method: 'POST',
    data: hartaData,
    selector: '#harta-list',
    prev: '#harta-prev',
    next: '#harta-next',
    columns: [
        { data: 'owner' },
        { data: 'type' },
        { data: 'description' },
        { data: 'value' },
        { data: 'year' },
        { data: 'pelupusan' },
        { data: 'terkini' },
        { data: 'declaration_status' },

        {
            data: 'action',
           
        }
    ]
});

table.setupChangePage('#harta-prev', '#harta-next');
table.run();

$("#harta-year").flatpickr({
    dateFormat: "d-m-Y",
    allowInput: true,
    maxDate: "today"
});