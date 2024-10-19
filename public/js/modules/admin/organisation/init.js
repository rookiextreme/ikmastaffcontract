let organisationModal = new Modals({selector: '#organisation-modal'});

let tableData = common.getForm(false)
tableData.append('type', type)
tableData.append('organisation_type_id', organisation_type_id)

let table = new DatatableInit({
    url: `${common.getUrl()}${moduleUrl}list`,
    method: 'POST',
    data: tableData,
    selector: '#organisation-list',
    prev: '#organisation-prev',
    next: '#organisation-next',
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
                        <li><button class="dropdown-item text-warning organisation-edit">Kemaskini</button></li>
                        <li><button class="dropdown-item text-danger organisation-delete">Padam</button></li>
                      </ul>
                    </div>`;

            }
        }
    ]
})
table.setSearchButton('#organisation-list-search').setupChangePage('#organisation-prev', '#organisation-next');
table.run();

function resetOrganisationForm(){
    common.resetForm([
        ['#sub-type', 'dropdown'],
        ['#name', 'string'],
        ['#telephone', 'string'],
        ['#fax', 'string'],
        ['#address', 'string'],
        ['#postcode', 'string'],
        ['#state', 'dropdown'],
        ['#location-code', 'string'],
        ['#city', 'string'],
        ['#worker', 'string'],
        ['#website', 'string'],
    ])

    common.setFormValue('#organisation-id', '', 'string');
}
