let table = new DatatableInit({
    url: `${common.getUrl()}${moduleUrl}${user_id}/course-history-list`,
    method: 'POST',
    selector: '#history-list',
    prev: '#history-prev',
    next: '#history-next',
    columns: [
        {
            data: 'course_name'
        },
        {
            data: 'date_start'
        },
        {
            data: 'position_registered'
        },
        {
            data: 'status'
        },
        {
            data: 'amount'
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
table.setSearchButton('#history-list-search').setupChangePage('#history-prev', '#history-next');
table.run();
