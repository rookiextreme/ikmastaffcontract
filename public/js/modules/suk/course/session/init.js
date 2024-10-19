let sessionFormData = common.getForm();
sessionFormData.append('user_id', user_id)

let table = new DatatableInit({
    url: `${common.getUrl()}${moduleUrl}list`,
    method: 'POST',
    data: sessionFormData,
    selector: '#suksession-list',
    prev: '#suksession-prev',
    next: '#suksession-next',
    columns: [
        {
            data: 'course'
        },
        {
            data: 'date_start'
        },
        {
            data: 'participant_count'
        },
        {
            data: 'action',
            raw: function (full) {
                return `<div class="dropdown">
                      <button class="btn btn-icon btn-warning" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                           <i class="fas fa-pencil fs-4"></i>
                      </button>
                      <ul class="dropdown-menu">
                        <li><a href="${common.getUrl()}admin/course/${full.id}/setup" class="dropdown-item text-warning">Papar</a></li>
                      </ul>
                    </div>`;

            }
        }
    ]
})
table.setSearchButton('#suksession-list-search').setupChangePage('#suksession-prev', '#suksession-next');
table.run();
