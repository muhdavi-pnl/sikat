<x-app-layout>
    @section('title', $title)
    <x-slot name="header">
        <div class="section-header-back">
            <a href="{{ route('dashboard') }}" class="btn btn-icon"><i class="fas fa-arrow-left"></i></a>
        </div>
        <h1>{{ $title }}</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{ route('dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item">Statistik Pegawai</div>
        </div>
    </x-slot>

    @push('page_css')
    <style>
        .chart-container-custom {
            position: relative;
            height: 320px;
            width: 100%;
        }
        .chart-container-doughnut {
            position: relative;
            height: 280px;
            width: 100%;
        }
        .stat-badge {
            font-size: 0.82rem;
            padding: 0.35em 0.7em;
            border-radius: 6px;
        }
        .stat-progress-bar {
            height: 8px;
            border-radius: 4px;
        }
        .nav-pills-custom .nav-link {
            border-radius: 20px;
            padding: 8px 18px;
            margin-right: 6px;
            margin-bottom: 8px;
            font-weight: 600;
            font-size: 0.85rem;
            color: #495057 !important;
            background: #eef2f7 !important;
            border: 1px solid #e2e8f0;
            transition: all 0.2s ease-in-out;
        }
        .nav-pills-custom .nav-link i {
            color: #6777ef;
            transition: color 0.2s ease;
        }
        .nav-pills-custom .nav-link:hover {
            color: #ffffff !important;
            background: #6777ef !important;
            border-color: #6777ef !important;
            box-shadow: 0 4px 12px rgba(103, 119, 239, 0.35);
            text-decoration: none !important;
        }
        .nav-pills-custom .nav-link:hover i {
            color: #ffffff !important;
        }
        .nav-pills-custom .nav-link.active {
            color: #ffffff !important;
            background: #6777ef !important;
            border-color: #6777ef !important;
            box-shadow: 0 4px 12px rgba(103, 119, 239, 0.4);
        }
        .nav-pills-custom .nav-link.active i {
            color: #ffffff !important;
        }
        .matrix-table th {
            text-align: center;
            vertical-align: middle !important;
        }
        .matrix-table td {
            vertical-align: middle !important;
        }
        .table-rekap tbody tr:hover {
            background-color: #f8fafc;
        }
        .ratio-bar-container {
            display: flex;
            height: 6px;
            border-radius: 3px;
            overflow: hidden;
            background-color: #e9ecef;
        }
        @media print {
            .main-sidebar, .main-navbar, .main-footer, .nav-pills-custom, .btn-print-hide, .section-header-back {
                display: none !important;
            }
            .main-content {
                padding: 0 !important;
            }
            .card {
                box-shadow: none !important;
                border: 1px solid #ddd !important;
                page-break-inside: avoid;
            }
        }
    </style>
    @endpush

    {{-- Top Overview Bar --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-primary text-white shadow-sm border-0 mb-0" style="background: linear-gradient(135deg, #6777ef 0%, #3abaf4 100%);">
                <div class="card-body p-4">
                    <div class="row align-items-center">
                        <div class="col-lg-8 col-md-8 col-12">
                            <h3 class="font-weight-bold text-white mb-2"><i class="fas fa-chart-pie mr-2"></i> Statistik & Rekapitulasi Data Pegawai</h3>
                            <p class="mb-0 text-white-50" style="font-size: 0.95rem;">
                                Visualisasi analitik dan rekapitulasi data kepegawaian di setiap unit kerja berdasarkan jenis kelamin, status pegawai, golongan/pangkat, tingkat pendidikan, eselon jabatan, dan jenis jabatan di lingkungan Politeknik Negeri Lhokseumawe.
                            </p>
                        </div>
                        <div class="col-lg-4 col-md-4 col-12 text-md-right mt-3 mt-md-0 btn-print-hide">
                            <button type="button" onclick="window.print()" class="btn btn-light text-primary font-weight-bold shadow-sm">
                                <i class="fas fa-print mr-1"></i> Cetak Laporan
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Ringkasan Kartu Statistik Utama --}}
    <div class="row">
        <div class="col-lg-4 col-md-4 col-sm-4 col-12">
            <div class="card card-statistic-1 shadow-sm">
                <div class="card-icon bg-primary">
                    <i class="fas fa-users"></i>
                </div>
                <div class="card-wrap">
                    <div class="card-header">
                        <h4>Total Pegawai</h4>
                    </div>
                    <div class="card-body">
                        {{ number_format($totalPegawai) }}
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-4 col-sm-4 col-12">
            <div class="card card-statistic-1 shadow-sm">
                <div class="card-icon bg-warning">
                    <i class="fas fa-user-graduate"></i>
                </div>
                <div class="card-wrap">
                    <div class="card-header">
                        <h4>Dosen</h4>
                    </div>
                    <div class="card-body">
                        {{ number_format($totalDosen) }}
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-4 col-sm-4 col-12">
            <div class="card card-statistic-1 shadow-sm">
                <div class="card-icon bg-danger">
                    <i class="fas fa-user-tie"></i>
                </div>
                <div class="card-wrap">
                    <div class="card-header">
                        <h4>Tendik</h4>
                    </div>
                    <div class="card-body">
                        {{ number_format($totalTendik) }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Navigasi Tab / Filter Cepat --}}
    <div class="row mb-3 btn-print-hide">
        <div class="col-12">
            <ul class="nav nav-pills nav-pills-custom" id="statistikNavPills" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="pill-all-tab" href="#section-all" data-toggle="pill" role="tab"><i class="fas fa-th-large mr-1"></i> Semua Statistik</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="pill-gender-tab" href="#section-gender" data-toggle="pill" role="tab"><i class="fas fa-venus-mars mr-1"></i> Jenis Kelamin</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="pill-golongan-tab" href="#section-golongan" data-toggle="pill" role="tab"><i class="fas fa-medal mr-1"></i> Golongan / Pangkat</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="pill-pendidikan-tab" href="#section-pendidikan" data-toggle="pill" role="tab"><i class="fas fa-graduation-cap mr-1"></i> Tingkat Pendidikan</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="pill-eselon-tab" href="#section-eselon" data-toggle="pill" role="tab"><i class="fas fa-sitemap mr-1"></i> Eselon Jabatan</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="pill-jenis-jabatan-tab" href="#section-jenis-jabatan" data-toggle="pill" role="tab"><i class="fas fa-id-card-alt mr-1"></i> Jenis Jabatan</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="pill-matrix-tab" href="#section-matrix" data-toggle="pill" role="tab"><i class="fas fa-table mr-1"></i> Eselon x Jenis Kelamin</a>
                </li>
            </ul>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- SEKSI 1 & 6: JENIS KELAMIN --}}
    {{-- ========================================================================= --}}
    <div class="stat-section" id="section-gender-wrapper">
        <div class="row">
            {{-- Grafik Jumlah Pegawai Per Jenis Kelamin --}}
            <div class="col-lg-5 col-md-12 col-12 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header border-bottom-0 pb-0 d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="text-primary font-weight-bold mb-1">Grafik Jumlah Pegawai Per Jenis Kelamin</h4>
                            <p class="text-muted small mb-0">Komposisi seluruh pegawai berdasarkan jenis kelamin</p>
                        </div>
                    </div>
                    <div class="card-body d-flex flex-column justify-content-center">
                        <div class="chart-container-doughnut">
                            <canvas id="chartJenisKelamin"></canvas>
                        </div>
                        <div class="row mt-3 text-center">
                            @foreach($rekapGender as $item)
                                <div class="col-6">
                                    <div class="p-2 border rounded">
                                        <div class="text-muted text-xs font-weight-bold text-uppercase">
                                            <i class="{{ $item['icon'] }} mr-1" style="color: {{ $item['color'] }};"></i> {{ $item['label'] }}
                                        </div>
                                        <div class="h5 font-weight-bold mb-0" style="color: {{ $item['color'] }};">
                                            {{ number_format($item['total']) }}
                                        </div>
                                        <div class="text-muted text-xs">
                                            {{ $item['percentage'] }}%
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            {{-- Ringkasan Komposisi Gender & Kategori Pegawai --}}
            <div class="col-lg-7 col-md-12 col-12 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header border-bottom-0 pb-0">
                        <h4 class="text-primary font-weight-bold mb-1">Ringkasan Komposisi Gender Pegawai</h4>
                        <p class="text-muted small mb-0">Perbandingan jenis kelamin menurut kelompok dosen, tendik, dan status</p>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-rekap mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Jenis Kelamin</th>
                                        <th class="text-center">Dosen</th>
                                        <th class="text-center">Tendik</th>
                                        <th class="text-center">PNS</th>
                                        <th class="text-center">PPPK</th>
                                        <th class="text-center">Total</th>
                                        <th class="text-center" style="width: 140px;">Persentase</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($rekapGender as $item)
                                        <tr>
                                            <td class="font-weight-bold">
                                                <i class="{{ $item['icon'] }} mr-2" style="color: {{ $item['color'] }};"></i>
                                                {{ $item['label'] }}
                                            </td>
                                            <td class="text-center font-weight-600 text-primary">{{ number_format($item['dosen']) }}</td>
                                            <td class="text-center font-weight-600 text-danger">{{ number_format($item['tendik']) }}</td>
                                            <td class="text-center">{{ number_format($item['pns']) }}</td>
                                            <td class="text-center">{{ number_format($item['pppk']) }}</td>
                                            <td class="text-center font-weight-bold" style="color: {{ $item['color'] }};">{{ number_format($item['total']) }}</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="progress flex-grow-1 mr-2 stat-progress-bar">
                                                        <div class="progress-bar" role="progressbar" style="width: {{ $item['percentage'] }}%; background-color: {{ $item['color'] }};" aria-valuenow="{{ $item['percentage'] }}" aria-valuemin="0" aria-valuemax="100"></div>
                                                    </div>
                                                    <span class="small font-weight-bold text-muted">{{ $item['percentage'] }}%</span>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="bg-light font-weight-bold">
                                    <tr>
                                        <td>Total Keseluruhan</td>
                                        <td class="text-center text-primary">{{ number_format($totalDosen) }}</td>
                                        <td class="text-center text-danger">{{ number_format($totalTendik) }}</td>
                                        <td class="text-center">{{ number_format(collect($rekapGender)->sum('pns')) }}</td>
                                        <td class="text-center">{{ number_format(collect($rekapGender)->sum('pppk')) }}</td>
                                        <td class="text-center text-dark">{{ number_format($totalPegawai) }}</td>
                                        <td class="text-center"><span class="badge badge-success stat-badge">100%</span></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Rekapitulasi Jumlah Pegawai Per Jenis Kelamin Berdasarkan Unit Kerja & Status Pegawai --}}
            <div class="col-12 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header border-bottom-0 pb-0 d-flex justify-content-between align-items-center flex-wrap">
                        <div>
                            <h4 class="text-primary font-weight-bold mb-1">Rekapitulasi Jumlah Pegawai Per Jenis Kelamin</h4>
                            <p class="text-muted small mb-0">Rincian data pegawai di setiap unit kerja yang dikelompokkan berdasarkan status pegawai (PNS, CPNS, PPPK, PPPK Paruh Waktu) dan jenis kelamin</p>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped matrix-table mb-0">
                                <thead>
                                    <tr class="bg-light text-dark">
                                        <th rowspan="2" style="width: 50px;" class="text-center">No</th>
                                        <th rowspan="2" style="min-width: 220px;">Unit Kerja</th>
                                        @foreach($statusPegawaiOptions as $st)
                                            <th colspan="3" class="text-center font-weight-bold" style="background-color: #f1f5f9; border-bottom: 2px solid #cbd5e1;">{{ $st }}</th>
                                        @endforeach
                                        <th colspan="3" class="text-center bg-primary text-white font-weight-bold">Total Pegawai</th>
                                        <th rowspan="2" class="text-center" style="width: 100px;">Persentase</th>
                                    </tr>
                                    <tr class="bg-light text-muted small">
                                        @foreach($statusPegawaiOptions as $st)
                                            <th class="text-center text-info" style="width: 60px;">L</th>
                                            <th class="text-center text-danger" style="width: 60px;">P</th>
                                            <th class="text-center font-weight-bold text-dark" style="width: 75px; background-color: #e2e8f0;">Subtotal</th>
                                        @endforeach
                                        <th class="text-center text-info" style="width: 65px;">L</th>
                                        <th class="text-center text-danger" style="width: 65px;">P</th>
                                        <th class="text-center font-weight-bold text-dark" style="width: 80px; background-color: #cbd5e1;">Jumlah</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($rekapUnitKerjaGender as $idx => $row)
                                        <tr>
                                            <td class="text-center">{{ $idx + 1 }}</td>
                                            <td class="font-weight-bold text-dark">{{ $row['unit_kerja'] }}</td>
                                            @foreach($statusPegawaiOptions as $st)
                                                <td class="text-center text-info font-weight-600">{{ number_format($row['status'][$st]['laki_laki'] ?? 0) }}</td>
                                                <td class="text-center text-danger font-weight-600">{{ number_format($row['status'][$st]['perempuan'] ?? 0) }}</td>
                                                <td class="text-center font-weight-bold" style="background-color: #f8fafc;">{{ number_format($row['status'][$st]['subtotal'] ?? 0) }}</td>
                                            @endforeach
                                            <td class="text-center text-info font-weight-bold">{{ number_format($row['total_laki_laki']) }}</td>
                                            <td class="text-center text-danger font-weight-bold">{{ number_format($row['total_perempuan']) }}</td>
                                            <td class="text-center font-weight-bold text-dark" style="background-color: #eef2f7;">{{ number_format($row['total']) }}</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="progress flex-grow-1 mr-2 stat-progress-bar">
                                                        <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $row['percentage'] }}%;" aria-valuenow="{{ $row['percentage'] }}" aria-valuemin="0" aria-valuemax="100"></div>
                                                    </div>
                                                    <span class="small font-weight-bold text-muted">{{ $row['percentage'] }}%</span>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="{{ 2 + (count($statusPegawaiOptions) * 3) + 4 }}" class="text-center text-muted py-3">Belum ada data unit kerja.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                <tfoot class="bg-light font-weight-bold">
                                    <tr>
                                        <td colspan="2" class="text-right">Total Keseluruhan</td>
                                        @foreach($statusPegawaiOptions as $st)
                                            <td class="text-center text-info font-weight-bold">{{ number_format($statusGrandTotals[$st]['laki_laki'] ?? 0) }}</td>
                                            <td class="text-center text-danger font-weight-bold">{{ number_format($statusGrandTotals[$st]['perempuan'] ?? 0) }}</td>
                                            <td class="text-center text-dark font-weight-bold" style="background-color: #e2e8f0;">{{ number_format($statusGrandTotals[$st]['subtotal'] ?? 0) }}</td>
                                        @endforeach
                                        <td class="text-center text-white font-weight-bold bg-info">{{ number_format($totalLakiLaki) }}</td>
                                        <td class="text-center text-white font-weight-bold bg-danger">{{ number_format($totalPerempuan) }}</td>
                                        <td class="text-center text-white font-weight-bold bg-primary">{{ number_format($totalPegawai) }}</td>
                                        <td class="text-center"><span class="badge badge-success stat-badge">100%</span></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- SEKSI 2 & 7: GOLONGAN / PANGKAT --}}
    {{-- ========================================================================= --}}
    <div class="stat-section" id="section-golongan-wrapper">
        <div class="row">
            {{-- Grafik Jumlah Pegawai Per Golongan --}}
            <div class="col-12 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                        <div>
                            <h4 class="text-primary font-weight-bold mb-1">Grafik Jumlah Pegawai Per Golongan</h4>
                            <p class="text-muted small mb-0">Distribusi pegawai berdasarkan tingkatan golongan (Golongan I - IV & PPPK)</p>
                        </div>
                        <div class="card-header-action mt-2 mt-md-0 btn-print-hide">
                            <div class="btn-group btn-group-sm" role="group" id="golonganChartFilter">
                                <button type="button" class="btn btn-primary active" data-filter="group"><i class="fas fa-layer-group mr-1"></i> Kelompok Golongan</button>
                                <button type="button" class="btn btn-outline-primary" data-filter="gender"><i class="fas fa-venus-mars mr-1"></i> Menurut Gender</button>
                                <button type="button" class="btn btn-outline-primary" data-filter="role"><i class="fas fa-users-cog mr-1"></i> Dosen vs Tendik</button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="chart-container-custom">
                            <canvas id="chartGolongan"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Rekapitulasi Jumlah Pegawai Per Golongan Berdasarkan Unit Kerja --}}
            <div class="col-12 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header border-bottom-0 pb-0 d-flex justify-content-between align-items-center flex-wrap">
                        <div>
                            <h4 class="text-primary font-weight-bold mb-1">Rekapitulasi Jumlah Pegawai Per Golongan</h4>
                            <p class="text-muted small mb-0">Rincian data pegawai di setiap unit kerja yang dikelompokkan berdasarkan tingkatan golongan dan jenis kelamin</p>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped matrix-table mb-0">
                                <thead>
                                    <tr class="bg-light text-dark">
                                        <th rowspan="2" style="width: 50px;" class="text-center">No</th>
                                        <th rowspan="2" style="min-width: 220px;">Unit Kerja</th>
                                        @foreach($golonganGroupKeys as $gName => $gMeta)
                                            <th colspan="3" class="text-center font-weight-bold" style="background-color: #f1f5f9; border-bottom: 2px solid #cbd5e1;">{{ $gMeta['label'] }}</th>
                                        @endforeach
                                        <th colspan="3" class="text-center bg-primary text-white font-weight-bold">Total Pegawai</th>
                                        <th rowspan="2" class="text-center" style="width: 100px;">Persentase</th>
                                    </tr>
                                    <tr class="bg-light text-muted small">
                                        @foreach($golonganGroupKeys as $gName => $gMeta)
                                            <th class="text-center text-info" style="width: 55px;">L</th>
                                            <th class="text-center text-danger" style="width: 55px;">P</th>
                                            <th class="text-center font-weight-bold text-dark" style="width: 65px; background-color: #e2e8f0;">Sub</th>
                                        @endforeach
                                        <th class="text-center text-info" style="width: 65px;">L</th>
                                        <th class="text-center text-danger" style="width: 65px;">P</th>
                                        <th class="text-center font-weight-bold text-dark" style="width: 80px; background-color: #cbd5e1;">Jumlah</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($rekapUnitKerjaGolongan as $idx => $row)
                                        <tr>
                                            <td class="text-center">{{ $idx + 1 }}</td>
                                            <td class="font-weight-bold text-dark">{{ $row['unit_kerja'] }}</td>
                                            @foreach(array_keys($golonganGroupKeys) as $gName)
                                                <td class="text-center text-info font-weight-600">{{ number_format($row['golongan'][$gName]['laki_laki'] ?? 0) }}</td>
                                                <td class="text-center text-danger font-weight-600">{{ number_format($row['golongan'][$gName]['perempuan'] ?? 0) }}</td>
                                                <td class="text-center font-weight-bold" style="background-color: #f8fafc;">{{ number_format($row['golongan'][$gName]['subtotal'] ?? 0) }}</td>
                                            @endforeach
                                            <td class="text-center text-info font-weight-bold">{{ number_format($row['total_laki_laki']) }}</td>
                                            <td class="text-center text-danger font-weight-bold">{{ number_format($row['total_perempuan']) }}</td>
                                            <td class="text-center font-weight-bold text-dark" style="background-color: #eef2f7;">{{ number_format($row['total']) }}</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="progress flex-grow-1 mr-2 stat-progress-bar">
                                                        <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $row['percentage'] }}%;" aria-valuenow="{{ $row['percentage'] }}" aria-valuemin="0" aria-valuemax="100"></div>
                                                    </div>
                                                    <span class="small font-weight-bold text-muted">{{ $row['percentage'] }}%</span>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="{{ 2 + (count($golonganGroupKeys) * 3) + 4 }}" class="text-center text-muted py-3">Belum ada data golongan unit kerja.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                <tfoot class="bg-light font-weight-bold">
                                    <tr>
                                        <td colspan="2" class="text-right">Total Keseluruhan</td>
                                        @foreach(array_keys($golonganGroupKeys) as $gName)
                                            <td class="text-center text-info font-weight-bold">{{ number_format($golonganGrandTotals[$gName]['laki_laki'] ?? 0) }}</td>
                                            <td class="text-center text-danger font-weight-bold">{{ number_format($golonganGrandTotals[$gName]['perempuan'] ?? 0) }}</td>
                                            <td class="text-center text-dark font-weight-bold" style="background-color: #e2e8f0;">{{ number_format($golonganGrandTotals[$gName]['subtotal'] ?? 0) }}</td>
                                        @endforeach
                                        <td class="text-center text-white font-weight-bold bg-info">{{ number_format($totalLakiLaki) }}</td>
                                        <td class="text-center text-white font-weight-bold bg-danger">{{ number_format($totalPerempuan) }}</td>
                                        <td class="text-center text-white font-weight-bold bg-primary">{{ number_format($totalPegawai) }}</td>
                                        <td class="text-center"><span class="badge badge-success stat-badge">100%</span></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- SEKSI 3 & 8: TINGKAT PENDIDIKAN --}}
    {{-- ========================================================================= --}}
    <div class="stat-section" id="section-pendidikan-wrapper">
        <div class="row">
            {{-- Grafik Jumlah Pegawai Per Tingkat Pendidikan --}}
            <div class="col-12 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header border-bottom-0 pb-0">
                        <h4 class="text-primary font-weight-bold mb-1">Grafik Jumlah Pegawai Per Tingkat Pendidikan</h4>
                        <p class="text-muted small mb-0">Distribusi kualifikasi pendidikan formal seluruh pegawai</p>
                    </div>
                    <div class="card-body">
                        <div class="chart-container-custom">
                            <canvas id="chartPendidikan"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Rekapitulasi Jumlah Pegawai Per Tingkat Pendidikan Berdasarkan Unit Kerja --}}
            <div class="col-12 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header border-bottom-0 pb-0 d-flex justify-content-between align-items-center flex-wrap">
                        <div>
                            <h4 class="text-primary font-weight-bold mb-1">Rekapitulasi Jumlah Pegawai Per Tingkat Pendidikan</h4>
                            <p class="text-muted small mb-0">Rincian data pegawai di setiap unit kerja yang dikelompokkan berdasarkan jenjang pendidikan dan jenis kelamin</p>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped matrix-table mb-0">
                                <thead>
                                    <tr class="bg-light text-dark">
                                        <th rowspan="2" style="width: 50px;" class="text-center">No</th>
                                        <th rowspan="2" style="min-width: 220px;">Unit Kerja</th>
                                        @foreach($pendidikanMap as $pKey => $pMeta)
                                            <th colspan="3" class="text-center font-weight-bold" style="background-color: #f1f5f9; border-bottom: 2px solid #cbd5e1;">{{ $pMeta['short'] }}</th>
                                        @endforeach
                                        <th colspan="3" class="text-center bg-primary text-white font-weight-bold">Total Pegawai</th>
                                        <th rowspan="2" class="text-center" style="width: 100px;">Persentase</th>
                                    </tr>
                                    <tr class="bg-light text-muted small">
                                        @foreach($pendidikanMap as $pKey => $pMeta)
                                            <th class="text-center text-info" style="width: 50px;">L</th>
                                            <th class="text-center text-danger" style="width: 50px;">P</th>
                                            <th class="text-center font-weight-bold text-dark" style="width: 60px; background-color: #e2e8f0;">Sub</th>
                                        @endforeach
                                        <th class="text-center text-info" style="width: 65px;">L</th>
                                        <th class="text-center text-danger" style="width: 65px;">P</th>
                                        <th class="text-center font-weight-bold text-dark" style="width: 80px; background-color: #cbd5e1;">Jumlah</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($rekapUnitKerjaPendidikan as $idx => $row)
                                        <tr>
                                            <td class="text-center">{{ $idx + 1 }}</td>
                                            <td class="font-weight-bold text-dark">{{ $row['unit_kerja'] }}</td>
                                            @foreach(array_keys($pendidikanMap) as $pKey)
                                                <td class="text-center text-info font-weight-600">{{ number_format($row['pendidikan'][$pKey]['laki_laki'] ?? 0) }}</td>
                                                <td class="text-center text-danger font-weight-600">{{ number_format($row['pendidikan'][$pKey]['perempuan'] ?? 0) }}</td>
                                                <td class="text-center font-weight-bold" style="background-color: #f8fafc;">{{ number_format($row['pendidikan'][$pKey]['subtotal'] ?? 0) }}</td>
                                            @endforeach
                                            <td class="text-center text-info font-weight-bold">{{ number_format($row['total_laki_laki']) }}</td>
                                            <td class="text-center text-danger font-weight-bold">{{ number_format($row['total_perempuan']) }}</td>
                                            <td class="text-center font-weight-bold text-dark" style="background-color: #eef2f7;">{{ number_format($row['total']) }}</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="progress flex-grow-1 mr-2 stat-progress-bar">
                                                        <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $row['percentage'] }}%;" aria-valuenow="{{ $row['percentage'] }}" aria-valuemin="0" aria-valuemax="100"></div>
                                                    </div>
                                                    <span class="small font-weight-bold text-muted">{{ $row['percentage'] }}%</span>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="{{ 2 + (count($pendidikanMap) * 3) + 4 }}" class="text-center text-muted py-3">Belum ada data pendidikan unit kerja.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                <tfoot class="bg-light font-weight-bold">
                                    <tr>
                                        <td colspan="2" class="text-right">Total Keseluruhan</td>
                                        @foreach(array_keys($pendidikanMap) as $pKey)
                                            <td class="text-center text-info font-weight-bold">{{ number_format($pendidikanGrandTotals[$pKey]['laki_laki'] ?? 0) }}</td>
                                            <td class="text-center text-danger font-weight-bold">{{ number_format($pendidikanGrandTotals[$pKey]['perempuan'] ?? 0) }}</td>
                                            <td class="text-center text-dark font-weight-bold" style="background-color: #e2e8f0;">{{ number_format($pendidikanGrandTotals[$pKey]['subtotal'] ?? 0) }}</td>
                                        @endforeach
                                        <td class="text-center text-white font-weight-bold bg-info">{{ number_format($totalLakiLaki) }}</td>
                                        <td class="text-center text-white font-weight-bold bg-danger">{{ number_format($totalPerempuan) }}</td>
                                        <td class="text-center text-white font-weight-bold bg-primary">{{ number_format($totalPegawai) }}</td>
                                        <td class="text-center"><span class="badge badge-success stat-badge">100%</span></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- SEKSI 4 & 9: ESELON JABATAN --}}
    {{-- ========================================================================= --}}
    <div class="stat-section" id="section-eselon-wrapper">
        <div class="row">
            {{-- Grafik Jumlah Pegawai Per Eselon Jabatan --}}
            <div class="col-12 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header border-bottom-0 pb-0">
                        <h4 class="text-primary font-weight-bold mb-1">Grafik Jumlah Pegawai Per Eselon Jabatan</h4>
                        <p class="text-muted small mb-0">Struktur pegawai menurut jenjang eselon jabatan</p>
                    </div>
                    <div class="card-body">
                        <div class="chart-container-custom">
                            <canvas id="chartEselon"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Rekapitulasi Jumlah Pegawai Per Eselon Jabatan Berdasarkan Unit Kerja --}}
            <div class="col-12 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header border-bottom-0 pb-0 d-flex justify-content-between align-items-center flex-wrap">
                        <div>
                            <h4 class="text-primary font-weight-bold mb-1">Rekapitulasi Jumlah Pegawai Per Eselon Jabatan</h4>
                            <p class="text-muted small mb-0">Rincian data pegawai di setiap unit kerja yang dikelompokkan berdasarkan tingkatan eselon dan jenis kelamin</p>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped matrix-table mb-0">
                                <thead>
                                    <tr class="bg-light text-dark">
                                        <th rowspan="2" style="width: 50px;" class="text-center">No</th>
                                        <th rowspan="2" style="min-width: 220px;">Unit Kerja</th>
                                        @foreach($eselonGroupDefinitions as $eName => $eMeta)
                                            <th colspan="3" class="text-center font-weight-bold" style="background-color: #f1f5f9; border-bottom: 2px solid #cbd5e1;">{{ $eMeta['label'] }}</th>
                                        @endforeach
                                        <th colspan="3" class="text-center bg-primary text-white font-weight-bold">Total Pegawai</th>
                                        <th rowspan="2" class="text-center" style="width: 100px;">Persentase</th>
                                    </tr>
                                    <tr class="bg-light text-muted small">
                                        @foreach($eselonGroupDefinitions as $eName => $eMeta)
                                            <th class="text-center text-info" style="width: 55px;">L</th>
                                            <th class="text-center text-danger" style="width: 55px;">P</th>
                                            <th class="text-center font-weight-bold text-dark" style="width: 65px; background-color: #e2e8f0;">Sub</th>
                                        @endforeach
                                        <th class="text-center text-info" style="width: 65px;">L</th>
                                        <th class="text-center text-danger" style="width: 65px;">P</th>
                                        <th class="text-center font-weight-bold text-dark" style="width: 80px; background-color: #cbd5e1;">Jumlah</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($rekapUnitKerjaEselon as $idx => $row)
                                        <tr>
                                            <td class="text-center">{{ $idx + 1 }}</td>
                                            <td class="font-weight-bold text-dark">{{ $row['unit_kerja'] }}</td>
                                            @foreach(array_keys($eselonGroupDefinitions) as $eName)
                                                <td class="text-center text-info font-weight-600">{{ number_format($row['eselon'][$eName]['laki_laki'] ?? 0) }}</td>
                                                <td class="text-center text-danger font-weight-600">{{ number_format($row['eselon'][$eName]['perempuan'] ?? 0) }}</td>
                                                <td class="text-center font-weight-bold" style="background-color: #f8fafc;">{{ number_format($row['eselon'][$eName]['subtotal'] ?? 0) }}</td>
                                            @endforeach
                                            <td class="text-center text-info font-weight-bold">{{ number_format($row['total_laki_laki']) }}</td>
                                            <td class="text-center text-danger font-weight-bold">{{ number_format($row['total_perempuan']) }}</td>
                                            <td class="text-center font-weight-bold text-dark" style="background-color: #eef2f7;">{{ number_format($row['total']) }}</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="progress flex-grow-1 mr-2 stat-progress-bar">
                                                        <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $row['percentage'] }}%;" aria-valuenow="{{ $row['percentage'] }}" aria-valuemin="0" aria-valuemax="100"></div>
                                                    </div>
                                                    <span class="small font-weight-bold text-muted">{{ $row['percentage'] }}%</span>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="{{ 2 + (count($eselonGroupDefinitions) * 3) + 4 }}" class="text-center text-muted py-3">Belum ada data eselon unit kerja.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                <tfoot class="bg-light font-weight-bold">
                                    <tr>
                                        <td colspan="2" class="text-right">Total Keseluruhan</td>
                                        @foreach(array_keys($eselonGroupDefinitions) as $eName)
                                            <td class="text-center text-info font-weight-bold">{{ number_format($eselonGrandTotals[$eName]['laki_laki'] ?? 0) }}</td>
                                            <td class="text-center text-danger font-weight-bold">{{ number_format($eselonGrandTotals[$eName]['perempuan'] ?? 0) }}</td>
                                            <td class="text-center text-dark font-weight-bold" style="background-color: #e2e8f0;">{{ number_format($eselonGrandTotals[$eName]['subtotal'] ?? 0) }}</td>
                                        @endforeach
                                        <td class="text-center text-white font-weight-bold bg-info">{{ number_format($totalLakiLaki) }}</td>
                                        <td class="text-center text-white font-weight-bold bg-danger">{{ number_format($totalPerempuan) }}</td>
                                        <td class="text-center text-white font-weight-bold bg-primary">{{ number_format($totalPegawai) }}</td>
                                        <td class="text-center"><span class="badge badge-success stat-badge">100%</span></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- SEKSI 5 & 10: JENIS JABATAN --}}
    {{-- ========================================================================= --}}
    <div class="stat-section" id="section-jenis-jabatan-wrapper">
        <div class="row">
            {{-- Grafik Jumlah Pegawai Per Jenis Jabatan --}}
            <div class="col-12 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header border-bottom-0 pb-0">
                        <h4 class="text-primary font-weight-bold mb-1">Grafik Jumlah Pegawai Per Jenis Jabatan</h4>
                        <p class="text-muted small mb-0">Distribusi jabatan struktural, fungsional tertentu/umum, dan rangkap</p>
                    </div>
                    <div class="card-body">
                        <div class="chart-container-custom">
                            <canvas id="chartJenisJabatan"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Rekapitulasi Jumlah Pegawai Per Jenis Jabatan Berdasarkan Unit Kerja --}}
            <div class="col-12 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header border-bottom-0 pb-0 d-flex justify-content-between align-items-center flex-wrap">
                        <div>
                            <h4 class="text-primary font-weight-bold mb-1">Rekapitulasi Jumlah Pegawai Per Jenis Jabatan</h4>
                            <p class="text-muted small mb-0">Rincian data pegawai di setiap unit kerja yang dikelompokkan berdasarkan jenis jabatan dan jenis kelamin</p>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped matrix-table mb-0">
                                <thead>
                                    <tr class="bg-light text-dark">
                                        <th rowspan="2" style="width: 50px;" class="text-center">No</th>
                                        <th rowspan="2" style="min-width: 220px;">Unit Kerja</th>
                                        @foreach($rekapJenisJabatan as $jjItem)
                                            <th colspan="3" class="text-center font-weight-bold" style="background-color: #f1f5f9; border-bottom: 2px solid #cbd5e1;">{{ $jjItem['label'] }}</th>
                                        @endforeach
                                        <th colspan="3" class="text-center bg-primary text-white font-weight-bold">Total Pegawai</th>
                                        <th rowspan="2" class="text-center" style="width: 100px;">Persentase</th>
                                    </tr>
                                    <tr class="bg-light text-muted small">
                                        @foreach($rekapJenisJabatan as $jjItem)
                                            <th class="text-center text-info" style="width: 55px;">L</th>
                                            <th class="text-center text-danger" style="width: 55px;">P</th>
                                            <th class="text-center font-weight-bold text-dark" style="width: 65px; background-color: #e2e8f0;">Sub</th>
                                        @endforeach
                                        <th class="text-center text-info" style="width: 65px;">L</th>
                                        <th class="text-center text-danger" style="width: 65px;">P</th>
                                        <th class="text-center font-weight-bold text-dark" style="width: 80px; background-color: #cbd5e1;">Jumlah</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($rekapUnitKerjaJenisJabatan as $idx => $row)
                                        <tr>
                                            <td class="text-center">{{ $idx + 1 }}</td>
                                            <td class="font-weight-bold text-dark">{{ $row['unit_kerja'] }}</td>
                                            @foreach($rekapJenisJabatan as $jjItem)
                                                @php $jKey = $jjItem['label']; @endphp
                                                <td class="text-center text-info font-weight-600">{{ number_format($row['jenis_jabatan'][$jKey]['laki_laki'] ?? 0) }}</td>
                                                <td class="text-center text-danger font-weight-600">{{ number_format($row['jenis_jabatan'][$jKey]['perempuan'] ?? 0) }}</td>
                                                <td class="text-center font-weight-bold" style="background-color: #f8fafc;">{{ number_format($row['jenis_jabatan'][$jKey]['subtotal'] ?? 0) }}</td>
                                            @endforeach
                                            <td class="text-center text-info font-weight-bold">{{ number_format($row['total_laki_laki']) }}</td>
                                            <td class="text-center text-danger font-weight-bold">{{ number_format($row['total_perempuan']) }}</td>
                                            <td class="text-center font-weight-bold text-dark" style="background-color: #eef2f7;">{{ number_format($row['total']) }}</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="progress flex-grow-1 mr-2 stat-progress-bar">
                                                        <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $row['percentage'] }}%;" aria-valuenow="{{ $row['percentage'] }}" aria-valuemin="0" aria-valuemax="100"></div>
                                                    </div>
                                                    <span class="small font-weight-bold text-muted">{{ $row['percentage'] }}%</span>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="{{ 2 + (count($rekapJenisJabatan) * 3) + 4 }}" class="text-center text-muted py-3">Belum ada data jenis jabatan unit kerja.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                <tfoot class="bg-light font-weight-bold">
                                    <tr>
                                        <td colspan="2" class="text-right">Total Keseluruhan</td>
                                        @foreach($rekapJenisJabatan as $jjItem)
                                            @php $jKey = $jjItem['label']; @endphp
                                            <td class="text-center text-info font-weight-bold">{{ number_format($jenisJabatanGrandTotals[$jKey]['laki_laki'] ?? 0) }}</td>
                                            <td class="text-center text-danger font-weight-bold">{{ number_format($jenisJabatanGrandTotals[$jKey]['perempuan'] ?? 0) }}</td>
                                            <td class="text-center text-dark font-weight-bold" style="background-color: #e2e8f0;">{{ number_format($jenisJabatanGrandTotals[$jKey]['subtotal'] ?? 0) }}</td>
                                        @endforeach
                                        <td class="text-center text-white font-weight-bold bg-info">{{ number_format($totalLakiLaki) }}</td>
                                        <td class="text-center text-white font-weight-bold bg-danger">{{ number_format($totalPerempuan) }}</td>
                                        <td class="text-center text-white font-weight-bold bg-primary">{{ number_format($totalPegawai) }}</td>
                                        <td class="text-center"><span class="badge badge-success stat-badge">100%</span></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- SEKSI 11: REKAPITULASI ESELON JABATAN DAN JENIS KELAMIN (CROSS-TABULATION) --}}
    {{-- ========================================================================= --}}
    <div class="stat-section" id="section-matrix-wrapper">
        <div class="row">
            <div class="col-12 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header border-bottom-0 pb-0 d-flex justify-content-between align-items-center flex-wrap">
                        <div>
                            <h4 class="text-primary font-weight-bold mb-1">Rekapitulasi Jumlah Pegawai Per Eselon Jabatan dan Jenis Kelamin</h4>
                            <p class="text-muted small mb-0">Tabel silang (Cross-Tabulation Matrix) korelasi tingkat eselon jabatan dengan jenis kelamin & kelompok pegawai</p>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped matrix-table mb-0">
                                <thead>
                                    <tr class="bg-light text-dark">
                                        <th rowspan="2" style="width: 200px;">Tingkat Eselon Jabatan</th>
                                        <th colspan="3" class="bg-info text-white"><i class="fas fa-mars mr-1"></i> Laki-laki</th>
                                        <th colspan="3" class="bg-danger text-white"><i class="fas fa-venus mr-1"></i> Perempuan</th>
                                        <th colspan="3" class="bg-primary text-white"><i class="fas fa-users mr-1"></i> Total Pegawai</th>
                                        <th rowspan="2" style="width: 150px;">Rasio Gender (L : P)</th>
                                    </tr>
                                    <tr class="bg-light text-muted small">
                                        {{-- Laki-laki subcolumns --}}
                                        <th class="text-center" style="width: 80px;">Dosen</th>
                                        <th class="text-center" style="width: 80px;">Tendik</th>
                                        <th class="text-center font-weight-bold" style="width: 90px;">Subtotal</th>
                                        {{-- Perempuan subcolumns --}}
                                        <th class="text-center" style="width: 80px;">Dosen</th>
                                        <th class="text-center" style="width: 80px;">Tendik</th>
                                        <th class="text-center font-weight-bold" style="width: 90px;">Subtotal</th>
                                        {{-- Total subcolumns --}}
                                        <th class="text-center" style="width: 80px;">Dosen</th>
                                        <th class="text-center" style="width: 80px;">Tendik</th>
                                        <th class="text-center font-weight-bold" style="width: 95px;">Jumlah</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($rekapEselonGenderMatrix as $m)
                                        <tr>
                                            <td class="font-weight-bold">
                                                <span class="chart-legend-dot mr-2 d-inline-block rounded-circle" style="width: 10px; height: 10px; background-color: {{ $m['color'] }};"></span>
                                                {{ $m['eselon_group'] }}
                                            </td>
                                            {{-- Laki-laki --}}
                                            <td class="text-center text-muted">-</td>
                                            <td class="text-center text-muted">-</td>
                                            <td class="text-center font-weight-bold text-info bg-light-info">{{ number_format($m['laki_laki']) }}</td>
                                            {{-- Perempuan --}}
                                            <td class="text-center text-muted">-</td>
                                            <td class="text-center text-muted">-</td>
                                            <td class="text-center font-weight-bold text-danger bg-light-danger">{{ number_format($m['perempuan']) }}</td>
                                            {{-- Total --}}
                                            <td class="text-center text-primary font-weight-600">{{ number_format($m['dosen']) }}</td>
                                            <td class="text-center text-warning font-weight-600">{{ number_format($m['tendik']) }}</td>
                                            <td class="text-center font-weight-bold text-dark" style="background-color: #f1f3f9;">{{ number_format($m['total']) }}</td>
                                            {{-- Rasio Bar --}}
                                            <td>
                                                <div class="ratio-bar-container mb-1" title="Laki-laki: {{ $m['row_ratio_l'] }}% | Perempuan: {{ $m['row_ratio_p'] }}%">
                                                    <div style="width: {{ $m['row_ratio_l'] }}%; background-color: #3abaf4;"></div>
                                                    <div style="width: {{ $m['row_ratio_p'] }}%; background-color: #fc544b;"></div>
                                                </div>
                                                <div class="d-flex justify-content-between text-xs text-muted">
                                                    <span class="text-info font-weight-600">{{ $m['row_ratio_l'] }}% L</span>
                                                    <span class="text-danger font-weight-600">{{ $m['row_ratio_p'] }}% P</span>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="bg-light font-weight-bold">
                                    <tr>
                                        <td>Total Keseluruhan</td>
                                        <td colspan="2" class="text-center text-muted">-</td>
                                        <td class="text-center text-info font-weight-bold bg-info text-white">{{ number_format($totalLakiLaki) }}</td>
                                        <td colspan="2" class="text-center text-muted">-</td>
                                        <td class="text-center text-danger font-weight-bold bg-danger text-white">{{ number_format($totalPerempuan) }}</td>
                                        <td class="text-center text-primary font-weight-bold">{{ number_format($totalDosen) }}</td>
                                        <td class="text-center text-warning font-weight-bold">{{ number_format($totalTendik) }}</td>
                                        <td class="text-center text-white font-weight-bold bg-primary">{{ number_format($totalPegawai) }}</td>
                                        <td class="text-center">
                                            <span class="badge badge-primary font-weight-bold">
                                                {{ $totalPegawai > 0 ? round(($totalLakiLaki / $totalPegawai) * 100, 1) : 0 }}% L : {{ $totalPegawai > 0 ? round(($totalPerempuan / $totalPegawai) * 100, 1) : 0 }}% P
                                            </span>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('plugins_js')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    @endpush

    @push('page_js')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Setup Tab Navigasi Smooth Scrolling & Filter
            $('#statistikNavPills a').on('click', function(e) {
                e.preventDefault();
                var targetId = $(this).attr('href');
                $('#statistikNavPills a').removeClass('active');
                $(this).addClass('active');

                if (targetId === '#section-all') {
                    $('.stat-section').fadeIn(200);
                } else {
                    var wrapperId = targetId + '-wrapper';
                    $('.stat-section').hide();
                    $(wrapperId).fadeIn(200);
                }
            });

            // -------------------------------------------------------------
            // 1. CHART JENIS KELAMIN (Doughnut)
            // -------------------------------------------------------------
            const ctxGender = document.getElementById('chartJenisKelamin');
            if (ctxGender) {
                new Chart(ctxGender, {
                    type: 'doughnut',
                    data: {
                        labels: ['Laki-laki', 'Perempuan'],
                        datasets: [{
                            data: [{{ $totalLakiLaki }}, {{ $totalPerempuan }}],
                            backgroundColor: ['#3abaf4', '#fc544b'],
                            borderColor: '#ffffff',
                            borderWidth: 3,
                            hoverOffset: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    boxWidth: 14,
                                    font: { family: "'Nunito', sans-serif", size: 12, weight: '600' },
                                    padding: 16
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const value = context.parsed || 0;
                                        const total = {{ $totalPegawai }};
                                        const pct = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                        return ` ${context.label}: ${value} Pegawai (${pct}%)`;
                                    }
                                }
                            }
                        },
                        cutout: '65%'
                    }
                });
            }

            // -------------------------------------------------------------
            // 2. CHART GOLONGAN (Bar Chart with Switchable Modes)
            // -------------------------------------------------------------
            const ctxGolongan = document.getElementById('chartGolongan');
            let chartGolonganInstance = null;

            const golonganGroupLabels = {!! json_encode(collect($rekapGolonganGroup)->pluck('name')) !!};
            const golonganGroupTotals = {!! json_encode(collect($rekapGolonganGroup)->pluck('total')) !!};
            const golonganGroupColors = {!! json_encode(collect($rekapGolonganGroup)->pluck('color')) !!};
            const golonganGroupLaki = {!! json_encode(collect($rekapGolonganGroup)->pluck('laki_laki')) !!};
            const golonganGroupPerempuan = {!! json_encode(collect($rekapGolonganGroup)->pluck('perempuan')) !!};
            const golonganGroupDosen = {!! json_encode(collect($rekapGolonganGroup)->pluck('dosen')) !!};
            const golonganGroupTendik = {!! json_encode(collect($rekapGolonganGroup)->pluck('tendik')) !!};

            function renderGolonganChart(mode) {
                if (chartGolonganInstance) {
                    chartGolonganInstance.destroy();
                }

                let datasets = [];
                if (mode === 'gender') {
                    datasets = [
                        {
                            label: 'Laki-laki',
                            data: golonganGroupLaki,
                            backgroundColor: '#3abaf4',
                            borderRadius: 6
                        },
                        {
                            label: 'Perempuan',
                            data: golonganGroupPerempuan,
                            backgroundColor: '#fc544b',
                            borderRadius: 6
                        }
                    ];
                } else if (mode === 'role') {
                    datasets = [
                        {
                            label: 'Dosen',
                            data: golonganGroupDosen,
                            backgroundColor: '#6777ef',
                            borderRadius: 6
                        },
                        {
                            label: 'Tendik',
                            data: golonganGroupTendik,
                            backgroundColor: '#ffa426',
                            borderRadius: 6
                        }
                    ];
                } else {
                    datasets = [
                        {
                            label: 'Total Pegawai',
                            data: golonganGroupTotals,
                            backgroundColor: golonganGroupColors,
                            borderRadius: 6
                        }
                    ];
                }

                if (ctxGolongan) {
                    chartGolonganInstance = new Chart(ctxGolongan, {
                        type: 'bar',
                        data: {
                            labels: golonganGroupLabels,
                            datasets: datasets
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: mode !== 'group',
                                    position: 'top',
                                    labels: { font: { family: "'Nunito', sans-serif", size: 12, weight: '600' } }
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            const total = {{ $totalPegawai }};
                                            const pct = total > 0 ? ((context.parsed.y / total) * 100).toFixed(1) : 0;
                                            return ` ${context.dataset.label}: ${context.parsed.y} Pegawai (${pct}%)`;
                                        }
                                    }
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    grid: { color: '#f0f3f6' },
                                    ticks: { font: { family: "'Nunito', sans-serif" }, precision: 0 }
                                },
                                x: {
                                    grid: { display: false },
                                    ticks: { font: { family: "'Nunito', sans-serif", weight: '600' } }
                                }
                            }
                        }
                    });
                }
            }

            renderGolonganChart('group');

            $('#golonganChartFilter button').on('click', function() {
                $('#golonganChartFilter button').removeClass('active btn-primary').addClass('btn-outline-primary');
                $(this).addClass('active btn-primary').removeClass('btn-outline-primary');
                const filter = $(this).data('filter');
                renderGolonganChart(filter);
            });

            // -------------------------------------------------------------
            // 3. CHART PENDIDIKAN (Horizontal Bar Chart)
            // -------------------------------------------------------------
            const ctxPendidikan = document.getElementById('chartPendidikan');
            if (ctxPendidikan) {
                const pendidikanLabels = {!! json_encode(collect($rekapPendidikan)->pluck('label')) !!};
                const pendidikanTotals = {!! json_encode(collect($rekapPendidikan)->pluck('total')) !!};
                const pendidikanColors = {!! json_encode(collect($rekapPendidikan)->pluck('color')) !!};

                new Chart(ctxPendidikan, {
                    type: 'bar',
                    data: {
                        labels: pendidikanLabels,
                        datasets: [{
                            label: 'Jumlah Pegawai',
                            data: pendidikanTotals,
                            backgroundColor: pendidikanColors,
                            borderRadius: 6
                        }]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const total = {{ $totalPegawai }};
                                        const pct = total > 0 ? ((context.parsed.x / total) * 100).toFixed(1) : 0;
                                        return ` Jumlah: ${context.parsed.x} Pegawai (${pct}%)`;
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                beginAtZero: true,
                                grid: { color: '#f0f3f6' },
                                ticks: { precision: 0 }
                            },
                            y: {
                                grid: { display: false },
                                ticks: { font: { family: "'Nunito', sans-serif", weight: '600' } }
                            }
                        }
                    }
                });
            }

            // -------------------------------------------------------------
            // 4. CHART ESELON (Vertical Bar Chart)
            // -------------------------------------------------------------
            const ctxEselon = document.getElementById('chartEselon');
            if (ctxEselon) {
                const eselonLabels = {!! json_encode(collect($rekapEselonGroup)->pluck('name')) !!};
                const eselonTotals = {!! json_encode(collect($rekapEselonGroup)->pluck('total')) !!};
                const eselonColors = {!! json_encode(collect($rekapEselonGroup)->pluck('color')) !!};

                new Chart(ctxEselon, {
                    type: 'bar',
                    data: {
                        labels: eselonLabels,
                        datasets: [{
                            label: 'Jumlah Pegawai',
                            data: eselonTotals,
                            backgroundColor: eselonColors,
                            borderRadius: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const total = {{ $totalPegawai }};
                                        const pct = total > 0 ? ((context.parsed.y / total) * 100).toFixed(1) : 0;
                                        return ` ${context.label}: ${context.parsed.y} Pegawai (${pct}%)`;
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: { color: '#f0f3f6' },
                                ticks: { precision: 0 }
                            },
                            x: {
                                grid: { display: false },
                                ticks: { font: { family: "'Nunito', sans-serif", weight: '600' } }
                            }
                        }
                    }
                });
            }

            // -------------------------------------------------------------
            // 5. CHART JENIS JABATAN (Bar Chart)
            // -------------------------------------------------------------
            const ctxJenisJabatan = document.getElementById('chartJenisJabatan');
            if (ctxJenisJabatan) {
                const jjLabels = {!! json_encode(collect($rekapJenisJabatan)->pluck('label')) !!};
                const jjTotals = {!! json_encode(collect($rekapJenisJabatan)->pluck('total')) !!};
                const jjColors = {!! json_encode(collect($rekapJenisJabatan)->pluck('color')) !!};

                new Chart(ctxJenisJabatan, {
                    type: 'bar',
                    data: {
                        labels: jjLabels,
                        datasets: [{
                            label: 'Jumlah Pegawai',
                            data: jjTotals,
                            backgroundColor: jjColors,
                            borderRadius: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const total = {{ $totalPegawai }};
                                        const pct = total > 0 ? ((context.parsed.y / total) * 100).toFixed(1) : 0;
                                        return ` ${context.label}: ${context.parsed.y} Pegawai (${pct}%)`;
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: { color: '#f0f3f6' },
                                ticks: { precision: 0 }
                            },
                            x: {
                                grid: { display: false },
                                ticks: { font: { family: "'Nunito', sans-serif", weight: '600' } }
                            }
                        }
                    }
                });
            }
        });
    </script>
    @endpush
</x-app-layout>
