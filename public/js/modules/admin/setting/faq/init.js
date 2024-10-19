let faqModal = new Modals({selector: '#faq-modal'});

let table = new DatatableInit({
    url: `${common.getUrl()}${moduleUrl}list`,
    method: 'POST',
    selector: '#faq-list',
    prev: '#faq-prev',
    next: '#faq-next',
    columns: [
        {
            data: 'question'
        },
        {
            data: 'answer'
        },
        {
            data: 'action',
            raw: function (full) {
                return `<div class="dropdown">
                      <button class="btn btn-icon btn-warning" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                           <i class="fas fa-pencil fs-4"></i>
                      </button>
                      <ul class="dropdown-menu">
                        <li><button class="dropdown-item text-warning faq-edit">Kemaskini</button></li>
                        <li><button class="dropdown-item text-danger faq-delete">Padam</button></li>
                      </ul>
                    </div>`;

            }
        }
    ]
})
table.setSearchButton('#faq-list-search').setupChangePage('#faq-prev', '#faq-next');
table.run();

function resetFaqForm(){
    common.resetForm([
        ['#question', 'string'],
        ['#answer', 'string'],
    ])

    common.setFormValue('#faq-id', '', 'string');
}
