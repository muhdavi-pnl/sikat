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
        <p class="section-lead">Kelola data pegawai, termasuk tambah, ubah, cetak, dan export data.</p>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Daftar {{ $title }}</h4>
                        <div class="card-header-action">
                            <a href="{{ route('kepegawaian.pegawai.print') }}" data-base-url="{{ route('kepegawaian.pegawai.print') }}" id="btn-print-pegawai" target="_blank" class="btn btn-icon icon-left btn-outline-dark mr-2">
                                <i class="fas fa-print"></i> Cetak
                            </a>
                            <a href="{{ route('kepegawaian.pegawai.export') }}" data-base-url="{{ route('kepegawaian.pegawai.export') }}" id="btn-export-pegawai" class="btn btn-icon icon-left btn-success mr-2">
                                <i class="fas fa-file-csv"></i> Export CSV
                            </a>
                            <a href="{{ route('kepegawaian.pegawai.create') }}" class="btn btn-icon icon-left btn-primary">
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
                                        <th>NIP</th>
                                        <th>Nama</th>
                                        <th>Jabatan</th>
                                        <th>Jurusan</th>
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
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.0/jquery.validate.js"></script>
        <script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.10.21/js/dataTables.bootstrap4.min.js"></script>
    @endpush

    @push('page_js')
        <script type="text/javascript">
            $(document).ready(function () {
                const tableElement = $('#tbl');

                const table = tableElement.DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: '{{ url()->current() }}',
                    columns: [
                        {data: 'DT_RowIndex', name: 'DT_RowIndex'},
                        {data: 'nip', name: 'nip'},
                        {data: 'nama', name: 'nama'},
                        {data: 'jabatan_fungsional', name: 'jabatan_fungsional'},
                        {data: 'jurusan', name: 'jurusan'},
                        {data: 'action', name: 'action', orderable: false, searchable: false, className: 'table-actions-cell'},
                    ],
                    language: {
                        emptyTable: 'Belum ada data.',
                        zeroRecords: 'Belum ada data.'
                    }
                });

                const printButton = $('#btn-print-pegawai');
                const exportButton = $('#btn-export-pegawai');

                const updateExportPrintUrls = function () {
                    const keyword = (table.search() || '').trim();

                    [printButton, exportButton].forEach(function (button) {
                        const baseUrl = button.data('base-url');
                        if (!baseUrl) {
                            return;
                        }

                        const url = new URL(baseUrl, window.location.origin);
                        if (keyword !== '') {
                            url.searchParams.set('q', keyword);
                        } else {
                            url.searchParams.delete('q');
                        }

                        button.attr('href', url.toString());
                    });
                };

                table.on('search.dt', updateExportPrintUrls);
                updateExportPrintUrls();
            });
        </script>
    @endpush
</x-app-layout>



