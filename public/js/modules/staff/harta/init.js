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
        { data: 'financial_source' },
        { data: 'year' },
        { data: 'pelupusan' },
        { data: 'terkini' },
        { data: 'declaration_status' },
        { data: 'attachment' },

        {
            data: 'action',
           
        }
    ]
});

table.setupChangePage('#harta-prev', '#harta-next');
table.run();

// 💥 INIT SEMULA fresh
$("#harta-year").flatpickr({
    dateFormat: "d-m-Y",
    allowInput: false,
    maxDate: "today",
    defaultDate: null
});