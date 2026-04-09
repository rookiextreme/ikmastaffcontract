let hartaModal = new Modals({selector: '#harta-modal'});

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
        { data: 'type' },
        { data: 'description' },
        { data: 'value' },
        { data: 'year' },
        {
            data: 'action',
            raw: function (full) {
    return `
    <div class="dropdown">
        <button class="btn btn-icon btn-warning" type="button" data-bs-toggle="dropdown" aria-expanded="false">
    <i class="fas fa-pencil fs-4"></i>
</button>
        <ul class="dropdown-menu">
            <li><button class="dropdown-item text-warning harta-edit">Kemaskini</button></li>
            <li><button class="dropdown-item text-danger harta-delete">Padam</button></li>
        </ul>
    </div>`;
}
        }
    ]
});

table.setupChangePage('#harta-prev', '#harta-next');
table.run();