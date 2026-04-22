// =======================================================
//  position.js — INIT (STABIL & SELAMAT)
// =======================================================

// ✅ Modal pengurusan jawatan
let positionModal = new Modals({ selector: '#position-modal' });

// ✅ Data asas untuk table
let branchPositionData = common.getForm(false);
branchPositionData.append('branch_id', branch_id);

// =======================================================
//  Datatable
// =======================================================
let table = new DatatableInit({
    url: `${common.getUrl()}${moduleUrl}position-list`,
    method: 'POST',
    selector: '#position-list',
    data: branchPositionData,
    prev: '#position-prev',
    next: '#position-next',
    columns: [
        { data: 'position' },
        { data: 'grade' },
        { data: 'unit' },
        { data: 'holiday' },
        {
            data: 'action',
            raw: function (full) {
                return `
                    <div class="dropdown">
                        <button class="btn btn-icon btn-warning" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-pencil fs-4"></i>
                        </button>
                        <ul class="dropdown-menu">
                            <li>
                                <button class="dropdown-item text-warning position-edit" data-id="${full.id}">
                                    Kemaskini
                                </button>
                            </li>
                            <li>
                                <button class="dropdown-item text-danger position-delete" data-id="${full.id}">
                                    Padam
                                </button>
                            </li>
                        </ul>
                    </div>
                `;
            }
        }
    ]
});

table.setSearchButton('#position-list-search')
     .setupChangePage('#position-prev', '#position-next');
table.run();


// =======================================================
//  RESET FORM (SAFE – TAK AKAN CRASH)
// =======================================================
function resetPositionForm() {

    // reset field asal - JANGAN ubah field lain
    common.resetForm([
        ['#position-name', 'dropdown'],
        ['#position-grade', 'dropdown'],
        ['#position-holiday', 'string']
    ]);

    common.setFormValue('#position-id', '', 'string');

    // reset UNIT secara manual sahaja
    $('#position-unit').val('');

    // buang tanda hijau / merah pada unit
    $('#position-unit').removeClass('is-valid is-invalid');

    // kosongkan mesej validation unit
    $('#position-unit').closest('.vals-row').find('.invalid-feedback').html('');
}
