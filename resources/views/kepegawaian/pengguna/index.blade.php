<x-app-layout>
    @push('plugins_css')
        <link href="https://cdn.datatables.net/1.10.21/css/dataTables.bootstrap4.min.css" rel="stylesheet">
    @endpush

    @push('page_css')
        <style>
            .dataTables_length {
                padding: 0.5rem 0 0 0.5rem;
            }
            .dataTables_filter {
                padding: 0.5rem 0.5rem 0 0;
            }
            .dataTables_paginate {
                padding: 0.5rem;
            }
            .dataTables_info {
                padding-left: 0.5rem;
            }
        </style>
    @endpush

    @section('title', $title)
    <x-slot name="header">
        <h1>{{ $title }}</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{ route('dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item">{{ $title }}</div>
        </div>
    </x-slot>

    <div class="section-body">
        <h2 class="section-title">{{ $title }}</h2>
        <p class="section-lead">Kelola akun pengguna, role, status aktif, dan hubungan akun dengan pegawai.</p>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Daftar {{ $title }}</h4>
                        <div class="card-header-action">
                            <a href="{{ route('kepegawaian.pengguna.create') }}" class="btn btn-icon icon-left btn-primary">
                                <i class="fas fa-plus"></i> Tambah {{ $title }}
                            </a>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped" id="tbl">
                                <thead class="table-dark">
                                    <tr>
                                        <th>#</th>
                                        <th>Nama / Email</th>
                                        <th>Pegawai</th>
                                        <th>Peran</th>
                                        <th>Status</th>
                                        <th class="table-actions-col">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

        @push('plugins_js')
            <script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
            <script src="https://cdn.datatables.net/1.10.21/js/dataTables.bootstrap4.min.js"></script>
        @endpush

        @push('page_js')
            <script type="text/javascript">
                $(document).ready(function () {
                    $('#tbl').DataTable({
                        processing: true,
                        serverSide: true,
                        ajax: '{{ url()->current() }}',
                        columns: [
                            {data: 'DT_RowIndex', name: 'DT_RowIndex'},
                            {data: 'identity', name: 'name'},
                            {data: 'pegawai', name: 'pegawai'},
                            {data: 'roles', name: 'roles'},
                            {data: 'status', name: 'status'},
                            {data: 'action', name: 'action', orderable: false, searchable: false, className: 'table-actions-cell'},
                        ],
                        language: {
                            emptyTable: 'Belum ada data.',
                            zeroRecords: 'Belum ada data.'
                        }
                    });
                });
            </script>
        @endpush
</x-app-layout>



