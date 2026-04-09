@php use Illuminate\Support\Facades\Auth; @endphp
<div class="row gx-5 gx-xl-10 mb-xl-10">
    <div class="col-md-12">
        <div class="card shadow-sm">
            <div class="card-header">
                <h3 class="card-title">Perisytiharan Harta</h3>

                @if(Auth::user()->hasRole('super-admin|admin'))
                    <div class="card-toolbar">
                        <a href="{{ route('admin.user.list') }}" class="btn btn-sm btn-danger">
                            Kembali Ke Senarai Pengguna
                        </a>
                    </div>
                @endif
            </div>

            <div class="card-body">
                <div class="table-responsive">

                    <!-- BUTTON TAMBAH -->
                    <div class="float-start mb-4">
                        <button class="btn btn-success btn-sm" id="harta-add">
    <i class="fas fa-add fs-4 pe-0"></i>
</button>
                    </div>

                    <!-- TABLE -->
                    <table class="table table-bordered text-center align-middle" id="harta-list">
                        <thead>
                        <tr class="fw-bold fs-6 text-gray-800">
                            <th>Jenis Harta</th>
                            <th>Keterangan</th>
                            <th>Nilai (RM)</th>
                            <th>Tahun Perolehan</th>
                            <th>Tindakan</th>
                        </tr>
                        </thead>
                        <tbody></tbody>
                    </table>

                    <!-- PAGINATION -->
                    <ul class="pagination">
                        <li class="page-item">
                            <button class="page-link" id="harta-prev">Previous</button>
                        </li>
                        <li class="page-item">
                            <button class="page-link" id="harta-next">Next</button>
                        </li>
                    </ul>

                </div>
            </div>
        </div>
    </div>
</div>