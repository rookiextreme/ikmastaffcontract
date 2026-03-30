@php
    $e = $evaluation;
    $data  = (array)($e->skt_bahagian_i ?? []);
    $items = (array)($data['items'] ?? []);

    // minimum 1 row
    if(count($items) < 1){
        $items = [['aktiviti'=>'','petunjuk'=>'']];
    }

    $readonly = ($roleKey === 'ppp') || $is_locked;

    // check lengkap
    $filledI = 0; $okI = true;

    foreach($items as $row){
        $a = trim((string)($row['aktiviti'] ?? ''));
        $p = trim((string)($row['petunjuk'] ?? ''));

        if($a==='' && $p==='') continue;

        $filledI++;

        if($a==='' || $p===''){
            $okI=false;
            break;
        }
    }

    $iComplete = ($filledI>0) && $okI;

    $canEdit = ($roleKey === 'pyd') && !$readonly;

    $itemsDisplay = array_values(array_filter($items,function($row){
        $a = trim((string)($row['aktiviti'] ?? ''));
        $p = trim((string)($row['petunjuk'] ?? ''));
        return $a !== '' || $p !== '';
    }));
@endphp


<style>

.skt-readonly-box{
    min-height:60px;
    background:#f8f9fb;
    border:1px solid #e9ecef;
    border-radius:10px;
    padding:12px 14px;
    color:#3f4254;
    line-height:1.6;

    display:flex;
    align-items:flex-start;
    justify-content:flex-start;
    text-align:left;
}

.skt-readonly-box div{
    width:100%;
    text-align:left;
    margin:0;
}

.skt-section-note{
    font-size:0.85rem;
    color:#7e8299;
    margin-top:6px;
}

.skt-section-block + .skt-section-block{
    margin-top:1.5rem;
    padding-top:1.5rem;
    border-top:1px solid #eff2f5;
}

</style>



<div class="card border mb-6">

<div class="card-header">

<h4 class="card-title mb-0">
BAHAGIAN I - Penetapan Sasaran Kerja Tahunan
</h4>

</div>


<div class="card-body">

<div class="fst-italic small mb-4">
(PYD dan PPP hendaklah berbincang bersama sebelum menetapkan SKT dan petunjuk prestasinya)
</div>


@if($roleKey === 'admin' || $roleKey === 'ppp')

{{-- =========================
   PAPARAN ADMIN / PPP
========================= --}}

@if(count($itemsDisplay) > 0)

@foreach($itemsDisplay as $i => $row)

<div class="mb-4 skt-section-block">

<div class="row">

<div class="col-md-6">

<label class="form-label fw-semibold">
{{ $i + 1 }}. Ringkasan Aktiviti / Projek
</label>

<div class="skt-readonly-box">
<div>
{!! nl2br(e(trim((string)($row['aktiviti'] ?? '')) !== '' ? $row['aktiviti'] : '-')) !!}
</div>
</div>

<div class="skt-section-note">
* Senaraikan aktiviti / projek.
</div>

</div>



<div class="col-md-6">

<label class="form-label fw-semibold">
Petunjuk Prestasi
</label>

<div class="skt-readonly-box">
<div>
{!! nl2br(e(trim((string)($row['petunjuk'] ?? '')) !== '' ? $row['petunjuk'] : '-')) !!}
</div>
</div>

<div class="skt-section-note">
* Kuantiti / Kualiti / Masa / Kos.
</div>

</div>

</div>

</div>

@endforeach

@else

<div class="text-muted small">
Tiada aktiviti / projek direkodkan.
</div>

@endif



@if(!$iComplete)

<div class="text-muted small mt-3">
* Lengkapkan sekurang-kurangnya 1 aktiviti dan pastikan Petunjuk Prestasi diisi.
</div>

@endif


<div class="skt-section-note">
* Paparan ini adalah read-only untuk {{ $roleKey === 'admin' ? 'Admin' : 'PPP' }}.
</div>



@else


{{-- =========================
   BORANG PYD
========================= --}}

<form method="POST" action="{{ route('staff.performance.skt.save') }}">

@csrf
<input type="hidden" name="bahagian" value="I">


<div class="table-responsive">

