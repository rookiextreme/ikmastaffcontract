let tableData = common.getForm(false)
tableData.append('user_id', user_id)
tableData.append('is_super', is_super)
tableData.append('is_admin', is_admin)
tableData.append('is_approval', is_approval)
tableData.append('is_staff', is_staff)

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
                let status_id = full.status_id;

                let btn = '-';
                if(is_approval == 1){
                    if(status_id == 1){
                        btn = `
                            <button class="btn btn-icon btn-success request-approve" data-approve="1" type="button" aria-expanded="false">
                               <i class="fas fa-check fs-4"></i>
                          </button>
                          <button class="btn btn-icon btn-danger request-approve" data-approve="2" type="button" aria-expanded="false">
                               <i class="fas fa-xmark fs-4"></i>
                          </button>
                        `
                    }

                }else if(is_staff == 1){
                    if(status_id == 1) {
                        btn = `<div class="dropdown">
                          <button class="btn btn-icon btn-warning" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                               <i class="fas fa-pencil fs-4"></i>
                          </button>
                          <ul class="dropdown-menu">
                                ${btn}
                          </ul>
                        </div>`
                    }
                }
                return btn;
            }
        }
    ]
})
table.setSearchButton('#request-list-search').setupChangePage('#request-prev', '#request-next');
table.run();
