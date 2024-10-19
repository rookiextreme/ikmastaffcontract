let coursetaggingModal = new Modals({selector: '#coursetagging-modal'});

let table = new DatatableInit({
    url: `${common.getUrl()}${moduleUrl}list`,
    method: 'POST',
    selector: '#coursetagging-list',
    prev: '#coursetagging-prev',
    next: '#coursetagging-next',
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
                        <li><button class="dropdown-item text-warning coursetagging-edit">Kemaskini</button></li>
                        <li><button class="dropdown-item text-danger coursetagging-delete">Padam</button></li>
                      </ul>
                    </div>`;

            }
        }
    ]
})
table.setSearchButton('#coursetagging-list-search').setupChangePage('#coursetagging-prev', '#coursetagging-next');
table.run();

function resetCourseTaggingForm(){
    common.resetForm([
        ['#name', 'string'],
    ])

    common.setFormValue('#coursetagging-id', '', 'string');
}
