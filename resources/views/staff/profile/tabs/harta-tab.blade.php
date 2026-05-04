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

                    <div class="float-start mb-4">
                        <button class="btn btn-success btn-sm" id="harta-add">
                            <i class="fas fa-add fs-4 pe-0"></i>
                        </button>

                        <!-- ✅ BUTTON HANTAR -->
                        <button class="btn btn-primary btn-sm ms-2" id="harta-submit">
                            <i class="fas fa-paper-plane fs-4 pe-0"></i>
                        </button>

                        <!-- ✅ TAMBAH BUTTON ADMIN -->
                        @if(Auth::user()->hasRole('super-admin|admin'))
                            <button class="btn btn-success btn-sm ms-2" id="harta-approve">
                                <i class="fas fa-check fs-4 pe-0"></i>
                            </button>

                            <button class="btn btn-warning btn-sm ms-2" id="harta-return">
                                <i class="fas fa-undo fs-4 pe-0"></i>
                            </button>
                        @endif
                    </div>

                    <table class="table table-bordered text-center align-middle" id="harta-list">
                        <thead>
                        <tr class="fw-bold fs-6 text-gray-800">
                            <th>Pemilik Harta</th>
                            <th>Jenis Harta</th>
                            <th>Keterangan</th>
                            <th>Nilai (RM)</th>
                            <th>Tarikh Pemilikan</th>
                            <th>Pelupusan</th>
                            <th>Terkini?</th>

                            <!-- ✅ COLUMN STATUS -->
                            <th>Status Perisytiharan</th>

                            <th>Tindakan</th>
                        </tr>
                        </thead>
                        <tbody></tbody>
                    </table>

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