<table class="table table-bordered align-middle">

<thead class="table-light">

<tr>

<th style="width:60px;">Bil.</th>

<th>
Ringkasan Aktiviti / Projek
<br>
<span class="text-muted small">(Senaraikan aktiviti / projek)</span>
</th>

<th>
Petunjuk Prestasi
<br>
<span class="text-muted small">(Kuantiti / Kualiti / Masa / Kos)</span>
</th>

@if($canEdit)
<th style="width:70px;" class="text-center">Padam</th>
@endif

</tr>

</thead>


<tbody id="skt-bahagian1-wrapper">

@foreach($items as $i => $row)

<tr class="skt-row">

<td class="text-center skt-bil">
{{ $i+1 }}
</td>

<td>

<textarea
class="form-control"
rows="2"
name="skt_bahagian_i[items][{{ $i }}][aktiviti]"
{{ $readonly ? 'readonly' : '' }}
>{{ $row['aktiviti'] ?? '' }}</textarea>

</td>


<td>

<textarea
class="form-control"
rows="2"
name="skt_bahagian_i[items][{{ $i }}][petunjuk]"
{{ $readonly ? 'readonly' : '' }}
>{{ $row['petunjuk'] ?? '' }}</textarea>

</td>


@if($canEdit)

<td class="text-center">

<button
type="button"
class="btn btn-sm btn-light-danger skt-remove"
onclick="deleteRowSKT(this, 'skt-bahagian1-wrapper')"
title="Padam baris">

🗑

</button>

</td>

@endif

</tr>

@endforeach

</tbody>

</table>

</div>



@if($roleKey === 'pyd')

<div class="mt-4 d-flex gap-2 align-items-center">

<button
type="submit"
class="btn btn-primary"
{{ $is_locked ? 'disabled' : '' }}
>
Simpan
</button>


@if($canEdit)

<button
type="button"
class="btn btn-light-primary"
onclick="addRowSKT('skt-bahagian1-wrapper', sktBahagian1Row)"
>
+ Tambah Baris
</button>

@endif

</div>

@endif



@if(!$iComplete)

<div class="text-muted small mt-3">
* Lengkapkan sekurang-kurangnya 1 aktiviti dan pastikan Petunjuk Prestasi diisi.
</div>

@endif

</form>

@endif


</div>

</div>



@if($canEdit)

<script>

function addRowSKT(wrapperId,rowHtml){

document
.getElementById(wrapperId)
.insertAdjacentHTML('beforeend',rowHtml());

reindexSKT(wrapperId);

}



function deleteRowSKT(btn,wrapperId){

const tbody=document.getElementById(wrapperId);

const rows=tbody.querySelectorAll('tr.skt-row');


if(rows.length<=1){

rows[0].querySelectorAll('textarea')
.forEach(el=>el.value='');

return;

}

btn.closest('tr').remove();

reindexSKT(wrapperId);

}



function reindexSKT(wrapperId){

const tbody=document.getElementById(wrapperId);

const rows=tbody.querySelectorAll('tr.skt-row');

rows.forEach((tr,idx)=>{

const bil=tr.querySelector('.skt-bil');

if(bil) bil.textContent=idx+1;

tr.querySelectorAll('textarea[name^="skt_bahagian_i[items]"]')
.forEach(el=>{

el.name=el.name.replace(
/skt_bahagian_i\[items]\[\d+]/,
`skt_bahagian_i[items][${idx}]`
);

});

});

}



function sktBahagian1Row(){

return `
<tr class="skt-row">

<td class="text-center skt-bil">1</td>

<td>
<textarea class="form-control"
rows="2"
name="skt_bahagian_i[items][0][aktiviti]">
</textarea>
</td>

<td>
<textarea class="form-control"
rows="2"
name="skt_bahagian_i[items][0][petunjuk]">
</textarea>
</td>

<td class="text-center">
<button
type="button"
class="btn btn-sm btn-light-danger"
onclick="deleteRowSKT(this,'skt-bahagian1-wrapper')"
>
🗑
</button>
</td>

</tr>
`;

}



document.addEventListener('DOMContentLoaded',()=>{

reindexSKT('skt-bahagian1-wrapper');

});

</script>

@endif