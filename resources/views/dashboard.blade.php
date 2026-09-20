<x-app-layout>
    @section('title', $title)
    <x-slot name="header">
        <h1>{{ $title }}</h1>
    </x-slot>

    {{-- Row 1: Statistic Cards --}}
    <div class="row">
        <div class="col-lg-4 col-md-4 col-sm-4 col-12">
            <div class="card card-statistic-1">
                <div class="card-icon bg-primary">
                    <i class="fas fa-users"></i>
                </div>
                <div class="card-wrap">
                    <div class="card-header">
                        <h4>Jumlah Pegawai</h4>
                    </div>
                    <div class="card-body">
                        {{ $pegawais }}
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-4 col-sm-4 col-12">
            <div class="card card-statistic-1">
                <div class="card-icon bg-warning">
                    <i class="fas fa-user-graduate"></i>
                </div>
                <div class="card-wrap">
                    <div class="card-header">
                        <h4>Jumlah Dosen</h4>
                    </div>
                    <div class="card-body">
                        {{ $dosens }}
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-4 col-sm-4 col-12">
            <div class="card card-statistic-1">
                <div class="card-icon bg-danger">
                    <i class="fas fa-user-tie"></i>
                </div>
                <div class="card-wrap">
                    <div class="card-header">
                        <h4>Jumlah Tendik</h4>
                    </div>
                    <div class="card-body">
                        {{ $tendiks }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Row 2: Grafik Tingkat Pendidikan Pegawai (Dosen & Tendik) --}}
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                    <h4>Jumlah Pegawai Berdasarkan Tingkat Pendidikan</h4>
                    <div class="card-header-action mt-2 mt-md-0">
                        <div class="btn-group btn-group-sm" id="pendidikan-mode-switch" role="group" aria-label="Filter Kelompok Pegawai">
                            <button type="button" class="btn btn-primary active" data-mode="all">
                                <i class="fas fa-users mr-1"></i> Semua Pegawai
                            </button>
                            <button type="button" class="btn btn-outline-primary" data-mode="dosen">
                                <i class="fas fa-user-graduate mr-1"></i> Dosen
                            </button>
                            <button type="button" class="btn btn-outline-primary" data-mode="tendik">
                                <i class="fas fa-user-tie mr-1"></i> Tendik
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @php
                        $pendidikanColors = [
                            's3' => '#6f42c1',
                            's2' => '#6777ef',
                            's1' => '#3abaf4',
                            'd3' => '#47c363',
                            'slta' => '#ffa426',
                            'lainnya' => '#98a6ad',
                        ];

                        $jurusanShortLabels = [
                            1 => 'Sipil',
                            2 => 'Kimia',
                            3 => 'Mesin',
                            4 => 'Elektro',
                            5 => 'Bisnis',
                            6 => 'TIK',
                            7 => 'UPA & Pusat',
                        ];

                        $maxPendidikanTotal = max(1, (int) collect($pegawaiPendidikanChart)->max('total_pegawai'));
                        $scalePendidikan = collect([1, 0.75, 0.5, 0.25, 0])
                            ->map(fn ($r) => (int) round($maxPendidikanTotal * $r))
                            ->unique()
                            ->values()
                            ->all();
                    @endphp

                    <div id="pegawai-pendidikan-bar-chart">
                        {{-- Summary Cards --}}
                        <div class="chart-summary row mb-3">
                            @foreach($pegawaiPendidikanTotals as $item)
                                <div class="col-xl-2 col-lg-4 col-md-4 col-sm-6 col-6 mb-3">
                                    <button type="button" class="chart-summary-card chart-summary-button h-100 w-100 text-left" data-pendidikan-key="{{ $item['key'] }}" aria-pressed="false">
                                        <div class="d-flex align-items-center mb-1">
                                            <span class="chart-legend-color mr-2 rounded" style="background-color: {{ $pendidikanColors[$item['key']] ?? '#6c757d' }};"></span>
                                            <span class="text-small text-muted font-weight-bold">{{ $item['label'] }}</span>
                                        </div>
                                        <div class="chart-summary-count mb-1" data-count-all="{{ $item['count'] }}" data-count-dosen="{{ $item['dosen_count'] }}" data-count-tendik="{{ $item['tendik_count'] }}">
                                            {{ $item['count'] }}
                                        </div>
                                        <div class="text-muted text-xs d-flex justify-content-between">
                                            <span>Dosen: <strong class="text-primary">{{ $item['dosen_count'] }}</strong></span>
                                            <span>Tendik: <strong class="text-danger">{{ $item['tendik_count'] }}</strong></span>
                                        </div>
                                    </button>
                                </div>
                            @endforeach
                        </div>

                        {{-- Legend --}}
                        <div class="chart-legend d-flex flex-wrap mb-3">
                            @foreach($pegawaiPendidikanTotals as $item)
                                <div class="chart-legend-item d-flex align-items-center mr-3 mb-1">
                                    <span class="chart-legend-color mr-2 rounded" style="background-color: {{ $pendidikanColors[$item['key']] ?? '#6c757d' }};"></span>
                                    <span class="text-small">{{ $item['label'] }}</span>
                                </div>
                            @endforeach
                        </div>

                        {{-- Visual Chart (No Horizontal Scroll) --}}
                        <div class="chart-visual">
                            {{-- Sumbu Y Terisolasi Setinggi Chart Bars --}}
                            <div class="chart-axis-wrapper d-none d-md-flex">
                                <div class="chart-axis-spacer-top"></div>
                                <div class="chart-axis">
                                    @foreach($scalePendidikan as $val)
                                        <span class="chart-axis-val">{{ $val }}</span>
                                    @endforeach
                                </div>
                            </div>

                            <div class="chart-scroll">
                                @foreach($pegawaiPendidikanChart as $row)
                                    @php
                                        $currTotal = $row['total_pegawai'];
                                        $stackHeight = $maxPendidikanTotal > 0 ? ($currTotal / $maxPendidikanTotal) * 100 : 0;
                                    @endphp
                                    <div class="chart-group"
                                         data-unit-id="{{ $row['unit_id'] }}"
                                         data-total-all="{{ $row['total_pegawai'] }}"
                                         data-total-dosen="{{ $row['total_dosen'] }}"
                                         data-total-tendik="{{ $row['total_tendik'] }}"
                                         style="--chart-delay: {{ $loop->index * 70 }}ms;">
                                        <div class="text-muted small chart-unit-total">Total {{ $row['total_pegawai'] }}</div>
                                        <div class="chart-bars">
                                            <div class="chart-stack-wrapper d-flex flex-column align-items-center justify-content-end">
                                                <div class="chart-bar-value small font-weight-bold">{{ $row['total_pegawai'] }}</div>
                                                <div class="chart-stack-shell" title="{{ $row['unit_label'] }}: total {{ $row['total_pegawai'] }}" style="height: {{ $stackHeight }}%; min-height: {{ $currTotal > 0 ? '16px' : '0' }};">
                                                    {{-- Layer: Semua Pegawai --}}
                                                    <div class="chart-mode-layer chart-mode-all">
                                                        @foreach($row['combined_distribution'] as $item)
                                                            @if(($item['count'] ?? 0) > 0)
                                                                @php $segH = $row['total_pegawai'] > 0 ? ($item['count'] / $row['total_pegawai']) * 100 : 0; @endphp
                                                                <div class="chart-stack-segment"
                                                                     data-pendidikan-key="{{ $item['key'] }}"
                                                                     tabindex="0"
                                                                     title="{{ $item['label'] }}: {{ $item['count'] }} (Dosen: {{ $item['dosen_count'] }}, Tendik: {{ $item['tendik_count'] }})"
                                                                     aria-label="{{ $row['unit_label'] }} - {{ $item['label'] }}: {{ $item['count'] }}"
                                                                     style="height: {{ $segH }}%; background-color: {{ $pendidikanColors[$item['key']] ?? '#6c757d' }};"></div>
                                                            @endif
                                                        @endforeach
                                                    </div>
                                                    {{-- Layer: Dosen Saja --}}
                                                    <div class="chart-mode-layer chart-mode-dosen" style="display: none;">
                                                        @foreach($row['dosen_distribution'] as $item)
                                                            @if(($item['count'] ?? 0) > 0)
                                                                @php $segH = $row['total_dosen'] > 0 ? ($item['count'] / $row['total_dosen']) * 100 : 0; @endphp
                                                                <div class="chart-stack-segment"
                                                                     data-pendidikan-key="{{ $item['key'] }}"
                                                                     tabindex="0"
                                                                     title="Dosen {{ $item['label'] }}: {{ $item['count'] }}"
                                                                     aria-label="{{ $row['unit_label'] }} - Dosen {{ $item['label'] }}: {{ $item['count'] }}"
                                                                     style="height: {{ $segH }}%; background-color: {{ $pendidikanColors[$item['key']] ?? '#6c757d' }};"></div>
                                                            @endif
                                                        @endforeach
                                                    </div>
                                                    {{-- Layer: Tendik Saja --}}
                                                    <div class="chart-mode-layer chart-mode-tendik" style="display: none;">
                                                        @foreach($row['tendik_distribution'] as $item)
                                                            @if(($item['count'] ?? 0) > 0)
                                                                @php $segH = $row['total_tendik'] > 0 ? ($item['count'] / $row['total_tendik']) * 100 : 0; @endphp
                                                                <div class="chart-stack-segment"
                                                                     data-pendidikan-key="{{ $item['key'] }}"
                                                                     tabindex="0"
                                                                     title="Tendik {{ $item['label'] }}: {{ $item['count'] }}"
                                                                     aria-label="{{ $row['unit_label'] }} - Tendik {{ $item['label'] }}: {{ $item['count'] }}"
                                                                     style="height: {{ $segH }}%; background-color: {{ $pendidikanColors[$item['key']] ?? '#6c757d' }};"></div>
                                                            @endif
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="chart-group-title font-weight-bold mt-2 mb-2" title="{{ $row['unit_label'] }}">
                                            <span class="d-none d-xl-inline">{{ $row['unit_label'] }}</span>
                                            <span class="d-inline d-xl-none">{{ $jurusanShortLabels[$row['unit_id']] ?? $row['unit_label'] }}</span>
                                            <span class="sr-only">{{ $row['unit_label'] }}</span>
                                        </div>
                                        {{-- Badges: Tampilkan semua tingkat pendidikan secara dinamis tanpa duplikasi --}}
                                        <div class="chart-badges d-none d-md-flex justify-content-center flex-wrap text-muted">
                                            @foreach($row['combined_distribution'] as $item)
                                                <span class="badge {{ ($item['count'] ?? 0) > 0 ? 'badge-light font-weight-bold text-dark' : 'badge-light text-muted chart-badge-zero' }}"
                                                      data-pendidikan-key="{{ $item['key'] }}"
                                                      data-label="{{ $item['label'] }}"
                                                      data-count-all="{{ $item['count'] }}"
                                                      data-count-dosen="{{ $item['dosen_count'] }}"
                                                      data-count-tendik="{{ $item['tendik_count'] }}"
                                                      title="{{ $item['label'] }}: {{ $item['count'] }} (Dosen: {{ $item['dosen_count'] }}, Tendik: {{ $item['tendik_count'] }})">
                                                    {{ $item['label'] }}: <span class="chart-badge-count">{{ $item['count'] }}</span>
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Row 3: Grafik Jabatan Fungsional Dosen per Jurusan --}}
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4>Jumlah Dosen Berdasarkan Jabatan Fungsional per Jurusan</h4>
                </div>
                <div class="card-body">
                    @php
                        $jabatanColors = [
                            'tenaga pengajar' => '#6777ef',
                            'asisten ahli' => '#47c363',
                            'lektor' => '#ffa426',
                            'lektor kepala' => '#fc544b',
                            'profesor' => '#3abaf4',
                        ];

                        $jurusanShortLabelsDosen = [
                            1 => 'Sipil',
                            2 => 'Kimia',
                            3 => 'Mesin',
                            4 => 'Elektro',
                            5 => 'Bisnis',
                            6 => 'TIK',
                        ];

                        $maxJurusanTotal = max(1, (int) collect($jurusanJabatanChart)->max('total'));
                        $chartScaleLabels = collect([1, 0.75, 0.5, 0.25, 0])
                            ->map(fn ($ratio) => (int) round($maxJurusanTotal * $ratio))
                            ->unique()
                            ->values()
                            ->all();
                    @endphp

                    <div id="jurusan-jabatan-bar-chart">
                        <div class="chart-summary row mb-3">
                            @foreach($jabatanFungsionalTotals as $item)
                                <div class="col-xl col-lg-4 col-md-4 col-sm-6 col-6 mb-3">
                                    <button type="button" class="chart-summary-card chart-summary-button h-100 w-100 text-left" data-jabatan-key="{{ $item['key'] }}" aria-pressed="false">
                                        <div class="d-flex align-items-center mb-2">
                                            <span class="chart-legend-color mr-2 rounded" style="background-color: {{ $jabatanColors[$item['key']] ?? '#6c757d' }};"></span>
                                            <span class="text-small text-muted">{{ $item['label'] }}</span>
                                        </div>
                                        <div class="chart-summary-count">{{ $item['count'] }}</div>
                                    </button>
                                </div>
                            @endforeach
                        </div>

                        <div class="chart-legend d-flex flex-wrap mb-3">
                            @foreach(($jurusanJabatanChart[0]['distribution'] ?? []) as $item)
                                <div class="chart-legend-item d-flex align-items-center mr-3 mb-1">
                                    <span class="chart-legend-color mr-2 rounded" style="background-color: {{ $jabatanColors[$item['key']] ?? '#6c757d' }};"></span>
                                    <span class="text-small">{{ $item['label'] }}</span>
                                </div>
                            @endforeach
                        </div>

                        <div class="chart-visual">
                            <div class="chart-axis-wrapper d-none d-md-flex">
                                <div class="chart-axis-spacer-top"></div>
                                <div class="chart-axis">
                                    @foreach($chartScaleLabels as $value)
                                        <span class="chart-axis-val">{{ $value }}</span>
                                    @endforeach
                                </div>
                            </div>

                            <div class="chart-scroll">
                                @foreach($jurusanJabatanChart as $row)
                                    <div class="chart-group" style="--chart-delay: {{ $loop->index * 80 }}ms;">
                                        <div class="text-muted small chart-unit-total">Total {{ $row['total'] }}</div>
                                        <div class="chart-bars">
                                            @php
                                                $stackHeight = $maxJurusanTotal > 0
                                                    ? ($row['total'] / $maxJurusanTotal) * 100
                                                    : 0;

                                                $segments = collect($row['distribution'])
                                                    ->filter(fn ($item) => ($item['count'] ?? 0) > 0)
                                                    ->values()
                                                    ->all();
                                            @endphp
                                            <div class="chart-stack-wrapper d-flex flex-column align-items-center justify-content-end">
                                                <div class="chart-bar-value small font-weight-bold">{{ $row['total'] }}</div>
                                                <div class="chart-stack-shell" title="{{ $row['jurusan_label'] }}: total {{ $row['total'] }}" style="height: {{ $stackHeight }}%; min-height: {{ $row['total'] > 0 ? '16px' : '0' }};">
                                                    @if ($segments === [])
                                                        <div class="chart-stack-empty"></div>
                                                    @else
                                                        @foreach ($segments as $item)
                                                            @php
                                                                $segmentHeight = $row['total'] > 0
                                                                    ? ($item['count'] / $row['total']) * 100
                                                                    : 0;
                                                            @endphp
                                                            <div class="chart-stack-segment" data-jabatan-key="{{ $item['key'] }}" tabindex="0" aria-label="{{ $row['jurusan_label'] }} - {{ $item['label'] }}: {{ $item['count'] }}" title="{{ $item['label'] }}: {{ $item['count'] }}" style="height: {{ $segmentHeight }}%; background-color: {{ $jabatanColors[$item['key']] ?? '#6c757d' }};"></div>
                                                        @endforeach
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        <div class="chart-group-title font-weight-bold mt-2 mb-2" title="{{ $row['jurusan_label'] }}">
                                            <span class="d-none d-xl-inline">{{ $row['jurusan_label'] }}</span>
                                            <span class="d-inline d-xl-none">{{ $jurusanShortLabelsDosen[$row['jurusan_id']] ?? $row['jurusan_label'] }}</span>
                                            <span class="sr-only">{{ $row['jurusan_label'] }}</span>
                                        </div>
                                        <div class="chart-badges d-none d-md-flex justify-content-center flex-wrap text-muted">
                                            @foreach($row['distribution'] as $item)
                                                <span class="badge {{ ($item['count'] ?? 0) > 0 ? 'badge-light font-weight-bold text-dark' : 'badge-light text-muted chart-badge-zero' }}" data-jabatan-key="{{ $item['key'] }}">{{ $item['label'] }}: {{ $item['count'] }}</span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Row 4: Side-by-Side Charts (Sertifikasi Dosen & Studi Lanjut Dosen) --}}
    <div class="row">
        {{-- Chart Sertifikasi Dosen (Radial Progress Gauges per Jurusan) --}}
        <div class="col-lg-6 col-md-12 col-12 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h4>Jumlah Dosen Berdasarkan Sertifikasi Dosen per Jurusan</h4>
                </div>
                <div class="card-body d-flex flex-column justify-content-between">
                    @php
                        $sudahSertifikasi = collect($sertifikasiDosenTotals)->firstWhere('key', 'sudah_sertifikasi')['count'] ?? 0;
                        $belumSertifikasi = collect($sertifikasiDosenTotals)->firstWhere('key', 'belum_sertifikasi')['count'] ?? 0;
                        $totalDosenSertifikasi = $sudahSertifikasi + $belumSertifikasi;
                        $overallSertifikasiRate = $totalDosenSertifikasi > 0 ? round(($sudahSertifikasi / $totalDosenSertifikasi) * 100, 1) : 0.0;
                    @endphp

                    <div id="jurusan-sertifikasi-radial-chart" class="sertifikasi-radial-container w-100">
                        {{-- Top Summary Metrics --}}
                        <div class="chart-summary row mb-3">
                            <div class="col-6 mb-2">
                                <div class="chart-summary-card h-100">
                                    <div class="d-flex align-items-center mb-1">
                                        <i class="fas fa-certificate text-success mr-2"></i>
                                        <span class="text-small text-muted font-weight-600">Sudah Sertifikasi</span>
                                    </div>
                                    <div class="chart-summary-count text-success">
                                        {{ $sudahSertifikasi }} <span class="badge badge-light text-success font-weight-bold ml-1">{{ $overallSertifikasiRate }}%</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 mb-2">
                                <div class="chart-summary-card h-100">
                                    <div class="d-flex align-items-center mb-1">
                                        <i class="fas fa-exclamation-circle text-warning mr-2"></i>
                                        <span class="text-small text-muted font-weight-600">Belum Sertifikasi</span>
                                    </div>
                                    <div class="chart-summary-count text-dark">
                                        {{ $belumSertifikasi }} <span class="badge badge-light text-muted font-weight-bold ml-1">{{ $totalDosenSertifikasi > 0 ? round(($belumSertifikasi / $totalDosenSertifikasi) * 100, 1) : 0 }}%</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Jurusan Radial Gauges Grid (6 Jurusan) --}}
                        <div class="row" style="row-gap: 0.5rem;">
                            @foreach($jurusanSertifikasiChart as $row)
                                @php
                                    $sudah = collect($row['distribution'])->firstWhere('key', 'sudah_sertifikasi')['count'] ?? 0;
                                    $belum = collect($row['distribution'])->firstWhere('key', 'belum_sertifikasi')['count'] ?? 0;
                                    $jurTotal = (int) $row['total'];
                                    $pct = $jurTotal > 0 ? round(($sudah / $jurTotal) * 100, 1) : 0.0;
                                    $circumference = 175.93; // 2 * pi * 28
                                    $dashLength = round(($pct / 100) * $circumference, 2);

                                    $ringColor = match (true) {
                                        $pct >= 80 => '#47c363',
                                        $pct >= 50 => '#3abaf4',
                                        $pct > 0 => '#ffa426',
                                        default => '#cbd5e1',
                                    };
                                @endphp
                                <div class="col-6 col-sm-4 mb-2">
                                    <div class="radial-gauge-card p-2 text-center h-100 d-flex flex-column align-items-center justify-content-between rounded border">
                                        <div class="radial-gauge-title font-weight-bold text-dark text-truncate w-100" title="{{ $row['jurusan_label'] }}">
                                            {{ $jurusanShortLabelsDosen[$row['jurusan_id']] ?? $row['jurusan_label'] }}
                                        </div>

                                        {{-- Circular Progress Ring --}}
                                        <div class="radial-ring-wrapper my-1 position-relative">
                                            <svg viewBox="0 0 72 72" width="64" height="64" class="radial-ring-svg">
                                                <circle cx="36" cy="36" r="28" fill="transparent" stroke="#f1f3f5" stroke-width="6" />
                                                <circle cx="36" cy="36" r="28" fill="transparent"
                                                    stroke="{{ $ringColor }}"
                                                    stroke-width="6"
                                                    stroke-dasharray="{{ $dashLength }} {{ $circumference }}"
                                                    stroke-linecap="round"
                                                    transform="rotate(-90 36 36)"
                                                    class="radial-ring-progress"
                                                />
                                            </svg>
                                            <div class="radial-ring-text position-absolute font-weight-bold">
                                                {{ $pct }}<span style="font-size: 0.65rem;">%</span>
                                            </div>
                                        </div>

                                        {{-- Stats Breakdown --}}
                                        <div class="radial-gauge-stats w-100 mt-1">
                                            <div class="d-flex justify-content-center" style="gap: 3px;">
                                                <span class="badge {{ $sudah > 0 ? 'badge-light font-weight-bold text-success' : 'badge-light text-muted chart-badge-zero' }}" style="font-size: 0.65rem; padding: 2px 4px;" title="Sudah Sertifikasi: {{ $sudah }}">
                                                    {{ $sudah }} Sudah
                                                </span>
                                                <span class="badge {{ $belum > 0 ? 'badge-light font-weight-bold text-dark' : 'badge-light text-muted chart-badge-zero' }}" style="font-size: 0.65rem; padding: 2px 4px;" title="Belum Sertifikasi: {{ $belum }}">
                                                    {{ $belum }} Belum
                                                </span>
                                            </div>
                                            <div class="text-muted small mt-1 font-weight-600" style="font-size: 0.7rem;">
                                                Total: {{ $jurTotal }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        {{-- Footer Quick Hint --}}
                        <div class="radial-footer-hint text-center text-muted small mt-3 pt-2 border-top">
                            <i class="fas fa-check-circle text-success mr-1"></i> Capaian sertifikasi dosen institusi: <strong class="text-dark">{{ $overallSertifikasiRate }}%</strong> ({{ $sudahSertifikasi }} dari {{ $totalDosenSertifikasi }} Dosen).
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Chart Studi Lanjut Dosen (Donut Chart berdasarkan Bidang Ilmu) --}}
        <div class="col-lg-6 col-md-12 col-12 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h4>Jumlah Dosen Studi Lanjut Berdasarkan Bidang Ilmu</h4>
                </div>
                <div class="card-body d-flex flex-column justify-content-between">
                    @php
                        $topBidang = collect($studiLanjutTotals)->sortByDesc('count')->first();
                    @endphp

                    <div id="studi-lanjut-donut-chart" class="donut-chart-container w-100">
                        {{-- Top KPI Insights Row --}}
                        <div class="chart-summary row mb-3">
                            <div class="col-6 mb-2">
                                <div class="chart-summary-card h-100">
                                    <div class="d-flex align-items-center mb-1">
                                        <i class="fas fa-user-graduate text-primary mr-2"></i>
                                        <span class="text-small text-muted font-weight-600">Total Studi Lanjut</span>
                                    </div>
                                    <div class="chart-summary-count text-primary">
                                        {{ $totalStudiLanjutDosen }} <span class="text-small font-weight-normal text-muted">Dosen</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 mb-2">
                                <div class="chart-summary-card h-100">
                                    <div class="d-flex align-items-center mb-1">
                                        <i class="fas fa-layer-group text-warning mr-2"></i>
                                        <span class="text-small text-muted font-weight-600">Bidang Dominan</span>
                                    </div>
                                    <div class="chart-summary-count text-dark text-truncate" title="{{ $totalStudiLanjutDosen > 0 && ($topBidang['count'] ?? 0) > 0 ? $topBidang['label'] : '-' }}">
                                        @if($totalStudiLanjutDosen > 0 && ($topBidang['count'] ?? 0) > 0)
                                            {{ $topBidang['label'] }} <span class="badge badge-light text-primary font-weight-bold ml-1">{{ $topBidang['percentage'] }}%</span>
                                        @else
                                            <span class="text-muted text-small font-weight-normal">Belum ada data</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Middle Visual & Breakdown Row --}}
                        <div class="row align-items-center">
                            {{-- Donut SVG Visual --}}
                            <div class="col-12 col-sm-5 d-flex justify-content-center mb-3 mb-sm-0">
                                <div class="donut-wrapper">
                                    <svg class="donut-svg" viewBox="0 0 200 200" width="180" height="180" role="img" aria-label="Grafik Donat Dosen Studi Lanjut Berdasarkan Bidang Ilmu">
                                        {{-- Base Track Circle --}}
                                        <circle cx="100" cy="100" r="60" fill="transparent" stroke="#f1f3f5" stroke-width="24" />

                                        {{-- Segments --}}
                                        @if($totalStudiLanjutDosen > 0)
                                            @foreach($studiLanjutDonutSegments as $seg)
                                                @if($seg['count'] > 0)
                                                    <circle class="donut-segment"
                                                        cx="100" cy="100" r="60"
                                                        fill="transparent"
                                                        stroke="{{ $seg['color'] }}"
                                                        stroke-width="24"
                                                        stroke-dasharray="{{ $seg['dash_length'] }} {{ $seg['dash_space'] }}"
                                                        stroke-dashoffset="{{ $seg['dash_offset'] }}"
                                                        data-bidang-key="{{ $seg['key'] }}"
                                                        data-label="{{ $seg['label'] }}"
                                                        data-count="{{ $seg['count'] }}"
                                                        data-percentage="{{ $seg['percentage'] }}%"
                                                        tabindex="0"
                                                        role="button"
                                                        aria-label="{{ $seg['label'] }}: {{ $seg['count'] }} dosen ({{ $seg['percentage'] }}%)"
                                                    />
                                                @endif
                                            @endforeach
                                        @endif
                                    </svg>

                                    {{-- Center Hole Info --}}
                                    <div class="donut-center">
                                        <div class="donut-center-val" data-default-count="{{ $totalStudiLanjutDosen }}">{{ $totalStudiLanjutDosen }}</div>
                                        <div class="donut-center-lbl" data-default-label="Total Dosen">Total Dosen</div>
                                    </div>
                                </div>
                            </div>

                            {{-- Donut Breakdown / Legend --}}
                            <div class="col-12 col-sm-7">
                                <div class="donut-legend-list">
                                    @foreach($studiLanjutTotals as $item)
                                        <div class="donut-legend-card"
                                            data-bidang-key="{{ $item['key'] }}"
                                            data-label="{{ $item['label'] }}"
                                            data-count="{{ $item['count'] }}"
                                            data-percentage="{{ $item['percentage'] }}%"
                                            role="button"
                                            tabindex="0"
                                            aria-label="{{ $item['label'] }}: {{ $item['count'] }} dosen ({{ $item['percentage'] }}%)">
                                            <div class="d-flex flex-column flex-grow-1 min-width-0 mr-2">
                                                <div class="d-flex align-items-center justify-content-between mb-1">
                                                    <div class="d-flex align-items-center min-width-0">
                                                        <span class="donut-bullet mr-2" style="background-color: {{ $item['color'] }};"></span>
                                                        <span class="donut-item-label font-weight-600 text-dark text-truncate">{{ $item['label'] }}</span>
                                                    </div>
                                                    <span class="donut-item-count font-weight-bold text-dark ml-2">{{ $item['count'] }}</span>
                                                </div>
                                                <div class="progress" style="height: 4px; background-color: #f1f3f5;">
                                                    <div class="progress-bar rounded" role="progressbar" style="width: {{ $item['percentage'] }}%; background-color: {{ $item['color'] }};" aria-valuenow="{{ $item['percentage'] }}" aria-valuemin="0" aria-valuemax="100"></div>
                                                </div>
                                            </div>
                                            <span class="badge {{ $item['count'] > 0 ? 'badge-light font-weight-bold text-dark' : 'badge-light text-muted chart-badge-zero' }} flex-shrink-0" style="min-width: 48px; text-align: right;">
                                                {{ $item['percentage'] }}%
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        {{-- Footer Quick Hint --}}
                        <div class="donut-footer-hint text-center text-muted small mt-3 pt-2 border-top">
                            <i class="fas fa-info-circle text-primary mr-1"></i> Arahkan kursor atau klik pada bidang ilmu / segmen donat untuk melihat sorotan interaktif.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('page_css')
        <style>
            .chart-legend {
                gap: .5rem 1rem;
            }

            .chart-summary-card {
                padding: .75rem .85rem;
                border: 1px solid #e9ecef;
                border-radius: .75rem;
                background-color: #fff;
                box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.04);
                transition: border-color .2s ease, box-shadow .2s ease, transform .2s ease, opacity .2s ease;
            }

            .chart-summary-button {
                appearance: none;
                cursor: pointer;
            }

            .chart-summary-button:hover,
            .chart-summary-button:focus {
                border-color: rgba(103, 119, 239, 0.35);
                box-shadow: 0 8px 18px rgba(103, 119, 239, 0.1);
                transform: translateY(-1px);
                outline: none;
            }

            .chart-summary-button.is-active {
                border-color: rgba(103, 119, 239, 0.45);
                box-shadow: 0 10px 20px rgba(103, 119, 239, 0.14);
                background-color: #f8f9ff;
            }

            .chart-summary-button.is-inactive {
                opacity: .55;
            }

            .chart-summary-count {
                font-size: 1.35rem;
                font-weight: 700;
                line-height: 1;
                color: #34395e;
            }

            .chart-legend-item {
                margin-right: 0;
            }

            .chart-legend-color {
                display: inline-block;
                width: 11px;
                height: 11px;
                flex-shrink: 0;
            }

            /* Container Flex Layout: Bebas Scroll Horizontal */
            .chart-visual {
                position: relative;
                display: flex;
                align-items: flex-start;
                width: 100%;
                max-width: 100%;
                min-width: 0;
                overflow: hidden;
            }

            /* Sumbu Y Terisolasi */
            .chart-axis-wrapper {
                flex: 0 0 32px;
                width: 32px;
                min-width: 32px;
                display: flex;
                flex-direction: column;
                align-items: flex-end;
                user-select: none;
            }

            .chart-axis-spacer-top {
                height: 24px;
                flex-shrink: 0;
            }

            .chart-axis {
                width: 100%;
                height: 220px; /* Tepat sama dengan tinggi .chart-bars */
                flex-shrink: 0;
                display: flex;
                flex-direction: column;
                justify-content: space-between;
                align-items: flex-end;
                padding-right: 6px;
                font-size: 0.72rem;
                font-weight: 600;
                color: #8898aa;
                line-height: 1;
            }

            .chart-axis-val {
                transform: translateY(50%);
            }

            .chart-axis-val:first-child {
                transform: translateY(0);
            }

            .chart-axis-val:last-child {
                transform: translateY(50%);
            }

            /* Kontainer Bars: Muat 100% tanpa Scroll Horizontal */
            .chart-scroll {
                display: flex;
                align-items: flex-start;
                flex: 1 1 0%;
                min-width: 0;
                max-width: 100%;
                overflow: hidden;
                gap: 0.4rem;
                padding-bottom: 0.25rem;
            }

            .chart-group {
                display: flex;
                flex-direction: column;
                justify-content: flex-start;
                flex: 1 1 0%;
                min-width: 0; /* Fleksibel membagi rata 100% lebar kartu */
                text-align: center;
            }

            .chart-unit-total {
                height: 24px;
                line-height: 24px;
                margin-bottom: 0;
                font-size: 0.75rem;
            }

            .chart-bars {
                position: relative;
                width: 100%;
                height: 220px; /* Baseline terkunci pada 220px */
                display: flex;
                align-items: flex-end;
                justify-content: center;
                border-bottom: 2px solid #dee2e6;
                background-image: repeating-linear-gradient(
                    to top,
                    transparent,
                    transparent calc(25% - 1px),
                    rgba(108, 117, 125, 0.08) calc(25% - 1px),
                    rgba(108, 117, 125, 0.08) 25%
                );
                background-size: 100% 100%;
            }

            .chart-stack-wrapper {
                width: 100%;
                max-width: 38px;
                height: 100%;
            }

            .chart-bar-value {
                font-size: 0.78rem;
                line-height: 1;
                margin-bottom: .35rem;
                color: #34395e;
            }

            .chart-stack-shell {
                width: 100%;
                max-width: 32px;
                min-height: 0;
                display: flex;
                flex-direction: column-reverse;
                justify-content: flex-start;
                overflow: hidden;
                border-radius: 5px 5px 0 0;
                background-color: #f1f3f5;
                box-shadow: inset 0 0 0 1px rgba(103, 119, 239, 0.08);
                transition: box-shadow .2s ease, transform .2s ease;
                animation: chart-stack-enter .5s ease-out both;
                animation-delay: var(--chart-delay, 0ms);
                transform-origin: bottom center;
            }

            .chart-mode-layer {
                height: 100%;
                width: 100%;
                display: flex;
                flex-direction: column-reverse;
            }

            .chart-stack-segment {
                width: 100%;
                min-height: 4px;
                transition: filter .2s ease, opacity .2s ease, transform .2s ease, box-shadow .2s ease;
                transform-origin: center;
                cursor: pointer;
            }

            .chart-stack-segment.is-muted {
                opacity: .2 !important;
                filter: grayscale(.2) saturate(.7) brightness(.94);
                transform: scaleX(.92);
            }

            .chart-stack-segment.is-highlighted {
                opacity: 1 !important;
                filter: brightness(1.1) saturate(1.2);
                transform: scaleX(1.08);
                box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.35);
            }

            .chart-stack-shell:hover,
            .chart-stack-shell:focus-within {
                box-shadow: inset 0 0 0 1px rgba(103, 119, 239, 0.2), 0 8px 20px rgba(103, 119, 239, 0.12);
                transform: translateY(-2px);
            }

            .chart-stack-shell:hover .chart-stack-segment,
            .chart-stack-shell:focus-within .chart-stack-segment {
                opacity: .65;
                filter: saturate(.9);
            }

            .chart-stack-segment:hover,
            .chart-stack-segment:focus {
                opacity: 1 !important;
                filter: brightness(1.08) saturate(1.15);
                transform: scaleX(1.08);
                box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.28);
                outline: none;
            }

            .chart-stack-empty {
                width: 100%;
                height: 100%;
                min-height: 12px;
            }

            .chart-group-title {
                font-size: .8rem;
                line-height: 1.25;
                color: #34395e;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }

            .chart-badges {
                gap: .2rem;
            }

            .chart-badges .badge {
                font-size: .62rem;
                font-weight: 500;
                white-space: normal;
                line-height: 1.2;
                padding: .18rem .3rem;
                border: 1px solid #e9ecef;
            }

            .chart-badge-zero {
                opacity: .45;
                background-color: #f8f9fa !important;
                color: #8898aa !important;
            }

            @keyframes chart-stack-enter {
                from {
                    opacity: 0;
                    transform: translateY(10px) scaleY(.7);
                }
                to {
                    opacity: 1;
                    transform: translateY(0) scaleY(1);
                }
            }

            @media (prefers-reduced-motion: reduce) {
                .chart-stack-shell,
                .chart-stack-segment {
                    animation: none !important;
                    transition: none !important;
                }
            }

            @media (max-width: 991.98px) {
                .chart-summary-count {
                    font-size: 1.15rem;
                }

                .chart-axis-wrapper {
                    flex-basis: 26px;
                    width: 26px;
                    min-width: 26px;
                }

                .chart-axis-spacer-top {
                    height: 22px;
                }

                .chart-axis {
                    height: 180px;
                }

                .chart-scroll {
                    gap: 0.3rem;
                }

                .chart-unit-total {
                    height: 22px;
                    line-height: 22px;
                    font-size: 0.7rem;
                }

                .chart-bars {
                    height: 180px;
                }

                .chart-stack-wrapper {
                    max-width: 32px;
                }

                .chart-stack-shell {
                    max-width: 26px;
                }
            }

            @media (max-width: 767.98px) {
                .chart-legend {
                    gap: .35rem .75rem;
                    margin-bottom: .75rem !important;
                }

                .chart-summary-card {
                    padding: .65rem .7rem;
                }

                .chart-axis-spacer-top {
                    height: 20px;
                }

                .chart-unit-total {
                    height: 20px;
                    line-height: 20px;
                    font-size: 0.68rem;
                }

                .chart-bars {
                    height: 160px;
                }

                .chart-stack-wrapper {
                    max-width: 26px;
                }

                .chart-stack-shell {
                    max-width: 20px;
                }

                .chart-group-title {
                    font-size: .72rem;
                    margin-bottom: 0;
                }

                .chart-badges .badge {
                    font-size: .55rem;
                    padding: .15rem .22rem;
                }
            }

            /* Radial Gauge Styling for Sertifikasi per Jurusan */
            .sertifikasi-radial-container {
                padding: 0;
            }

            .radial-gauge-card {
                background-color: #fff;
                border-color: #e9ecef !important;
                box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.03);
                transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease;
            }

            .radial-gauge-card:hover {
                transform: translateY(-2px);
                border-color: rgba(71, 195, 99, 0.45) !important;
                box-shadow: 0 6px 16px rgba(71, 195, 99, 0.1);
            }

            .radial-gauge-title {
                font-size: 0.78rem;
                letter-spacing: 0.3px;
            }

            .radial-ring-wrapper {
                width: 64px;
                height: 64px;
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .radial-ring-svg {
                display: block;
            }

            .radial-ring-progress {
                transition: stroke-dasharray 0.6s cubic-bezier(0.4, 0, 0.2, 1);
            }

            .radial-ring-text {
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                font-size: 0.85rem;
                color: #34395e;
                line-height: 1;
                pointer-events: none;
            }

            .radial-footer-hint {
                font-size: 0.75rem;
                color: #8898aa;
            }

            /* Donut Chart Styling */
            .donut-chart-container {
                padding: 0;
            }

            .donut-wrapper {
                position: relative;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                margin: 0 auto;
                width: 180px;
                height: 180px;
            }

            .donut-svg {
                transform: rotate(-90deg);
                overflow: visible;
                display: block;
            }

            .donut-segment {
                transition: stroke-width 0.25s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.25s ease, filter 0.25s ease;
                cursor: pointer;
            }

            .donut-segment:hover,
            .donut-segment.is-active {
                stroke-width: 30;
                filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.15));
                outline: none;
            }

            .donut-segment.is-muted {
                opacity: 0.2;
            }

            .donut-center {
                position: absolute;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                text-align: center;
                pointer-events: none;
                line-height: 1.15;
            }

            .donut-center-val {
                font-size: 1.85rem;
                font-weight: 700;
                color: #34395e;
                transition: all 0.2s ease;
            }

            .donut-center-lbl {
                font-size: 0.72rem;
                font-weight: 600;
                color: #8898aa;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                max-width: 90px;
                margin-top: 2px;
                transition: all 0.2s ease;
            }

            .donut-legend-list {
                display: flex;
                flex-direction: column;
                gap: 0.35rem;
            }

            .donut-legend-card {
                display: flex;
                align-items: center;
                justify-content: space-between;
                padding: 0.45rem 0.65rem;
                border: 1px solid #e9ecef;
                border-radius: 0.55rem;
                background-color: #fff;
                cursor: pointer;
                transition: border-color .2s ease, box-shadow .2s ease, transform .2s ease, opacity .2s ease, background-color .2s ease;
            }

            .donut-legend-card:hover,
            .donut-legend-card:focus {
                border-color: rgba(103, 119, 239, 0.35);
                box-shadow: 0 4px 12px rgba(103, 119, 239, 0.08);
                transform: translateX(2px);
                outline: none;
            }

            .donut-legend-card.is-active {
                border-color: rgba(103, 119, 239, 0.55);
                background-color: #f8f9ff;
                box-shadow: 0 4px 14px rgba(103, 119, 239, 0.12);
            }

            .donut-legend-card.is-muted {
                opacity: 0.35;
            }

            .donut-bullet {
                width: 9px;
                height: 9px;
                border-radius: 50%;
                flex-shrink: 0;
            }

            .donut-item-label {
                font-size: 0.8rem;
            }

            .donut-item-count {
                font-size: 0.85rem;
            }

            .donut-footer-hint {
                font-size: 0.75rem;
                color: #8898aa;
            }
        </style>
    @endpush

    @push('page_js')
        <script>
            (function () {
                // 1. Interactive chart filter helper
                function setupChartFilter(containerId, buttonSelector, segmentSelector, keyAttr) {
                    var chart = document.getElementById(containerId);
                    if (!chart) return;

                    var buttons = Array.prototype.slice.call(chart.querySelectorAll(buttonSelector));
                    var segments = Array.prototype.slice.call(chart.querySelectorAll(segmentSelector));
                    var activeKey = null;

                    var renderState = function () {
                        buttons.forEach(function (button) {
                            var btnKey = button.getAttribute(keyAttr);
                            var isActive = activeKey !== null && btnKey === activeKey;
                            var isInactive = activeKey !== null && btnKey !== activeKey;

                            button.classList.toggle('is-active', isActive);
                            button.classList.toggle('is-inactive', isInactive);
                            button.setAttribute('aria-pressed', isActive ? 'true' : 'false');
                        });

                        segments.forEach(function (segment) {
                            var segKey = segment.getAttribute(keyAttr);
                            var matches = activeKey !== null && segKey === activeKey;
                            var shouldMute = activeKey !== null && segKey !== activeKey;

                            segment.classList.toggle('is-highlighted', matches);
                            segment.classList.toggle('is-muted', shouldMute);
                        });
                    };

                    buttons.forEach(function (button) {
                        button.addEventListener('click', function () {
                            var btnKey = button.getAttribute(keyAttr);
                            activeKey = activeKey === btnKey ? null : btnKey;
                            renderState();
                        });
                    });
                }

                // Setup interactive charts
                setupChartFilter('jurusan-jabatan-bar-chart', '.chart-summary-button', '.chart-stack-segment', 'data-jabatan-key');
                setupChartFilter('pegawai-pendidikan-bar-chart', '.chart-summary-button', '.chart-stack-segment', 'data-pendidikan-key');

                // Donut Chart Interactive Handler (Studi Lanjut per Bidang Studi)
                (function () {
                    var container = document.getElementById('studi-lanjut-donut-chart');
                    if (!container) return;

                    var segments = Array.prototype.slice.call(container.querySelectorAll('.donut-segment'));
                    var legendCards = Array.prototype.slice.call(container.querySelectorAll('.donut-legend-card'));
                    var centerVal = container.querySelector('.donut-center-val');
                    var centerLbl = container.querySelector('.donut-center-lbl');

                    var defaultTotal = centerVal ? centerVal.dataset.defaultCount || '0' : '0';
                    var defaultLabel = centerLbl ? centerLbl.dataset.defaultLabel || 'Total Dosen' : 'Total Dosen';

                    var activeKey = null;

                    function setActive(key) {
                        if (key === activeKey) {
                            key = null;
                        }
                        activeKey = key;

                        if (key === null) {
                            if (centerVal) centerVal.textContent = defaultTotal;
                            if (centerLbl) centerLbl.textContent = defaultLabel;

                            segments.forEach(function (seg) {
                                seg.classList.remove('is-active', 'is-muted');
                            });
                            legendCards.forEach(function (card) {
                                card.classList.remove('is-active', 'is-muted');
                            });
                        } else {
                            var selectedCard = null;
                            legendCards.forEach(function (card) {
                                var cardKey = card.getAttribute('data-bidang-key');
                                var isSelected = cardKey === key;
                                card.classList.toggle('is-active', isSelected);
                                card.classList.toggle('is-muted', !isSelected);
                                if (isSelected) selectedCard = card;
                            });

                            segments.forEach(function (seg) {
                                var segKey = seg.getAttribute('data-bidang-key');
                                var isSelected = segKey === key;
                                seg.classList.toggle('is-active', isSelected);
                                seg.classList.toggle('is-muted', !isSelected);
                            });

                            if (selectedCard && centerVal && centerLbl) {
                                centerVal.textContent = selectedCard.dataset.count || '0';
                                centerLbl.textContent = selectedCard.dataset.label || key;
                            }
                        }
                    }

                    legendCards.forEach(function (card) {
                        card.addEventListener('click', function () {
                            var key = card.getAttribute('data-bidang-key');
                            setActive(key);
                        });

                        card.addEventListener('mouseenter', function () {
                            if (activeKey === null) {
                                var key = card.getAttribute('data-bidang-key');
                                segments.forEach(function (seg) {
                                    var segKey = seg.getAttribute('data-bidang-key');
                                    seg.classList.toggle('is-active', segKey === key);
                                    seg.classList.toggle('is-muted', segKey !== key);
                                });
                                if (centerVal && centerLbl) {
                                    centerVal.textContent = card.dataset.count || '0';
                                    centerLbl.textContent = card.dataset.label || key;
                                }
                            }
                        });

                        card.addEventListener('mouseleave', function () {
                            if (activeKey === null) {
                                segments.forEach(function (seg) {
                                    seg.classList.remove('is-active', 'is-muted');
                                });
                                if (centerVal && centerLbl) {
                                    centerVal.textContent = defaultTotal;
                                    centerLbl.textContent = defaultLabel;
                                }
                            }
                        });
                    });

                    segments.forEach(function (seg) {
                        seg.addEventListener('click', function () {
                            var key = seg.getAttribute('data-bidang-key');
                            setActive(key);
                        });

                        seg.addEventListener('mouseenter', function () {
                            if (activeKey === null) {
                                var key = seg.getAttribute('data-bidang-key');
                                legendCards.forEach(function (card) {
                                    var cardKey = card.getAttribute('data-bidang-key');
                                    card.classList.toggle('is-active', cardKey === key);
                                    card.classList.toggle('is-muted', cardKey !== key);
                                });
                                if (centerVal && centerLbl) {
                                    centerVal.textContent = seg.dataset.count || '0';
                                    centerLbl.textContent = seg.dataset.label || key;
                                }
                            }
                        });

                        seg.addEventListener('mouseleave', function () {
                            if (activeKey === null) {
                                legendCards.forEach(function (card) {
                                    card.classList.remove('is-active', 'is-muted');
                                });
                                if (centerVal && centerLbl) {
                                    centerVal.textContent = defaultTotal;
                                    centerLbl.textContent = defaultLabel;
                                }
                            }
                        });
                    });
                })();

                // 2. Mode switcher for Pegawai Pendidikan (Semua Pegawai | Dosen Saja | Tendik Saja)
                var pendidikanChart = document.getElementById('pegawai-pendidikan-bar-chart');
                var switchGroup = document.getElementById('pendidikan-mode-switch');
                if (pendidikanChart && switchGroup) {
                    var modeButtons = Array.prototype.slice.call(switchGroup.querySelectorAll('button[data-mode]'));
                    var summaryCounts = Array.prototype.slice.call(pendidikanChart.querySelectorAll('.chart-summary-count'));
                    var groups = Array.prototype.slice.call(pendidikanChart.querySelectorAll('.chart-group'));

                    modeButtons.forEach(function (btn) {
                        btn.addEventListener('click', function () {
                            var mode = btn.dataset.mode;
                            modeButtons.forEach(function (b) {
                                b.classList.toggle('active', b === btn);
                                b.classList.toggle('btn-primary', b === btn);
                                b.classList.toggle('btn-outline-primary', b !== btn);
                            });

                            // Update summary counts
                            summaryCounts.forEach(function (sc) {
                                if (mode === 'dosen') {
                                    sc.textContent = sc.dataset.countDosen || '0';
                                } else if (mode === 'tendik') {
                                    sc.textContent = sc.dataset.countTendik || '0';
                                } else {
                                    sc.textContent = sc.dataset.countAll || '0';
                                }
                            });

                            // Calculate max total for new mode to rescale bars
                            var maxTotal = 1;
                            groups.forEach(function (grp) {
                                var count = 0;
                                if (mode === 'dosen') count = parseInt(grp.dataset.totalDosen || '0', 10);
                                else if (mode === 'tendik') count = parseInt(grp.dataset.totalTendik || '0', 10);
                                else count = parseInt(grp.dataset.totalAll || '0', 10);
                                if (count > maxTotal) maxTotal = count;
                            });

                            // Update axis scale
                            var axisVals = Array.prototype.slice.call(pendidikanChart.querySelectorAll('.chart-axis-val'));
                            var ratios = [1, 0.75, 0.5, 0.25, 0];
                            axisVals.forEach(function (av, idx) {
                                if (ratios[idx] !== undefined) {
                                    av.textContent = Math.round(maxTotal * ratios[idx]);
                                }
                            });

                            // Update groups
                            groups.forEach(function (grp) {
                                var count = 0;
                                if (mode === 'dosen') count = parseInt(grp.dataset.totalDosen || '0', 10);
                                else if (mode === 'tendik') count = parseInt(grp.dataset.totalTendik || '0', 10);
                                else count = parseInt(grp.dataset.totalAll || '0', 10);

                                var topTotal = grp.querySelector('.chart-unit-total');
                                if (topTotal) topTotal.textContent = 'Total ' + count;

                                var barVal = grp.querySelector('.chart-bar-value');
                                if (barVal) barVal.textContent = count;

                                var shell = grp.querySelector('.chart-stack-shell');
                                if (shell) {
                                    var heightPct = maxTotal > 0 ? (count / maxTotal) * 100 : 0;
                                    shell.style.height = heightPct + '%';
                                    shell.style.minHeight = count > 0 ? '16px' : '0';
                                }

                                // Switch bar layers safely via style.display
                                var layerAll = grp.querySelector('.chart-mode-all');
                                var layerDosen = grp.querySelector('.chart-mode-dosen');
                                var layerTendik = grp.querySelector('.chart-mode-tendik');

                                if (layerAll) layerAll.style.display = (mode === 'all' ? 'flex' : 'none');
                                if (layerDosen) layerDosen.style.display = (mode === 'dosen' ? 'flex' : 'none');
                                if (layerTendik) layerTendik.style.display = (mode === 'tendik' ? 'flex' : 'none');

                                // Update single-layer badges dynamically
                                var badges = Array.prototype.slice.call(grp.querySelectorAll('.chart-badges .badge'));
                                badges.forEach(function (badge) {
                                    var bCount = 0;
                                    var bLabel = badge.dataset.label || '';
                                    if (mode === 'dosen') {
                                        bCount = parseInt(badge.dataset.countDosen || '0', 10);
                                        badge.title = 'Dosen ' + bLabel + ': ' + bCount;
                                    } else if (mode === 'tendik') {
                                        bCount = parseInt(badge.dataset.countTendik || '0', 10);
                                        badge.title = 'Tendik ' + bLabel + ': ' + bCount;
                                    } else {
                                        bCount = parseInt(badge.dataset.countAll || '0', 10);
                                        badge.title = bLabel + ': ' + bCount + ' (Dosen: ' + (badge.dataset.countDosen || 0) + ', Tendik: ' + (badge.dataset.countTendik || 0) + ')';
                                    }

                                    var countSpan = badge.querySelector('.chart-badge-count');
                                    if (countSpan) countSpan.textContent = bCount;

                                    badge.classList.toggle('font-weight-bold', bCount > 0);
                                    badge.classList.toggle('text-dark', bCount > 0);
                                    badge.classList.toggle('chart-badge-zero', bCount === 0);
                                    badge.classList.toggle('text-muted', bCount === 0);
                                });
                            });
                        });
                    });
                }
            })();
        </script>
    @endpush

</x-app-layout>
