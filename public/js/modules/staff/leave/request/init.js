let tableData = common.getForm(false)
tableData.append('user_id', user_id)

let table = new DatatableInit({
    url: `${common.getUrl()}${moduleUrl}request-list`,
    method: 'POST',
    selector: '#request-list',
    data: tableData,
    prev: '#request-prev',
    next: '#request-next',
    columns: [
        {
            data: 'start'
        },
        {
            data: 'end'
        },
        {
            data: 'days'
        },
        {
            data: 'status'
        },
        {
            data: 'action',
            raw: function (full) {
                btn = `<div class="dropdown">
                      <button class="btn btn-icon btn-warning" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                           <i class="fas fa-pencil fs-4"></i>
                      </button>
                      <ul class="dropdown-menu">
                        <li><button class="dropdown-item text-danger request-delete">Padam</button></li>
                      </ul>
                    </div>`
                return `${btn}`;
            }
        }
    ]
})
table.setSearchButton('#request-list-search').setupChangePage('#request-prev', '#request-next');
table.run();
