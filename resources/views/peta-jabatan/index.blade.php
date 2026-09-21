<x-app-layout>
    @section('title', $title)

    @push('page_css')
        <style>
            /* Tree Hierarchy Canvas Styling */
            .peta-org-wrapper {
                background: #f8fafc;
                border: 1px solid #e2e8f0;
                border-radius: 12px;
                padding: 30px 20px;
                overflow-x: auto;
                min-height: 520px;
                position: relative;
            }

            .peta-jabatan-tree-root {
                display: flex;
                justify-content: center;
                list-style-type: none;
                margin: 0;
                padding: 0;
                transform-origin: top center;
                transition: transform 0.2s ease-in-out;
            }

            .peta-jabatan-tree-root, .peta-jabatan-tree-root ul {
                list-style-type: none;
                margin: 0;
                padding: 0;
                position: relative;
            }

            .peta-jabatan-tree-root ul {
                display: flex;
                justify-content: center;
                padding-top: 28px;
                position: relative;
            }

            /* Connecting Lines */
            .peta-jabatan-tree-root ul::before {
                content: '';
                position: absolute;
                top: 0;
                left: 50%;
                border-left: 2px solid #cbd5e1;
                width: 0;
                height: 28px;
            }

            .peta-tree-item {
                display: flex;
                flex-direction: column;
                align-items: center;
                position: relative;
                padding: 28px 12px 0 12px;
            }

            .peta-tree-item::before, .peta-tree-item::after {
                content: '';
                position: absolute;
                top: 0;
                right: 50%;
                border-top: 2px solid #cbd5e1;
                width: 50%;
                height: 28px;
            }

            .peta-tree-item::after {
                right: auto;
                left: 50%;
                border-left: 2px solid #cbd5e1;
            }

            .peta-tree-item:only-child::after, .peta-tree-item:only-child::before {
                display: none;
            }

            .peta-tree-item:only-child {
                padding-top: 0;
            }

            .peta-tree-item:first-child::before, .peta-tree-item:last-child::after {
                border: 0 none;
            }

            .peta-tree-item:last-child::before {
                border-right: 2px solid #cbd5e1;
                border-radius: 0 8px 0 0;
            }

            .peta-tree-item:first-child::after {
                border-radius: 8px 0 0 0;
            }

            /* Card Node Styling */
            .peta-jabatan-node {
                width: 275px;
                border: 1px solid #e2e8f0;
                border-radius: 10px;
                background: #ffffff;
                transition: all 0.25s ease;
                z-index: 2;
                position: relative;
            }

            .peta-jabatan-node:hover {
                box-shadow: 0 10px 20px -3px rgba(0, 0, 0, 0.12), 0 4px 8px -2px rgba(0, 0, 0, 0.06) !important;
                transform: translateY(-3px);
            }

            .peta-jabatan-node.border-top-success {
                border-top: 4px solid #47c363;
            }
            .peta-jabatan-node.border-top-secondary {
                border-top: 4px solid #6c757d;
            }
            .peta-jabatan-node.border-top-danger {
                border-top: 4px solid #fc544b;
            }
            .peta-jabatan-node.border-top-warning {
                border-top: 4px solid #ffa426;
            }

            .hover-primary:hover {
                color: #6777ef !important;
            }

            .btn-xs {
                padding: 0.15rem 0.4rem;
                font-size: 0.75rem;
                line-height: 1.2;
                border-radius: 0.2rem;
            }

            .peta-tree-children.collapsed {
                display: none !important;
            }

            /* Highlight on search */
            .peta-jabatan-node.highlight-node {
                border: 2px solid #6777ef !important;
                box-shadow: 0 0 12px rgba(103, 119, 239, 0.5) !important;
            }

            /* Pemangku list scrollbar */
            .pemangku-list-container::-webkit-scrollbar {
                width: 4px;
            }
            .pemangku-list-container::-webkit-scrollbar-thumb {
                background: #cbd5e1;
                border-radius: 4px;
            }
            .pemangku-list-container::-webkit-scrollbar-thumb:hover {
                background: #94a3b8;
            }
        </style>
    @endpush

    <x-slot name="header">
        <h1>{{ $title }}</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{ route('dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item">{{ $title }}</div>
        </div>
    </x-slot>

    <div class="section-body">
        <h2 class="section-title">{{ $title }}</h2>
        <p class="section-lead">Master data jabatan terintegrasi untuk struktur hierarki organisasi, gap formasi kebutuhan, dan career path.</p>

        
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
            <div class="mt-2 mt-md-0">
                @if ($canManage)
                    <a href="{{ route('peta-jabatan.manage.create', ['slug' => 'jabatan']) }}" class="btn btn-primary btn-icon icon-left mr-1">
                        <i class="fas fa-plus-circle"></i> Tambah Jabatan
                    </a>
                    <a href="{{ route('peta-jabatan.manage.index', ['slug' => 'jabatan']) }}" class="btn btn-outline-primary btn-icon icon-left mr-1">
                        <i class="fas fa-list-ul"></i> Kelola Jabatan
                    </a>
                    <a href="{{ route('peta-jabatan.manage.index', ['slug' => 'career-path']) }}" class="btn btn-outline-secondary btn-icon icon-left">
                        <i class="fas fa-route"></i> Career Path
                    </a>
                @else
                    <div class="alert alert-info py-2 px-3 mb-0"><i class="fas fa-eye mr-1"></i> Mode Lihat Saja (Read-Only)</div>
                @endif
            </div>
        </div>

        {{-- Stat Cards --}}
        <div class="row" id="peta-jabatan-summary-cards">
            <div class="col-lg-3 col-md-6 col-12">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-primary"><i class="fas fa-sitemap"></i></div>
                    <div class="card-wrap">
                        <div class="card-header"><h4>Total Jabatan</h4></div>
                        <div class="card-body">{{ $summary['total_jabatan'] ?? 0 }}</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-12">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-success"><i class="fas fa-user-check"></i></div>
                    <div class="card-wrap">
                        <div class="card-header"><h4>Jabatan Terisi</h4></div>
                        <div class="card-body">{{ $summary['jabatan_terisi'] ?? 0 }}</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-12">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-secondary"><i class="fas fa-user-slash"></i></div>
                    <div class="card-wrap">
                        <div class="card-header"><h4>Jabatan Kosong</h4></div>
                        <div class="card-body">{{ $summary['jabatan_kosong'] ?? 0 }}</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-12">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-danger"><i class="fas fa-user-times"></i></div>
                    <div class="card-wrap">
                        <div class="card-header"><h4>Kekurangan Pegawai</h4></div>
                        <div class="card-body">{{ $summary['kekurangan'] ?? 0 }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filter Bar --}}
        <div class="card mb-4">
            <div class="card-body py-3">
                <form method="GET" action="{{ route('peta-jabatan.index') }}" class="row g-2 align-items-center">
                    <div class="col-lg-4 col-md-6 col-12">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                            </div>
                            <input type="text" name="search" value="{{ $filters['search'] }}" class="form-control" placeholder="Cari nama / kode jabatan...">
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 col-12">
                        <select name="unit_kerja_id" class="form-control">
                            <option value="">-- Semua Unit Kerja --</option>
                            @foreach ($unitKerjas as $unitKerja)
                                <option value="{{ $unitKerja->id }}" @selected($filters['unit_kerja_id'] == $unitKerja->id)>{{ $unitKerja->unit_kerja }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-6 col-12">
                        <select name="status_jabatan" class="form-control">
                            <option value="">-- Semua Status --</option>
                            <option value="Aktif" @selected($filters['status_jabatan'] === 'Aktif')>Aktif</option>
                            <option value="Definitif" @selected($filters['status_jabatan'] === 'Definitif')>Definitif</option>
                            <option value="PLT" @selected($filters['status_jabatan'] === 'PLT')>PLT</option>
                            <option value="PLH" @selected($filters['status_jabatan'] === 'PLH')>PLH</option>
                            <option value="Lowong" @selected($filters['status_jabatan'] === 'Lowong')>Lowong</option>
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-6 col-12 text-right">
                        <button type="submit" class="btn btn-primary mr-1"><i class="fas fa-filter"></i> Filter</button>
                        <a href="{{ route('peta-jabatan.index') }}" class="btn btn-light" title="Reset Filter"><i class="fas fa-undo"></i></a>
                    </div>
                </form>
            </div>
        </div>

        {{-- Main Navigation Tabs --}}
        <div class="card">
            <div class="card-header border-bottom-0 pb-0">
                <ul class="nav nav-tabs card-header-tabs" id="petaJabatanTab" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active font-weight-bold" id="tree-tab" data-toggle="tab" href="#tab-tree" role="tab" aria-controls="tree" aria-selected="true">
                            <i class="fas fa-sitemap text-primary mr-1"></i> Bagan Hirarki Organisasi
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link font-weight-bold" id="table-tab" data-toggle="tab" href="#tab-table" role="tab" aria-controls="table" aria-selected="false">
                            <i class="fas fa-table text-info mr-1"></i> Analisis Kebutuhan ({{ count($gapRows) }})
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link font-weight-bold" id="dashboard-tab" data-toggle="tab" href="#tab-dashboard" role="tab" aria-controls="dashboard" aria-selected="false">
                            <i class="fas fa-chart-pie text-success mr-1"></i> Dashboard &amp; Distribusi
                        </a>
                    </li>
                </ul>
            </div>
            <div class="card-body pt-3">
                <div class="tab-content" id="petaJabatanTabContent">
                    {{-- TAB 1: BAGAN HIRARKI (ORGANIZATIONAL CHART) --}}
                    <div class="tab-pane fade show active" id="tab-tree" role="tabpanel" aria-labelledby="tree-tab">
                        {{-- Controls & Legend Toolbar --}}
                        <div class="d-flex flex-wrap justify-content-between align-items-center bg-light p-2 rounded mb-3">
                            <div class="d-flex align-items-center mb-2 mb-md-0">
                                <span class="text-muted small font-weight-bold mr-2"><i class="fas fa-info-circle mr-1"></i>Status Formasi:</span>
                                <span class="badge badge-success mr-1"><i class="fas fa-check-circle mr-1"></i>Terisi</span>
                                <span class="badge badge-secondary mr-1"><i class="fas fa-circle mr-1"></i>Kosong</span>
                                <span class="badge badge-danger mr-1"><i class="fas fa-exclamation-circle mr-1"></i>Kekurangan</span>
                                <span class="badge badge-warning"><i class="fas fa-user-plus mr-1"></i>Kelebihan</span>
                            </div>
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-outline-secondary" id="btn-expand-all" title="Buka Semua Sub-Jabatan">
                                    <i class="fas fa-expand-arrows-alt mr-1"></i> Buka Semua
                                </button>
                                <button type="button" class="btn btn-outline-secondary" id="btn-collapse-all" title="Tutup Semua Sub-Jabatan">
                                    <i class="fas fa-compress-arrows-alt mr-1"></i> Tutup Semua
                                </button>
                                <button type="button" class="btn btn-outline-secondary" id="btn-zoom-in" title="Perbesar">
                                    <i class="fas fa-search-plus"></i>
                                </button>
                                <button type="button" class="btn btn-outline-secondary" id="btn-zoom-out" title="Perkecil">
                                    <i class="fas fa-search-minus"></i>
                                </button>
                                <button type="button" class="btn btn-outline-secondary" id="btn-zoom-reset" title="Reset Tampilan">
                                    <i class="fas fa-sync-alt"></i>
                                </button>
                            </div>
                        </div>

                        {{-- Hierarchy Tree Container --}}
                        <div class="peta-org-wrapper" id="petaOrgWrapper">
                            @if (empty($tree))
                                <div class="text-center py-5">
                                    <div class="text-muted mb-3"><i class="fas fa-sitemap fa-3x"></i></div>
                                    <h5 class="text-muted">Belum ada data jabatan untuk hierarki ini.</h5>
                                    <p class="text-muted small">Coba ubah filter pencarian atau tambahkan jabatan baru.</p>
                                    @if ($canManage)
                                        <a href="{{ route('peta-jabatan.manage.create', ['slug' => 'jabatan']) }}" class="btn btn-primary mt-2">
                                            <i class="fas fa-plus"></i> Tambah Jabatan Baru
                                        </a>
                                    @endif
                                </div>
                            @else
                                <ul class="peta-jabatan-tree-root" id="petaJabatanTreeRoot">
                                    @foreach ($tree as $node)
                                        @include('peta-jabatan._tree-node', ['node' => $node, 'canManage' => $canManage])
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    </div>

                    {{-- TAB 2: TABEL ANALISIS KEBUTUHAN & CRUD --}}
                    <div class="tab-pane fade" id="tab-table" role="tabpanel" aria-labelledby="table-tab">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover align-middle">
                                <thead class="thead-light">
                                    <tr>
                                        <th style="width: 50px;">No</th>
                                        <th>Kode &amp; Nama Jabatan</th>
                                        <th>Unit Kerja</th>
                                        <th>Atasan Langsung</th>
                                        <th class="text-center" style="width: 100px;">Kebutuhan</th>
                                        <th class="text-center" style="width: 100px;">Terisi</th>
                                        <th class="text-center" style="width: 120px;">Status Formasi</th>
                                        <th class="text-center" style="width: 150px;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($gapRows as $index => $row)
                                        @php
                                            $gapStatus = 'Terisi';
                                            $gapBadge = 'badge-success';
                                            if ($row['terisi'] === 0) {
                                                $gapStatus = 'Kosong';
                                                $gapBadge = 'badge-secondary';
                                            } elseif ($row['kekurangan'] > 0) {
                                                $gapStatus = 'Kurang ' . $row['kekurangan'];
                                                $gapBadge = 'badge-danger';
                                            } elseif ($row['kelebihan'] > 0) {
                                                $gapStatus = 'Lebih ' . $row['kelebihan'];
                                                $gapBadge = 'badge-warning';
                                            }
                                        @endphp
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>
                                                <a href="{{ route('peta-jabatan.manage.show', ['slug' => 'jabatan', 'id' => $row['id']]) }}" class="font-weight-600 text-dark">
                                                    {{ $row['jabatan'] }}
                                                </a>
                                                @if (!empty($row['kode_jabatan']))
                                                    <br><small class="text-muted"><i class="fas fa-tag"></i> {{ $row['kode_jabatan'] }}</small>
                                                @endif
                                            </td>
                                            <td>{{ $row['unit_kerja'] }}</td>
                                            <td>
                                                @if ($row['atasan_langsung'] !== '-')
                                                    <span class="badge badge-light"><i class="fas fa-level-up-alt text-primary mr-1"></i>{{ $row['atasan_langsung'] }}</span>
                                                @else
                                                    <span class="text-muted small font-italic">Top Level</span>
                                                @endif
                                            </td>
                                            <td class="text-center font-weight-bold">{{ $row['kebutuhan'] }}</td>
                                            <td class="text-center font-weight-bold">{{ $row['terisi'] }}</td>
                                            <td class="text-center">
                                                <span class="badge {{ $gapBadge }} px-2 py-1">{{ $gapStatus }}</span>
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group btn-group-sm">
                                                    <a href="{{ route('peta-jabatan.manage.show', ['slug' => 'jabatan', 'id' => $row['id']]) }}" class="btn btn-info" title="Detail">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    @if ($canManage)
                                                        <a href="{{ route('peta-jabatan.manage.edit', ['slug' => 'jabatan', 'id' => $row['id']]) }}" class="btn btn-primary" title="Edit">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <a href="{{ route('peta-jabatan.manage.create', ['slug' => 'jabatan', 'atasan_id' => $row['id'], 'unit_kerja_id' => $row['unit_kerja_id']]) }}" class="btn btn-success" title="Tambah Sub-Jabatan">
                                                            <i class="fas fa-plus"></i>
                                                        </a>
                                                        <button type="button" class="btn btn-danger btn-delete-jabatan" 
                                                                data-id="{{ $row['id'] }}" 
                                                                data-name="{{ $row['jabatan'] }}" 
                                                                data-pemangku="{{ $row['pegawais_count'] }}" 
                                                                data-bawahan="{{ $row['bawahan_count'] }}"
                                                                title="Hapus">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center py-4 text-muted">Belum ada data jabatan yang sesuai dengan filter.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- TAB 3: DASHBOARD & DISTRIBUSI --}}
                    <div class="tab-pane fade" id="tab-dashboard" role="tabpanel" aria-labelledby="dashboard-tab">
                        <div id="peta-jabatan-dashboard-loading" class="text-muted text-center py-4">
                            <i class="fas fa-spinner fa-spin mr-1"></i> Memuat data dashboard distribusi...
                        </div>
                        <div id="peta-jabatan-dashboard-content" class="d-none">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card card-primary">
                                        <div class="card-header">
                                            <h4><i class="fas fa-graduation-cap text-primary mr-2"></i> Distribusi Dosen per Jabatan Akademik</h4>
                                        </div>
                                        <div class="card-body p-0">
                                            <ul id="peta-jabatan-dosen-list" class="list-group list-group-flush"></ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card card-danger">
                                        <div class="card-header">
                                            <h4><i class="fas fa-user-times text-danger mr-2"></i> Jabatan yang Membutuhkan Pengisian Formasi</h4>
                                        </div>
                                        <div class="card-body p-0">
                                            <ul id="peta-jabatan-pengisian-list" class="list-group list-group-flush"></ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Career Path Preview --}}
                        <div class="card mt-3">
                            <div class="card-header">
                                <h4><i class="fas fa-route text-info mr-2"></i> Jalur Pengembangan Karier (Career Path)</h4>
                            </div>
                            <div class="card-body table-responsive p-0">
                                <table class="table table-striped mb-0">
                                    <thead>
                                        <tr>
                                            <th>Jabatan Asal</th>
                                            <th></th>
                                            <th>Jabatan Tujuan</th>
                                            <th>Persyaratan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($careerPaths as $cp)
                                            <tr>
                                                <td class="font-weight-600">{{ $cp->asal }}</td>
                                                <td class="text-center text-primary"><i class="fas fa-arrow-right"></i></td>
                                                <td class="font-weight-600 text-success">{{ $cp->tujuan }}</td>
                                                <td>
                                                    @if ($cp->persyaratan)
                                                        @php $reqs = is_string($cp->persyaratan) ? json_decode($cp->persyaratan, true) : $cp->persyaratan; @endphp
                                                        @if (is_array($reqs))
                                                            <ul class="mb-0 pl-3 small">
                                                                @foreach ($reqs as $r)
                                                                    <li>{{ $r }}</li>
                                                                @endforeach
                                                            </ul>
                                                        @else
                                                            <span class="small">{{ $cp->persyaratan }}</span>
                                                        @endif
                                                    @else
                                                        <span class="text-muted small">-</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center text-muted py-3">Belum ada pemetaan career path.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Hidden form for delete operation --}}
    <form id="delete-jabatan-form" action="" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>

    @push('page_js')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                // 1. Delete handler with validation check
                document.querySelectorAll('.btn-delete-jabatan').forEach(function (btn) {
                    btn.addEventListener('click', function () {
                        var id = this.getAttribute('data-id');
                        var name = this.getAttribute('data-name');
                        var pemangku = parseInt(this.getAttribute('data-pemangku') || 0);
                        var bawahan = parseInt(this.getAttribute('data-bawahan') || 0);

                        if (pemangku > 0) {
                            alert("Jabatan '" + name + "' tidak dapat dihapus karena masih memiliki " + pemangku + " pemangku pegawai.");
                            return;
                        }

                        if (bawahan > 0) {
                            alert("Jabatan '" + name + "' tidak dapat dihapus karena memiliki " + bawahan + " sub-jabatan (bawahan). Harap pindahkan atau hapus bawahan terlebih dahulu.");
                            return;
                        }

                        if (confirm("Apakah Anda yakin ingin menghapus jabatan '" + name + "'?")) {
                            var form = document.getElementById('delete-jabatan-form');
                            form.action = "{{ url('peta-jabatan/manage/jabatan') }}/" + id;
                            form.submit();
                        }
                    });
                });

                // 2. Child Collapse / Expand toggle
                document.querySelectorAll('.btn-toggle-children').forEach(function (btn) {
                    btn.addEventListener('click', function () {
                        var targetSelector = this.getAttribute('data-target');
                        var target = document.querySelector(targetSelector);
                        if (target) {
                            target.classList.toggle('collapsed');
                            var icon = this.querySelector('i');
                            if (target.classList.contains('collapsed')) {
                                icon.classList.remove('fa-chevron-down');
                                icon.classList.add('fa-chevron-right');
                            } else {
                                icon.classList.remove('fa-chevron-right');
                                icon.classList.add('fa-chevron-down');
                            }
                        }
                    });
                });

                // 3. Expand All / Collapse All buttons
                var btnExpandAll = document.getElementById('btn-expand-all');
                var btnCollapseAll = document.getElementById('btn-collapse-all');

                if (btnExpandAll) {
                    btnExpandAll.addEventListener('click', function () {
                        document.querySelectorAll('.peta-tree-children').forEach(function (el) {
                            el.classList.remove('collapsed');
                        });
                        document.querySelectorAll('.btn-toggle-children i').forEach(function (icon) {
                            icon.classList.remove('fa-chevron-right');
                            icon.classList.add('fa-chevron-down');
                        });
                    });
                }

                if (btnCollapseAll) {
                    btnCollapseAll.addEventListener('click', function () {
                        document.querySelectorAll('.peta-tree-children').forEach(function (el) {
                            el.classList.add('collapsed');
                        });
                        document.querySelectorAll('.btn-toggle-children i').forEach(function (icon) {
                            icon.classList.remove('fa-chevron-down');
                            icon.classList.add('fa-chevron-right');
                        });
                    });
                }

                // 4. Zoom in / Zoom out / Zoom reset
                var zoomLevel = 1;
                var treeRoot = document.getElementById('petaJabatanTreeRoot');
                var btnZoomIn = document.getElementById('btn-zoom-in');
                var btnZoomOut = document.getElementById('btn-zoom-out');
                var btnZoomReset = document.getElementById('btn-zoom-reset');

                function applyZoom() {
                    if (treeRoot) {
                        treeRoot.style.transform = 'scale(' + zoomLevel + ')';
                    }
                }

                if (btnZoomIn) {
                    btnZoomIn.addEventListener('click', function () {
                        if (zoomLevel < 1.5) {
                            zoomLevel += 0.1;
                            applyZoom();
                        }
                    });
                }

                if (btnZoomOut) {
                    btnZoomOut.addEventListener('click', function () {
                        if (zoomLevel > 0.6) {
                            zoomLevel -= 0.1;
                            applyZoom();
                        }
                    });
                }

                if (btnZoomReset) {
                    btnZoomReset.addEventListener('click', function () {
                        zoomLevel = 1;
                        applyZoom();
                    });
                }

                // 5. Dashboard Tab async loader
                var dashboardLoaded = false;
                var dashboardTab = document.getElementById('dashboard-tab');
                if (dashboardTab) {
                    dashboardTab.addEventListener('shown.bs.tab', function () {
                        if (dashboardLoaded) return;

                        var loading = document.getElementById('peta-jabatan-dashboard-loading');
                        var content = document.getElementById('peta-jabatan-dashboard-content');
                        var dosenList = document.getElementById('peta-jabatan-dosen-list');
                        var pengisianList = document.getElementById('peta-jabatan-pengisian-list');

                        fetch('{{ route('peta-jabatan.dashboard') }}', {
                            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                            credentials: 'same-origin',
                        })
                            .then(function (response) { return response.json(); })
                            .then(function (payload) {
                                var data = payload.data || {};

                                (data.distribusi_dosen_per_jabatan_akademik || []).forEach(function (item) {
                                    var li = document.createElement('li');
                                    li.className = 'list-group-item d-flex justify-content-between align-items-center py-2 px-3';
                                    li.textContent = item.label;
                                    var badge = document.createElement('span');
                                    badge.className = 'badge badge-primary badge-pill';
                                    badge.textContent = item.total;
                                    li.appendChild(badge);
                                    dosenList.appendChild(li);
                                });

                                var pengisian = data.jabatan_membutuhkan_pengisian || [];
                                if (pengisian.length === 0) {
                                    var empty = document.createElement('li');
                                    empty.className = 'list-group-item text-muted py-3 px-3';
                                    empty.textContent = 'Tidak ada jabatan yang kekurangan pegawai.';
                                    pengisianList.appendChild(empty);
                                } else {
                                    pengisian.forEach(function (item) {
                                        var li = document.createElement('li');
                                        li.className = 'list-group-item d-flex justify-content-between align-items-center py-2 px-3';
                                        li.textContent = item.jabatan + ' (' + item.unit_kerja + ')';
                                        var badge = document.createElement('span');
                                        badge.className = 'badge badge-danger badge-pill';
                                        badge.textContent = 'Kurang ' + item.kekurangan;
                                        li.appendChild(badge);
                                        pengisianList.appendChild(li);
                                    });
                                }

                                if (loading) loading.classList.add('d-none');
                                if (content) content.classList.remove('d-none');
                                dashboardLoaded = true;
                            })
                            .catch(function () {
                                if (loading) loading.textContent = 'Gagal memuat data dashboard.';
                            });
                    });
                }
            });
        </script>
    @endpush
</x-app-layout>
