@extends('layouts.backend.master')

@section('title')
    Papan Pemuka Anda
@endsection

@section('cssExtensions')
    <link href="{{ asset('templates/backend/assets/plugins/global/plugins.bundle.css') }}" rel="stylesheet" type="text/css"/>

@endsection

@section('content')
    <div class="row gx-5 gx-xl-10 mb-xl-10">
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <label class="form-label">Tahun</label>
                    <select id="yearSelect" name="year" class="form-select">
                        <?php
                        $nowYear = date('Y');
                        $minYear = 2024;
                        for ($year = $nowYear; $year >= $minYear; $year--) {
                            echo "<option value='{$year}' ".($currentYear == $year ? 'selected' : '').">{$year}</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>
        </div>
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h3 class="card-title">Jumlah Staf Mengikut Negeri</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <canvas id="staff-by-state" class="mh-400px"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h3 class="card-title">Bilangan Cuti Mengikut Kategori</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <canvas id="leave-by-category" class="mh-400px"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h3 class="card-title">Bilangan Staf Mengikut Tahap Pendidikan</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <canvas id="staff-by-academic" class="mh-400px"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('jsExtensions')
    <script src="{{ asset('js/custom/datatable-helper.js') }}"></script>
    <script src="{{ asset('js/custom/modals.js') }}"></script>
    <script src="{{ asset('templates/backend/assets/plugins/global/plugins.bundle.js') }}"></script>
    <script src="{{ asset('templates/backend/assets/js/scripts.bundle.js') }}"></script>
@endsection

@section('jsCustom')
    <script>
        let moduleUrl = `admin/branch/`;
    </script>

   <script>
       var staffByState = document.getElementById('staff-by-state');

       // Define colors
       var primaryColor = KTUtil.getCssVariableValue('--kt-primary');
       var dangerColor = KTUtil.getCssVariableValue('--kt-danger');
       var successColor = KTUtil.getCssVariableValue('--kt-success');

       // Define fonts
       var fontFamily = KTUtil.getCssVariableValue('--bs-font-sans-serif');

       // Chart labels
       const labels = @json($statesByName);

       // Chart data
       const data = {
           labels: labels,
           datasets: [
               {
                   label: 'Jumlah Staf',
                   data: @json($staffStateCount)
               }
           ]
       };

       // Chart config
       const config = {
           type: 'bar',
           data: data,
           options: {
               plugins: {
                   title: {
                       display: false,
                   }
               },
               responsive: true,
               interaction: {
                   intersect: false,
               },
               scales: {
                   x: {
                       stacked: true,
                   },
                   y: {
                       stacked: true
                   }
               }
           },
           defaults:{
               global: {
                   defaultFont: fontFamily
               }
           }
       };

       var staffByStateChart = new Chart(staffByState, config);

       var leaveByCategory = document.getElementById('leave-by-category');

       const lCLabels = ['Cuti Rehat', 'Kebenaran Keluar Pejabat', 'Cuti Sakit'];

       // Chart data
       const lcData = {
           labels: lCLabels,
           datasets: [
               {
                   data: @json($staffLeaveByCategoryCount),
                   backgroundColor: [
                       'rgb(255, 99, 132)',
                       'rgb(54, 162, 235)',
                       'rgb(255, 205, 86)'
                   ],
                   hoverOffset: 4
               }
           ]
       };

       var leaveCategoryConfig = {
           type: 'pie',
           data: lcData,
           options: {
               plugins: {
                   title: {
                       display: false,
                   }
               },
               responsive: true,
           },
           defaults:{
               global: {
                   defaultFont: fontFamily
               }
           }
       };
       var leaveByCategoryChart = new Chart(leaveByCategory, leaveCategoryConfig);

       var staffAcademic = document.getElementById('staff-by-academic');

       const saLabels = @json($academicByName);

       // Chart data
       const saData = {
           labels: saLabels,
           datasets: [
               {
                   data: @json($staffByAcademic),
                   backgroundColor: [
                       'rgb(255, 99, 132)',   // red
                       'rgb(255, 159, 64)',   // orange
                       'rgb(255, 205, 86)',   // yellow
                       'rgb(75, 192, 192)',   // teal
                       'rgb(54, 162, 235)',   // blue
                       'rgb(153, 102, 255)',  // purple
                       'rgb(201, 203, 207)'   // gray
                   ],
                   hoverOffset: 4
               }
           ]
       };

       var staffAcademicConfig = {
           type: 'doughnut',
           data: saData,
           options: {
               plugins: {
                   title: {
                       display: false,
                   }
               },
               responsive: true,
           },
           defaults:{
               global: {
                   defaultFont: fontFamily
               }
           }
       };
       var staffAcademicChart = new Chart(staffAcademic, staffAcademicConfig);

       $('#yearSelect').on('change', function(){
           window.location.href = common.getUrl() + 'admin/dashboard?year=' + $(this).val()
       })
   </script>
@endsection
