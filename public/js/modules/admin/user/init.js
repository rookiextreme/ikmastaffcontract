let userModal = new Modals({selector: '#user-modal'});

let table = new DatatableInit({
    url: `${common.getUrl()}${moduleUrl}user-list`,
    method: 'POST',
    selector: '#user-list',
    prev: '#user-prev',
    next: '#user-next',
    columns: [
        {
            data: 'name'
        },
        {
            data: 'email'
        },
        {
            data: 'role'
        },
        {
            data: 'action',
            raw: function (full) {
                let btn = '';

                if(full.role_name == 'peserta'){
                    btn = `<li><a href="${common.getUrl()}participant/application/${full.id}/profile" class="dropdown-item text-info">Profil</a></li>`
                }

                btn = `<div class="dropdown">
                      <button class="btn btn-icon btn-warning" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                           <i class="fas fa-pencil fs-4"></i>
                      </button>
                      <ul class="dropdown-menu">
                        ${btn}
                        <li><button class="dropdown-item text-danger user-edit">Kemaskini</button></li>
                      </ul>
                    </div>`

                return `${btn}`;

            }
        }
    ]
})
table.setSearchButton('#user-list-search').setupChangePage('#user-prev', '#user-next');
table.run();

function resetUserForm(){
    common.resetForm([
        ['#name', 'string'],
        ['#email', 'string'],
        ['#role', 'dropdown'],
        ['#identification_no', 'string'],
    ])

    common.setFormValue('#user-id', '', 'string');
}
