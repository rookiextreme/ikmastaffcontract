@php use Illuminate\Support\Facades\Auth; @endphp
<div class="row gx-5 gx-xl-10 mb-xl-10">
    <div class="col-md-12">
        <div class="card shadow-sm">
            <div class="card-header">
                <h3 class="card-title">Sejarah Jawatan</h3>
                @if(Auth::user()->hasRole('super-admin|admin'))
                    <div class="card-toolbar">
                        <a href="{{ route('admin.user.list') }}" class="btn btn-sm btn-danger">
                            Kembali Ke Senarai Pengguna
                        </a>
                    </div>
                @endif
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="table-responsive">
                                <div class="float-start mb-4">
                                    <button class="btn btn-success" id="academic-add"><i
                                            class="fas fa-add fs-4 pe-0"></i>
                                    </button>
                                </div>
                                <table class="table table-bordered text-center align-middle" id="academic-list">
                                    <thead>
                                    <tr class="fw-bold fs-6 text-gray-800" style="text-align:center">
                                        <th style="width: 30%">Penempatan</th>
                                        <th style="width: 20%">Jawatan</th>
                                        <th style="width: 20%">Terkini?</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @if(count($staff->getStaffPositionHistory) > 0)
                                        @php
                                            $x = 0;
                                        @endphp
                                        @foreach($staff->getStaffPositionHistory as $gsp)
                                            <tr>
                                                <td>
                                                    {{ $gsp->getBranch->name }}
                                                </td>
                                                <td>
                                                    {{ $gsp->getBranchPosition->getPosition->name }}<br>
                                                    {{ $gsp->getBranchPosition->getGrade->name }}
                                                </td>
                                                <td>
                                                    @if($x == 0)
                                                        <span class="text-success">Ya</span>
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                            </tr>
                                            @php
                                                $x++;
                                            @endphp
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="3" class="text-center">
                                                Tiada Sejarah Jawatan
                                            </td>
                                        </tr>
                                    @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
