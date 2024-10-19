let table = new DatatableInit({
    url: `${common.getUrl()}${moduleUrl}registration-list`,
    method: 'POST',
    selector: '#registration-list',
    prev: '#registration-prev',
    next: '#registration-next',
    columns: [
        {
            data: 'course_name'
        },
        {
            data: 'name'
        },
        {
            data: 'amount'
        },
        {
            data: 'status'
        },
        {
            data: 'action',
            raw: function (full) {
                return `<div class="dropdown">
                      <button class="btn btn-icon btn-warning" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                           <i class="fas fa-pencil fs-4"></i>
                      </button>
                      <ul class="dropdown-menu">
                        <li><a href="${common.getUrl()}participant/payment/${full.id}/pay" class="dropdown-item text-info">Papar</a></li>
                      </ul>
                    </div>`;

            }
        }
    ]
})
table.setSearchButton('#registration-list-search').setupChangePage('#registration-prev', '#registration-next');
table.run();
