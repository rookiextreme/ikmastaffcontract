let coursecategoryModal = new Modals({selector: '#coursecategory-modal'});

let table = new DatatableInit({
    url: `${common.getUrl()}${moduleUrl}list`,
    method: 'POST',
    selector: '#coursecategory-list',
    prev: '#coursecategory-prev',
    next: '#coursecategory-next',
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
                        <li><button class="dropdown-item text-warning coursecategory-edit">Kemaskini</button></li>
                        <li><button class="dropdown-item text-danger coursecategory-delete">Padam</button></li>
                      </ul>
                    </div>`;

            }
        }
    ]
})
table.setSearchButton('#coursecategory-list-search').setupChangePage('#coursecategory-prev', '#coursecategory-next');
table.run();

function resetCourseCategoryForm(){
    common.resetForm([
        ['#name', 'string'],
    ])

    common.setFormValue('#coursecategory-id', '', 'string');
}
