$("#session-start-date").flatpickr({
    dateFormat: "d-m-Y",
});

$("#session-end-date").flatpickr({
    dateFormat: "d-m-Y",
});

let sessionModal = new Modals({selector: '#session-modal'});
let table = new DatatableInit({
    url: `${common.getUrl()}${moduleUrl}session-list`,
    method: 'POST',
    selector: '#session-list',
    prev: '#session-prev',
    next: '#session-next',
    columns: [
        {
            data: 'date_start'
        },
        {
            data: 'date_end'
        },
        {
            data: 'action',
            raw: function (full) {
                return `<div class="dropdown">
                      <button class="btn btn-icon btn-warning" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                           <i class="fas fa-pencil fs-4"></i>
                      </button>
                      <ul class="dropdown-menu">
                        <li><a href="${common.getUrl()}admin/course/${full.id}/setup" class="dropdown-item text-info">Setup</a></li>
                        <li><button class="dropdown-item text-warning session-edit">Kemaskini</button></li>
                        <li><button class="dropdown-item text-danger session-delete">Delete</button></li>
                      </ul>
                    </div>`;

            }
        }
    ]
})
table.setupChangePage('#session-prev', '#session-next');
table.run();

function resetSessionForm(){
    common.resetForm([
        ['#session-start-date', 'string'],
        ['#session-end-date', 'string'],
    ])

    common.setFormValue('#session-id', '', 'string');
}
