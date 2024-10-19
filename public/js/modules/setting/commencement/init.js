let modalDate = $("#name").flatpickr();

let commencementModal = new Modals({selector: '#commencement-modal'});

let table = new DatatableInit({
    url: `${common.getUrl()}${moduleUrl}list`,
    method: 'POST',
    selector: '#commencement-list',
    prev: '#commencement-prev',
    next: '#commencement-next',
    columns: [
        {
            data: 'date'
        },
        {
            data: 'action',
            raw: function (full) {
                return `<div class="dropdown">
                      <button class="btn btn-icon btn-warning" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                           <i class="fas fa-pencil fs-4"></i>
                      </button>
                      <ul class="dropdown-menu">
                        <li><button class="dropdown-item text-warning commencement-edit">Edit</button></li>
                        <li><button class="dropdown-item text-danger commencement-delete">Delete</button></li>
                      </ul>
                    </div>`;

            }
        }
    ]
})
table.setSearchButton('#commencement-list-search').setupChangePage('#commencement-prev', '#commencement-next');
table.run();

function resetCommencementForm(){
    common.resetForm([
        ['#name', 'string'],
    ])

    common.setFormValue('#commencement-id', '', 'string');
}
