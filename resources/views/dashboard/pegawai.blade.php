<x-app-layout>
    @section('title', $title)
    <x-slot name="header">
        <h1>{{ $title }}</h1>
    </x-slot>

    {{-- Welcome Card untuk Pegawai --}}
    @if($pegawai)
        <div class="row mb-4">
            <div class="col-12">
                <div class="card bg-primary text-white shadow-sm border-0 mb-0" style="background: linear-gradient(135deg, #6777ef 0%, #3abaf4 100%);">
                    <div class="card-body p-4">
                        <div class="row align-items-center">
                            <div class="col-lg-8 col-md-8 col-12">
                                <h3 class="font-weight-bold text-white mb-2">Selamat Datang, {{ $pegawai->nama }}!</h3>
                                <p class="mb-2 text-white-50" style="font-size: 0.95rem;">
                                    <span class="mr-3"><i class="fas fa-id-badge mr-1"></i> NIP: {{ $pegawai->nip ?? '-' }}</span>
                                    <span class="mr-3"><i class="fas fa-sitemap mr-1"></i> {{ $pegawai->programStudi->program_studi ?? $pegawai->unitKerja->unit_kerja ?? 'Politeknik Negeri Lhokseumawe' }}</span>
                                    <span><i class="fas fa-user-tag mr-1"></i> {{ ucfirst($pegawai->kelompok_pegawai ?? 'Pegawai') }} ({{ $pegawai->status_pegawai ?? 'PNS' }})</span>
                                </p>
                            </div>
                            <div class="col-lg-4 col-md-4 col-12 text-md-right mt-3 mt-md-0">
                                <a href="{{ route('pegawai.profile') }}" class="btn btn-outline-white btn-icon icon-left mr-1 mb-1">
                                    <i class="fas fa-user-edit"></i> Profil Saya
                                </a>
                                <a href="{{ route('pegawai.dokumen') }}" class="btn btn-light text-primary btn-icon icon-left mb-1">
                                    <i class="fas fa-file-alt"></i> Dokumen Saya
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Row 1: Statistic Cards --}}
    <div class="row">
        <div class="col-lg-4 col-md-4 col-sm-6 col-12">
            <div class="card card-statistic-1">
                <div class="card-icon bg-primary">
                    <i class="fas fa-users"></i>
                </div>
                <div class="card-wrap">
                    <div class="card-header">
                        <h4>Total Pegawai</h4>
                    </div>
                    <div class="card-body">
                        {{ $pegawais }}
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-4 col-sm-6 col-12">
            <div class="card card-statistic-1">
                <div class="card-icon" style="background-color: #3abaf4;">
                    <i class="fas fa-mars"></i>
                </div>
                <div class="card-wrap">
                    <div class="card-header">
                        <h4>Pegawai Laki-laki</h4>
                    </div>
                    <div class="card-body">
                        {{ $totalLakiLaki }}
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-4 col-sm-12 col-12">
            <div class="card card-statistic-1">
                <div class="card-icon" style="background-color: #fc544b;">
                    <i class="fas fa-venus"></i>
                </div>
                <div class="card-wrap">
                    <div class="card-header">
                        <h4>Pegawai Perempuan</h4>
                    </div>
                    <div class="card-body">
                        {{ $totalPerempuan }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Row 2: Grafik Jumlah Pegawai Berdasarkan Jenis Kelamin --}}
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                    <h4>Jumlah Pegawai Berdasarkan Jenis Kelamin</h4>
                    <div class="card-header-action mt-2 mt-md-0">
                        <div class="btn-group btn-group-sm" id="gender-mode-switch" role="group" aria-label="Filter Kelompok Pegawai">
                            <button type="button" class="btn btn-primary active" data-mode="all">
                                <i class="fas fa-users mr-1"></i> Semua Pegawai
                            </button>
                            <button type="button" class="btn btn-outline-primary" data-mode="dosen">
                                <i class="fas fa-user-graduate mr-1"></i> Dosen Saja
                            </button>
                            <button type="button" class="btn btn-outline-primary" data-mode="tendik">
                                <i class="fas fa-user-tie mr-1"></i> Tendik Saja
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @php
                        $genderColors = [
                            'laki_laki' => '#3abaf4',
                            'perempuan' => '#fc544b',
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

                        $maxGenderTotal = max(1, (int) collect($pegawaiGenderChart)->max('total_pegawai'));
                        $scaleGender = collect([1, 0.75, 0.5, 0.25, 0])
                            ->map(fn ($r) => (int) round($maxGenderTotal * $r))
                            ->unique()
                            ->values()
                            ->all();
                    @endphp

                    <div id="pegawai-gender-bar-chart">
                        {{-- Summary Cards Laki-laki & Perempuan --}}
                        <div class="chart-summary row mb-3">
                            @foreach($pegawaiGenderTotals as $item)
                                <div class="col-md-6 col-12 mb-3">
                                    <button type="button" class="chart-summary-card chart-summary-button h-100 w-100 text-left" data-gender-key="{{ $item['key'] }}" aria-pressed="false">
                                        <div class="d-flex align-items-center mb-1">
                                            <span class="chart-legend-color mr-2 rounded" style="background-color: {{ $genderColors[$item['key']] ?? '#6c757d' }};"></span>
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
                            @foreach($pegawaiGenderTotals as $item)
                                <div class="chart-legend-item d-flex align-items-center mr-3 mb-1">
                                    <span class="chart-legend-color mr-2 rounded" style="background-color: {{ $genderColors[$item['key']] ?? '#6c757d' }};"></span>
                                    <span class="text-small">{{ $item['label'] }}</span>
                                </div>
                            @endforeach
                        </div>

                        {{-- Visual Chart --}}
                        <div class="chart-visual">
                            <div class="chart-axis-wrapper d-none d-md-flex">
                                <div class="chart-axis-spacer-top"></div>
                                <div class="chart-axis">
                                    @foreach($scaleGender as $val)
                                        <span class="chart-axis-val">{{ $val }}</span>
                                    @endforeach
                                </div>
                            </div>

                            <div class="chart-scroll">
                                @foreach($pegawaiGenderChart as $row)
                                    @php
                                        $currTotal = $row['total_pegawai'];
                                        $stackHeight = $maxGenderTotal > 0 ? ($currTotal / $maxGenderTotal) * 100 : 0;
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
                                                                     data-gender-key="{{ $item['key'] }}"
                                                                     tabindex="0"
                                                                     title="{{ $item['label'] }}: {{ $item['count'] }} (Dosen: {{ $item['dosen_count'] }}, Tendik: {{ $item['tendik_count'] }})"
                                                                     aria-label="{{ $row['unit_label'] }} - {{ $item['label'] }}: {{ $item['count'] }}"
                                                                     style="height: {{ $segH }}%; background-color: {{ $genderColors[$item['key']] ?? '#6c757d' }};"></div>
                                                            @endif
                                                        @endforeach
                                                    </div>
                                                    {{-- Layer: Dosen Saja --}}
                                                    <div class="chart-mode-layer chart-mode-dosen" style="display: none;">
                                                        @foreach($row['dosen_distribution'] as $item)
                                                            @if(($item['count'] ?? 0) > 0)
                                                                @php $segH = $row['total_dosen'] > 0 ? ($item['count'] / $row['total_dosen']) * 100 : 0; @endphp
                                                                <div class="chart-stack-segment"
                                                                     data-gender-key="{{ $item['key'] }}"
                                                                     tabindex="0"
                                                                     title="Dosen {{ $item['label'] }}: {{ $item['count'] }}"
                                                                     aria-label="{{ $row['unit_label'] }} - Dosen {{ $item['label'] }}: {{ $item['count'] }}"
                                                                     style="height: {{ $segH }}%; background-color: {{ $genderColors[$item['key']] ?? '#6c757d' }};"></div>
                                                            @endif
                                                        @endforeach
                                                    </div>
                                                    {{-- Layer: Tendik Saja --}}
                                                    <div class="chart-mode-layer chart-mode-tendik" style="display: none;">
                                                        @foreach($row['tendik_distribution'] as $item)
                                                            @if(($item['count'] ?? 0) > 0)
                                                                @php $segH = $row['total_tendik'] > 0 ? ($item['count'] / $row['total_tendik']) * 100 : 0; @endphp
                                                                <div class="chart-stack-segment"
                                                                     data-gender-key="{{ $item['key'] }}"
                                                                     tabindex="0"
                                                                     title="Tendik {{ $item['label'] }}: {{ $item['count'] }}"
                                                                     aria-label="{{ $row['unit_label'] }} - Tendik {{ $item['label'] }}: {{ $item['count'] }}"
                                                                     style="height: {{ $segH }}%; background-color: {{ $genderColors[$item['key']] ?? '#6c757d' }};"></div>
                                                            @endif
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="chart-group-title font-weight-bold mt-2 mb-2" title="{{ $row['unit_label'] }}">
                                            <span class="d-none d-md-inline">{{ $row['unit_label'] }}</span>
                                            <span class="d-inline d-md-none">{{ $jurusanShortLabels[$row['unit_id']] ?? $row['unit_label'] }}</span>
                                            <span class="sr-only">{{ $row['unit_label'] }}</span>
                                        </div>
                                        {{-- Badges: Tampilkan jenis kelamin secara dinamis satu baris tanpa duplikasi --}}
                                        <div class="chart-badges d-none d-md-flex justify-content-center flex-wrap text-muted">
                                            @foreach($row['combined_distribution'] as $item)
                                                <span class="badge {{ ($item['count'] ?? 0) > 0 ? 'badge-light font-weight-bold text-dark' : 'badge-light text-muted chart-badge-zero' }}"
                                                      data-gender-key="{{ $item['key'] }}"
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

    {{-- Row 3: Quick Navigation Cards --}}
    <div class="row">
        <div class="col-lg-3 col-md-6 col-sm-6 col-12 mb-3">
            <a href="{{ route('pegawai.profile') }}" class="text-decoration-none">
                <div class="card card-statistic-2">
                    <div class="card-icon bg-primary">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>Profil Saya</h4>
                        </div>
                        <div class="card-body font-weight-normal text-muted" style="font-size: 0.85rem;">
                            Kelola data pribadi & kepegawaian
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6 col-12 mb-3">
            <a href="{{ route('pegawai.dokumen') }}" class="text-decoration-none">
                <div class="card card-statistic-2">
                    <div class="card-icon bg-info">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>Dokumen Saya</h4>
                        </div>
                        <div class="card-body font-weight-normal text-muted" style="font-size: 0.85rem;">
                            Upload & review arsip digital
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6 col-12 mb-3">
            <a href="{{ route('layanan.cuti') }}" class="text-decoration-none">
                <div class="card card-statistic-2">
                    <div class="card-icon bg-warning">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>Layanan Cuti</h4>
                        </div>
                        <div class="card-body font-weight-normal text-muted" style="font-size: 0.85rem;">
                            Pengajuan cuti online
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6 col-12 mb-3">
            <a href="{{ route('pegawai.layanan') }}" class="text-decoration-none">
                <div class="card card-statistic-2">
                    <div class="card-icon bg-success">
                        <i class="fas fa-concierge-bell"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>Layanan Kepegawaian</h4>
                        </div>
                        <div class="card-body font-weight-normal text-muted" style="font-size: 0.85rem;">
                            Usulan fungsional & kenaikan pangkat
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    @push('page_css')
        <style>
            .btn-outline-white {
                color: #fff;
                border-color: rgba(255, 255, 255, 0.7);
            }
            .btn-outline-white:hover {
                background-color: #fff;
                color: #6777ef;
            }

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

            /* Container Flex Layout */
            .chart-visual {
                position: relative;
                display: flex;
                align-items: flex-start;
                width: 100%;
                max-width: 100%;
                min-width: 0;
            }

            /* Sumbu Y Terisolasi */
            .chart-axis-wrapper {
                flex: 0 0 36px;
                width: 36px;
                min-width: 36px;
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
                padding-right: 8px;
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

            .chart-scroll {
                display: flex;
                align-items: flex-start;
                flex: 1 1 0%;
                min-width: 0;
                max-width: 100%;
                overflow-x: auto;
                overflow-y: hidden;
                gap: 0.75rem;
                padding-bottom: 0.5rem;
                -webkit-overflow-scrolling: touch;
            }

            .chart-group {
                display: flex;
                flex-direction: column;
                justify-content: flex-start;
                flex: 1 1 0%;
                min-width: 115px;
                text-align: center;
            }

            .chart-unit-total {
                height: 24px;
                line-height: 24px;
                margin-bottom: 0;
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
                max-width: 52px;
                height: 100%;
            }

            .chart-bar-value {
                line-height: 1;
                margin-bottom: .4rem;
                color: #34395e;
            }

            .chart-stack-shell {
                width: 100%;
                max-width: 42px;
                min-height: 0;
                display: flex;
                flex-direction: column-reverse;
                justify-content: flex-start;
                overflow: hidden;
                border-radius: 6px 6px 0 0;
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

            .chart-group-title {
                font-size: .85rem;
                line-height: 1.3;
                color: #34395e;
            }

            .chart-badges {
                gap: .25rem;
            }

            .chart-badges .badge {
                font-size: .65rem;
                font-weight: 500;
                white-space: normal;
                line-height: 1.25;
                padding: .25rem .4rem;
                border: 1px solid #e9ecef;
            }

            .chart-badge-zero {
                opacity: .5;
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
                    flex-basis: 30px;
                    width: 30px;
                    min-width: 30px;
                }

                .chart-axis-spacer-top {
                    height: 22px;
                }

                .chart-axis {
                    height: 180px;
                }

                .chart-group {
                    min-width: 105px;
                }

                .chart-unit-total {
                    height: 22px;
                    line-height: 22px;
                }

                .chart-bars {
                    height: 180px;
                }

                .chart-stack-wrapper {
                    max-width: 44px;
                }

                .chart-stack-shell {
                    max-width: 34px;
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

                .chart-group {
                    min-width: 90px;
                    flex: 0 0 90px;
                }

                .chart-axis-spacer-top {
                    height: 20px;
                }

                .chart-unit-total {
                    height: 20px;
                    line-height: 20px;
                }

                .chart-bars {
                    height: 160px;
                }

                .chart-stack-wrapper {
                    max-width: 32px;
                }

                .chart-stack-shell {
                    max-width: 24px;
                }

                .chart-group-title {
                    font-size: .78rem;
                    margin-bottom: 0;
                }

                .chart-badges .badge {
                    font-size: .6rem;
                    padding: .2rem .3rem;
                }
            }
        </style>
    @endpush

    @push('page_js')
        <script>
            (function () {
                // Interactive chart filter helper for gender chart
                var chart = document.getElementById('pegawai-gender-bar-chart');
                if (!chart) return;

                var buttons = Array.prototype.slice.call(chart.querySelectorAll('.chart-summary-button'));
                var segments = Array.prototype.slice.call(chart.querySelectorAll('.chart-stack-segment'));
                var activeKey = null;

                var renderState = function () {
                    buttons.forEach(function (button) {
                        var btnKey = button.getAttribute('data-gender-key');
                        var isActive = activeKey !== null && btnKey === activeKey;
                        var isInactive = activeKey !== null && btnKey !== activeKey;

                        button.classList.toggle('is-active', isActive);
                        button.classList.toggle('is-inactive', isInactive);
                        button.setAttribute('aria-pressed', isActive ? 'true' : 'false');
                    });

                    segments.forEach(function (segment) {
                        var segKey = segment.getAttribute('data-gender-key');
                        var matches = activeKey !== null && segKey === activeKey;
                        var shouldMute = activeKey !== null && segKey !== activeKey;

                        segment.classList.toggle('is-highlighted', matches);
                        segment.classList.toggle('is-muted', shouldMute);
                    });
                };

                buttons.forEach(function (button) {
                    button.addEventListener('click', function () {
                        var btnKey = button.getAttribute('data-gender-key');
                        activeKey = activeKey === btnKey ? null : btnKey;
                        renderState();
                    });
                });

                // Mode switcher for Gender Chart (Semua Pegawai | Dosen Saja | Tendik Saja)
                var switchGroup = document.getElementById('gender-mode-switch');
                if (switchGroup) {
                    var modeButtons = Array.prototype.slice.call(switchGroup.querySelectorAll('button[data-mode]'));
                    var summaryCounts = Array.prototype.slice.call(chart.querySelectorAll('.chart-summary-count'));
                    var groups = Array.prototype.slice.call(chart.querySelectorAll('.chart-group'));

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
                            var axisVals = Array.prototype.slice.call(chart.querySelectorAll('.chart-axis-val'));
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
