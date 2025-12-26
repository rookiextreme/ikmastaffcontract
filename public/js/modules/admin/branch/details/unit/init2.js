let unitModal = new Modals({selector: '#unit-modal'});

let branchUnitData = common.getForm(false);
branchUnitData.append('branch_id', branch_id);

let table = new DatatableInit({
    url: `${common.getUrl()}${moduleUrl}unit-list`,
    method: 'POST',
    selector: '#unit-list',
    data: branchUnitData,
    prev: '#unit-prev',
    next: '#unit-next',
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
                        <li><button class="dropdown-item text-warning unit-edit">Kemaskini</button></li>
                        <li><button class="dropdown-item text-danger unit-delete">Padam</button></li>
                      </ul>
                    </div>`;

            }
        }
    ]
})
table.setSearchButton('#unit-list-search').setupChangePage('#unit-prev', '#unit-next');
table.run();

function resetUnitForm(){
    common.resetForm([
        ['#unit-name', 'string'],
    ])

    common.setFormValue('#unit-id', '', 'string');
}
