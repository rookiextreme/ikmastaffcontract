let courseModal = new Modals({selector: '#course-modal'});
let table = new DatatableInit({
    url: `${common.getUrl()}${moduleUrl}course-list`,
    method: 'POST',
    selector: '#course-list',
    prev: '#course-prev',
    next: '#course-next',
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
                        <li><a href="${common.getUrl()}${moduleUrl}${full.id}/session" class="dropdown-item text-info">Tarikh Setup</a></li>
                        <li><button class="dropdown-item text-warning course-edit">Kemaskini</button></li>
                        <li><button class="dropdown-item text-danger course-delete">Delete</button></li>
                      </ul>
                    </div>`;

            }
        }
    ]
})
table.setSearchButton('#course-list-search').setupChangePage('#course-prev', '#course-next');
table.run();

function resetCourseForm(){
    common.resetForm([
        ['#name', 'string'],
    ])

    common.setFormValue('#course-id', '', 'string');
}
