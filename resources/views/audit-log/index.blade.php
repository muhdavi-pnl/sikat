<x-app-layout>
    <x-slot name="header">
        <div class="section-header-back">
            <a href="{{ route('dashboard') }}" class="btn btn-icon"><i class="fas fa-arrow-left"></i></a>
        </div>
        <h1>Log Audit & Keamanan Sistem</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{ route('dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item">Log Audit</div>
        </div>
    </x-slot>

    @push('plugins_css')
        <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
    @endpush

    <div class="section-body">
        <h2 class="section-title">Audit Trail & Rekaman Aktivitas</h2>
        <p class="section-lead">Pemantauan menyeluruh terhadap autentikasi, akses login, registrasi, dan setiap perubahan data dalam database.</p>

        <!-- KPI Statistic Cards -->
        <div class="row">
            <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-primary">
                        <i class="fas fa-history"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>Total Log Tercatat</h4>
                        </div>
                        <div class="card-body">
                            {{ number_format($stats['total']) }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-success">
                        <i class="fas fa-sign-in-alt"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>Login Berhasil Hari Ini</h4>
                        </div>
                        <div class="card-body">
                            {{ number_format($stats['successful_logins_today']) }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-danger">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>Gagal Login Hari Ini</h4>
                        </div>
                        <div class="card-body">
                            {{ number_format($stats['failed_logins_today']) }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-info">
                        <i class="fas fa-database"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>Perubahan DB Hari Ini</h4>
                        </div>
                        <div class="card-body">
                            {{ number_format($stats['db_modifications_today']) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Card -->
        <div class="card">
            <div class="card-header">
                <h4><i class="fas fa-filter mr-1 text-primary"></i> Filter Log Audit</h4>
                <div class="card-header-action">
                    <button class="btn btn-sm btn-outline-secondary" type="button" data-toggle="collapse" data-target="#filterCollapse" aria-expanded="true">
                        <i class="fas fa-sliders-h"></i> Buka/Tutup Filter
                    </button>
                </div>
            </div>
            <div class="collapse show" id="filterCollapse">
                <div class="card-body bg-light border-bottom">
                    <form id="filter-form">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label class="font-weight-600">Kategori</label>
                                <select class="form-control" id="filter-category" name="category">
                                    <option value="">Semua Kategori</option>
                                    <option value="auth">Autentikasi & Login</option>
                                    <option value="model">Modifikasi Database</option>
                                    <option value="security">Keamanan & Lockout</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="font-weight-600">Jenis Event</label>
                                <select class="form-control" id="filter-event-type" name="event_type">
                                    <option value="">Semua Event</option>
                                    @foreach($eventTypes as $type)
                                        <option value="{{ $type }}">{{ $type }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="font-weight-600">Status</label>
                                <select class="form-control" id="filter-status" name="status">
                                    <option value="">Semua Status</option>
                                    <option value="success">Success</option>
                                    <option value="failed">Failed</option>
                                    <option value="warning">Warning</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="font-weight-600">Pengguna</label>
                                <select class="form-control" id="filter-user" name="user_id">
                                    <option value="">Semua Pengguna</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="font-weight-600">IP Address</label>
                                <input type="text" class="form-control" id="filter-ip" name="ip_address" placeholder="Contoh: 127.0.0.1">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="font-weight-600">Dari Tanggal</label>
                                <input type="date" class="form-control" id="filter-date-start" name="date_start">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="font-weight-600">Sampai Tanggal</label>
                                <input type="date" class="form-control" id="filter-date-end" name="date_end">
                            </div>
                            <div class="col-md-3 mb-3 d-flex align-items-end">
                                <button type="button" id="btn-apply-filter" class="btn btn-primary mr-2 flex-grow-1">
                                    <i class="fas fa-search"></i> Terapkan
                                </button>
                                <button type="button" id="btn-reset-filter" class="btn btn-secondary">
                                    <i class="fas fa-undo"></i> Reset
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Audit Table Card -->
        <div class="card">
            <div class="card-header">
                <h4><i class="fas fa-list-alt mr-1 text-primary"></i> Daftar Rekaman Aktivitas</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover" id="audit-table" style="width: 100%;">
                        <thead>
                            <tr>
                                <th style="width: 50px;">No</th>
                                <th style="width: 150px;">Waktu</th>
                                <th style="width: 200px;">Akun & Identitas</th>
                                <th style="width: 150px;">Event</th>
                                <th>Deskripsi Aktivitas</th>
                                <th style="width: 140px;">Jaringan</th>
                                <th style="width: 90px;">Status</th>
                                <th style="width: 80px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Detail Modal -->
    <div class="modal fade" id="auditDetailModal" tabindex="-1" role="dialog" aria-labelledby="auditDetailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="auditDetailModalLabel">
                        <i class="fas fa-info-circle mr-1"></i> Rincian Audit Log #<span id="modal-log-id"></span>
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="card border mb-0">
                                <div class="card-header bg-light py-2">
                                    <h6 class="mb-0 text-dark"><i class="fas fa-user mr-1"></i> Informasi Akun</h6>
                                </div>
                                <div class="card-body py-2 small">
                                    <div class="row mb-1">
                                        <div class="col-4 text-muted">Nama:</div>
                                        <div class="col-8 font-weight-bold" id="modal-user-name">-</div>
                                    </div>
                                    <div class="row mb-1">
                                        <div class="col-4 text-muted">Email:</div>
                                        <div class="col-8" id="modal-user-email">-</div>
                                    </div>
                                    <div class="row mb-1">
                                        <div class="col-4 text-muted">Peran:</div>
                                        <div class="col-8"><span class="badge badge-secondary" id="modal-user-role">-</span></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="card border mb-0">
                                <div class="card-header bg-light py-2">
                                    <h6 class="mb-0 text-dark"><i class="fas fa-globe mr-1"></i> Akses & Jaringan</h6>
                                </div>
                                <div class="card-body py-2 small">
                                    <div class="row mb-1">
                                        <div class="col-4 text-muted">IP Address:</div>
                                        <div class="col-8 font-weight-bold text-primary" id="modal-ip-address">-</div>
                                    </div>
                                    <div class="row mb-1">
                                        <div class="col-4 text-muted">Method & Status:</div>
                                        <div class="col-8">
                                            <span class="badge badge-secondary mr-1" id="modal-method">-</span>
                                            <span class="badge" id="modal-status">-</span>
                                        </div>
                                    </div>
                                    <div class="row mb-1">
                                        <div class="col-4 text-muted">Waktu:</div>
                                        <div class="col-8" id="modal-created-at">-</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border mb-3">
                        <div class="card-header bg-light py-2">
                            <h6 class="mb-0 text-dark"><i class="fas fa-stream mr-1"></i> Aktivitas</h6>
                        </div>
                        <div class="card-body py-2 small">
                            <div class="row mb-1">
                                <div class="col-2 text-muted">Event:</div>
                                <div class="col-10"><span class="badge" id="modal-event-type">-</span></div>
                            </div>
                            <div class="row mb-1">
                                <div class="col-2 text-muted">Deskripsi:</div>
                                <div class="col-10 font-weight-bold" id="modal-action">-</div>
                            </div>
                            <div class="row mb-1" id="modal-url-row">
                                <div class="col-2 text-muted">URL:</div>
                                <div class="col-10 text-break text-muted font-monospace" id="modal-url">-</div>
                            </div>
                            <div class="row mb-1" id="modal-user-agent-row">
                                <div class="col-2 text-muted">User Agent:</div>
                                <div class="col-10 text-break text-muted small" id="modal-user-agent">-</div>
                            </div>
                        </div>
                    </div>

                    <!-- Database Changes Diff Section -->
                    <div id="modal-diff-container" style="display: none;">
                        <div class="card border mb-3">
                            <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center">
                                <h6 class="mb-0 text-dark"><i class="fas fa-exchange-alt mr-1"></i> Perubahan Data Database (<span id="modal-auditable-info"></span>)</h6>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm mb-0 text-left small">
                                        <thead class="thead-light">
                                            <tr>
                                                <th style="width: 25%;">Kolom / Atribut</th>
                                                <th style="width: 37.5%;" class="bg-danger-light text-danger">Nilai Sebelum (Old)</th>
                                                <th style="width: 37.5%;" class="bg-success-light text-success">Nilai Sesudah (New)</th>
                                            </tr>
                                        </thead>
                                        <tbody id="modal-diff-body">
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Additional Metadata / Properties -->
                    <div id="modal-properties-container" style="display: none;">
                        <div class="card border mb-0">
                            <div class="card-header bg-light py-2">
                                <h6 class="mb-0 text-dark"><i class="fas fa-tags mr-1"></i> Metadata / Properti Tambahan</h6>
                            </div>
                            <div class="card-body py-2">
                                <pre id="modal-properties" class="bg-light p-2 rounded mb-0" style="max-height: 200px; overflow-y: auto; font-size: 11px;"></pre>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    @push('plugins_js')
        <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
    @endpush

    @push('page_js')
    <script>
        $(document).ready(function() {
            var table = $('#audit-table').DataTable({
                processing: true,
                serverSide: true,
                order: [[1, 'desc']],
                ajax: {
                    url: "{{ route('admin.forensics.audit-logs.index') }}",
                    data: function(d) {
                        d.category = $('#filter-category').val();
                        d.event_type = $('#filter-event-type').val();
                        d.status = $('#filter-status').val();
                        d.user_id = $('#filter-user').val();
                        d.ip_address = $('#filter-ip').val();
                        d.date_start = $('#filter-date-start').val();
                        d.date_end = $('#filter-date-end').val();
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'created_at', name: 'created_at' },
                    { data: 'user_info', name: 'user_name' },
                    { data: 'event_type', name: 'event_type' },
                    { data: 'action', name: 'action' },
                    { data: 'network_info', name: 'ip_address' },
                    { data: 'status', name: 'status' },
                    { data: 'details', name: 'details', orderable: false, searchable: false }
                ],
                language: {
                    search: "Cari:",
                    lengthMenu: "Tampilkan _MENU_ data",
                    info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                    infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
                    emptyTable: "Tidak ada data log audit yang tersedia",
                    zeroRecords: "Tidak ditemukan data log audit yang sesuai",
                    paginate: {
                        first: "Pertama",
                        last: "Terakhir",
                        next: "Selanjutnya",
                        previous: "Sebelumnya"
                    }
                }
            });

            $('#btn-apply-filter').on('click', function() {
                table.draw();
            });

            $('#btn-reset-filter').on('click', function() {
                $('#filter-form')[0].reset();
                table.draw();
            });

            // View Details Modal Trigger
            $(document).on('click', '.js-view-audit-detail', function() {
                var url = $(this).data('url');
                var id = $(this).data('id');

                $('#modal-log-id').text(id);

                $.ajax({
                    url: url,
                    type: 'GET',
                    success: function(data) {
                        $('#modal-user-name').text(data.user_name || '-');
                        $('#modal-user-email').text(data.user_email || '-');
                        $('#modal-user-role').text(data.role || 'GUEST');

                        $('#modal-ip-address').text(data.ip_address || '-');
                        $('#modal-method').text(data.method || '-');
                        $('#modal-status').text(data.status ? data.status.toUpperCase() : '-')
                            .attr('class', 'badge ' + data.status_badge);

                        $('#modal-created-at').text(data.created_at_formatted || '-');
                        $('#modal-event-type').text(data.event_type || '-')
                            .attr('class', 'badge ' + data.event_badge);
                        $('#modal-action').text(data.action || '-');
                        $('#modal-url').text(data.url || '-');
                        $('#modal-user-agent').text(data.user_agent || '-');

                        // Diff Table Rendering
                        var oldVals = data.old_values;
                        var newVals = data.new_values;

                        if (oldVals || newVals) {
                            $('#modal-diff-container').show();
                            $('#modal-auditable-info').text((data.auditable_type || 'Model') + ' #' + (data.auditable_id || ''));

                            var diffHtml = '';
                            var keys = {};

                            if (oldVals) {
                                Object.keys(oldVals).forEach(function(k) { keys[k] = true; });
                            }
                            if (newVals) {
                                Object.keys(newVals).forEach(function(k) { keys[k] = true; });
                            }

                            Object.keys(keys).forEach(function(key) {
                                var oldVal = oldVals && oldVals[key] !== undefined ? (typeof oldVals[key] === 'object' ? JSON.stringify(oldVals[key]) : oldVals[key]) : '<span class="text-muted font-italic">-</span>';
                                var newVal = newVals && newVals[key] !== undefined ? (typeof newVals[key] === 'object' ? JSON.stringify(newVals[key]) : newVals[key]) : '<span class="text-muted font-italic">-</span>';

                                var isDiff = oldVals && newVals && oldVals[key] !== newVals[key];
                                var rowClass = isDiff ? 'table-warning' : '';

                                diffHtml += '<tr class="' + rowClass + '">' +
                                    '<td class="font-weight-bold font-monospace">' + $('<div>').text(key).html() + '</td>' +
                                    '<td class="text-break bg-light text-danger">' + $('<div>').text(oldVal).html() + '</td>' +
                                    '<td class="text-break bg-light text-success font-weight-600">' + $('<div>').text(newVal).html() + '</td>' +
                                    '</tr>';
                            });

                            $('#modal-diff-body').html(diffHtml);
                        } else {
                            $('#modal-diff-container').hide();
                        }

                        // Additional Properties
                        if (data.properties && Object.keys(data.properties).length > 0) {
                            $('#modal-properties-container').show();
                            $('#modal-properties').text(JSON.stringify(data.properties, null, 2));
                        } else {
                            $('#modal-properties-container').hide();
                        }

                        $('#auditDetailModal').modal('show');
                    },
                    error: function() {
                        alert('Gagal mengambil rincian log audit.');
                    }
                });
            });
        });
    </script>
    @endpush
</x-app-layout>
