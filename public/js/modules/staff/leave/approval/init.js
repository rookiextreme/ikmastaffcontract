let tableData = common.getForm(false)
tableData.append('user_id', user_id)
tableData.append('is_super', is_super)
tableData.append('is_admin', is_admin)
tableData.append('is_approval', is_approval)
tableData.append('is_staff', is_staff)

let table = new DatatableInit({
    url: `${common.getUrl()}${moduleUrl}approval-list`,
    method: 'POST',
    selector: '#approval-list',
    data: tableData,
    prev: '#approval-prev',
    next: '#approval-next',
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
                let status_id = full.status_id;
                let btn = '-';

                if(status_id == 1){
                    btn = `
                            <button class="btn btn-icon btn-success approval-approve" data-approve="1" type="button" aria-expanded="false">
                               <i class="fas fa-check fs-4"></i>
                          </button>
                          <button class="btn btn-icon btn-danger approval-approve" data-approve="2" type="button" aria-expanded="false">
                               <i class="fas fa-xmark fs-4"></i>
                          </button>
                        `
                }
                return btn;
            }
        }
    ]
})
table.setSearchButton('#approval-list-search').setupChangePage('#approval-prev', '#approval-next');
table.run();
