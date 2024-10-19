let coursetypeModal = new Modals({selector: '#coursetype-modal'});

let table = new DatatableInit({
    url: `${common.getUrl()}${moduleUrl}list`,
    method: 'POST',
    selector: '#coursetype-list',
    prev: '#coursetype-prev',
    next: '#coursetype-next',
    columns: [
        {
            data: 'name'
        },
        {
            data: 'action',
            raw: function (full) {
                return `<div class="dropdown">
                      <button class="btn btn-icon btn-warning" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                           <i class="fas fa-pencil fs-4"></i>
                      </button>
                      <ul class="dropdown-menu">
                        <li><button class="dropdown-item text-warning coursetype-edit">Kemaskini</button></li>
                        <li><button class="dropdown-item text-danger coursetype-delete">Padam</button></li>
                      </ul>
                    </div>`;

            }
        }
    ]
})
table.setSearchButton('#coursetype-list-search').setupChangePage('#coursetype-prev', '#coursetype-next');
table.run();

function resetCourseTypeForm(){
    common.resetForm([
        ['#name', 'string'],
    ])

    common.setFormValue('#coursetype-id', '', 'string');
}
