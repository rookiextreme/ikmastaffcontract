@extends('layouts.backend.master')

@section('title')
    Senarai Permohonan Cuti
@endsection

@section('content')
    <div class="row gx-5 gx-xl-10 mb-xl-10">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h3 class="card-title">Senarai Permohonan Cuti</h3>
                    <div class="card-toolbar">
                        <a href="{{ route('staff.leave.new-request', ['user_id' => $user_id]) }}" type="button" class="btn btn-sm btn-success">
                            Tambah Permohonan Baru
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="table-responsive">
                                <div class="search float-end mb-4">
                                    <input id="request-list-search" class="form-control" value="" style="outline: none"
                                           placeholder="Search..">
                                </div>
                                <table class="table table-bordered text-center align-middle" id="request-list">
                                    <thead>
                                    <tr class="fw-bold fs-6 text-gray-800" style="text-align:center">
                                        <th style="width: 15%">Tarikh Mula</th>
                                        <th style="width: 15%">Tarikh Akhir</th>
                                        <th style="width: 30%">Jumlah Hari<br>Tidak Termasuk Cuti Umum/Cuti Mingguan</th>
                                        <th style="width: 30%">Status</th>
                                        <th style="width: 10%">Tindakan</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                                <div>
                                    <ul class="pagination">
                                        <li class="page-item previous">
                                            <button class="page-link page-text" id="request-prev">Previous</button>
                                        </li>
                                        <li class="page-item next">
                                            <button class="page-link page-text" id="request-next">Next</button>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <input type="hidden" id="user-id" value="{{ $user_id }}">
@endsection

@section('jsExtensions')
    <script src="{{ asset('js/custom/datatable-helper.js') }}"></script>
    <script src="{{ asset('js/custom/modals.js') }}"></script>
    <script src="{{ asset('templates/backend/assets/js/scripts.bundle.js') }}"></script>
@endsection

@section('jsCustom')
    <script>
        let moduleUrl = `staff/leave/`;
        let user_id = $('#user-id').val();
    </script>

    <script src="{{ asset('js/modules/staff/leave/request/init.js') }}"></script>
    <script src="{{ asset('js/modules/staff/leave/request/index.js') }}"></script>
@endsection
