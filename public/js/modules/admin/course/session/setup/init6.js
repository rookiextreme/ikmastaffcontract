$("#session-start-date").flatpickr({
    dateFormat: "d-m-Y",
});

$("#session-end-date").flatpickr({
    dateFormat: "d-m-Y",
});

var quill = new Quill('#session-description', {
    modules: {
        toolbar: [
            [{
                header: [1, 2, false]
            }],
            ['bold', 'italic', 'underline'],
            ['image', 'code-block']
        ]
    },
    placeholder: 'Masukkan Deskripsi',
    theme: 'snow' // or 'bubble'
});

var emailBlastTemplate = new Quill('#session-email-blast-template', {
    modules: {
        toolbar: [
            [{
                header: [1, 2, false]
            }],
            ['bold', 'italic', 'underline'],
            ['image', 'code-block']
        ]
    },
    placeholder: 'Masukkan Konten',
    theme: 'snow' // or 'bubble'
});

let table = new DatatableInit({
    url: `${common.getUrl()}${moduleUrl}suk-list`,
    method: 'POST',
    selector: '#suk-list',
    prev: '#suk-prev',
    next: '#suk-next',
    columns: [
        {
            data: 'name'
        },
        {
            data: 'identification_no'
        },
        {
            data: 'action',
            raw: function (full) {
                let btn = '';
                let suk_id = full.suk_id;
                if(suk_id){
                    btn = ` <button class="btn btn-icon btn-danger unpick-suk" type="button" aria-expanded="false">
                           <i class="fas fa-x fs-4"></i>
                      </button>`
                }else{
                    btn = ` <button class="btn btn-icon btn-success pick-suk" type="button" aria-expanded="false">
                           <i class="fas fa-check fs-4"></i>
                      </button>`
                }
                return `<div class="dropdown">
                      ${btn}
                    </div>`;

            }
        }
    ]
})
table.setSearchButton('#suk-list-search').setupChangePage('#suk-prev', '#suk-next');
table.run();

let participantTable = new DatatableInit({
    url: `${common.getUrl()}admin/course/registration/registration-list?session_id=${session_id}`,
    method: 'POST',
    selector: '#registration-list',
    prev: '#registration-prev',
    next: '#registration-next',
    columns: [
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
                        <li><a href="${common.getUrl()}participant/application/${full.user_id}/profile?session_id=${session_id}" class="dropdown-item text-success">Profil</a></li>
                        <li><a href="${common.getUrl()}participant/payment/${full.id}/pay?session_id=${session_id}" class="dropdown-item text-info">Invois</a></li>
                      </ul>
                    </div>`;

            }
        }
    ]
})
participantTable.setSearchButton('#registration-list-search').setupChangePage('#registration-prev', '#registration-next');
participantTable.run();

$('#koperasi-check').on('change', function(){
    $('#koperasi').attr('disabled', $(this).prop('checked') == true ? true : false).val(0);
})

$('#agensi-check').on('change', function(){
    $('#agensi').attr('disabled', $(this).prop('checked') == true ? true : false).val(0);
})

$('#usahawan-check').on('change', function(){
    $('#usahawan').attr('disabled', $(this).prop('checked') == true ? true : false).val(0);
})

$('#individu-check').on('change', function(){
    $('#individu').attr('disabled', $(this).prop('checked') == true ? true : false).val(0);
})
