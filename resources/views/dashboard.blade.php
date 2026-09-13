<x-app-layout>
    @section('title', $title)
        <x-slot name="header">
            <h1>{{ $title }}</h1>
        </x-slot>
        <div class="row">
            <div class="col-lg-4 col-md-4 col-sm-4 col-12">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-primary">
                        <i class="far fa-user"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>Jumlah Dosen</h4>
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
                        <i class="far fa-file"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>Jumlah Dokumen</h4>
                        </div>
                        <div class="card-body">
                            {{ $dokumens }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-4 col-sm-4 col-12">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-danger">
                        <i class="far fa-newspaper"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>Jumlah Layanan</h4>
                        </div>
                        <div class="card-body">
                            {{ $layanans }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12 col-md-12 col-12">
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

                            $jurusanShortLabels = [
                                1 => 'Sipil',
                                2 => 'Mesin',
                                3 => 'Kimia',
                                4 => 'Elektro',
                                5 => 'Bisnis',
                                6 => 'TIK',
                            ];

                            $maxJurusanTotal = max(1, (int) collect($jurusanJabatanChart)->max('total'));
                            $chartScaleLabels = collect([1, 0.75, 0.5, 0.25, 0])
                                ->map(function ($ratio) use ($maxJurusanTotal) {
                                    return (int) round($maxJurusanTotal * $ratio);
                                })
                                ->unique()
                                ->values()
                                ->all();
                        @endphp

                        <div id="jurusan-jabatan-bar-chart">
                            <div class="chart-summary row mb-2">
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
                                    <div class="chart-legend-item d-flex align-items-center">
                                        <span class="chart-legend-color mr-2 rounded" style="background-color: {{ $jabatanColors[$item['key']] ?? '#6c757d' }};"></span>
                                        <span class="text-small">{{ $item['label'] }}</span>
                                    </div>
                                @endforeach
                            </div>

                            <div class="chart-visual d-flex align-items-stretch">
                                <div class="chart-axis d-none d-md-flex flex-column justify-content-between text-muted small">
                                    @foreach($chartScaleLabels as $value)
                                        <span>{{ $value }}</span>
                                    @endforeach
                                </div>

                                <div class="chart-scroll d-flex align-items-end overflow-auto pb-2">
                                    @foreach($jurusanJabatanChart as $row)
                                        <div class="chart-group d-flex flex-column justify-content-end text-center" style="--chart-delay: {{ $loop->index * 90 }}ms;">
                                            <div class="text-muted small mb-2">Total {{ $row['total'] }}</div>
                                            <div class="chart-bars d-flex align-items-end justify-content-center">
                                                @php
                                                    $stackHeight = $maxJurusanTotal > 0
                                                        ? ($row['total'] / $maxJurusanTotal) * 100
                                                        : 0;

                                                    $segments = collect($row['distribution'])
                                                        ->filter(function ($item) {
                                                            return ($item['count'] ?? 0) > 0;
                                                        })
                                                        ->values()
                                                        ->all();
                                                @endphp
                                                <div class="chart-stack-wrapper d-flex flex-column align-items-center justify-content-end">
                                                    <div class="chart-bar-value small font-weight-bold">{{ $row['total'] }}</div>
                                                    <div class="chart-stack-shell" title="{{ $row['jurusan_label'] }}: total {{ $row['total'] }}" style="height: {{ $stackHeight }}%; min-height: {{ $row['total'] > 0 ? '18px' : '0' }};">
                                                        <?php if ($segments === []): ?>
                                                            <div class="chart-stack-empty"></div>
                                                        <?php else: ?>
                                                            <?php foreach ($segments as $item): ?>
                                                                @php
                                                                    $segmentHeight = $row['total'] > 0
                                                                        ? ($item['count'] / $row['total']) * 100
                                                                        : 0;
                                                                @endphp
                                                                <div class="chart-stack-segment" data-jabatan-key="{{ $item['key'] }}" tabindex="0" aria-label="{{ $row['jurusan_label'] }} - {{ $item['label'] }}: {{ $item['count'] }}" title="{{ $item['label'] }}: {{ $item['count'] }}" style="height: {{ $segmentHeight }}%; background-color: {{ $jabatanColors[$item['key']] ?? '#6c757d' }};"></div>
                                                            <?php endforeach; ?>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="chart-group-title font-weight-bold mt-3 mb-2" title="{{ $row['jurusan_label'] }}">
                                                <span class="d-none d-md-inline">{{ $row['jurusan_label'] }}</span>
                                                <span class="d-inline d-md-none">{{ $jurusanShortLabels[$row['jurusan_id']] ?? $row['jurusan_label'] }}</span>
                                                <span class="sr-only">{{ $row['jurusan_label'] }}</span>
                                            </div>
                                            <div class="chart-badges d-none d-md-flex justify-content-center flex-wrap text-muted">
                                                @foreach($row['distribution'] as $item)
                                                    <span class="badge badge-light">{{ $item['label'] }}: {{ $item['count'] }}</span>
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
            {{--<div class="col-lg-4 col-md-12 col-12 col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Aktivitas Terakhir</h4>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled list-unstyled-border">
                            <li class="media">
                                <img class="mr-3 rounded-circle" width="50" src="assets/img/avatar/avatar-1.png" alt="avatar">
                                <div class="media-body">
                                    <div class="float-right text-primary">Now</div>
                                    <div class="media-title">Farhan A Mujib</div>
                                    <span class="text-small text-muted">Cras sit amet nibh libero, in gravida nulla.</span>
                                </div>
                            </li>
                            <li class="media">
                                <img class="mr-3 rounded-circle" width="50" src="assets/img/avatar/avatar-2.png" alt="avatar">
                                <div class="media-body">
                                    <div class="float-right">12m</div>
                                    <div class="media-title">Ujang Maman</div>
                                    <span class="text-small text-muted">Cras sit amet nibh libero, in gravida nulla.</span>
                                </div>
                            </li>
                            <li class="media">
                                <img class="mr-3 rounded-circle" width="50" src="assets/img/avatar/avatar-3.png" alt="avatar">
                                <div class="media-body">
                                    <div class="float-right">17m</div>
                                    <div class="media-title">Rizal Fakhri</div>
                                    <span class="text-small text-muted">Cras sit amet nibh libero, in gravida nulla.</span>
                                </div>
                            </li>
                            <li class="media">
                                <img class="mr-3 rounded-circle" width="50" src="assets/img/avatar/avatar-4.png" alt="avatar">
                                <div class="media-body">
                                    <div class="float-right">21m</div>
                                    <div class="media-title">Alfa Zulkarnain</div>
                                    <span class="text-small text-muted">Cras sit amet nibh libero, in gravida nulla.</span>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>--}}
        </div>

        @push('page_css')
            <style>
                #jurusan-jabatan-bar-chart .chart-legend {
                    gap: .5rem 1rem;
                }

                #jurusan-jabatan-bar-chart .chart-summary-card {
                    padding: .75rem .85rem;
                    border: 1px solid #e9ecef;
                    border-radius: .75rem;
                    background-color: #fff;
                    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.04);
                    transition: border-color .2s ease, box-shadow .2s ease, transform .2s ease, opacity .2s ease;
                }

                #jurusan-jabatan-bar-chart .chart-summary-button {
                    appearance: none;
                    cursor: pointer;
                }

                #jurusan-jabatan-bar-chart .chart-summary-button:hover,
                #jurusan-jabatan-bar-chart .chart-summary-button:focus {
                    border-color: rgba(103, 119, 239, 0.35);
                    box-shadow: 0 8px 18px rgba(103, 119, 239, 0.1);
                    transform: translateY(-1px);
                    outline: none;
                }

                #jurusan-jabatan-bar-chart .chart-summary-button.is-active {
                    border-color: rgba(103, 119, 239, 0.45);
                    box-shadow: 0 10px 20px rgba(103, 119, 239, 0.14);
                    background-color: #f8f9ff;
                }

                #jurusan-jabatan-bar-chart .chart-summary-button.is-inactive {
                    opacity: .6;
                }

                #jurusan-jabatan-bar-chart .chart-summary-count {
                    font-size: 1.35rem;
                    font-weight: 700;
                    line-height: 1;
                    color: #34395e;
                }

                #jurusan-jabatan-bar-chart .chart-legend-item {
                    margin-right: 0;
                }

                #jurusan-jabatan-bar-chart .chart-legend-color {
                    display: inline-block;
                    width: 11px;
                    height: 11px;
                    flex-shrink: 0;
                }

                #jurusan-jabatan-bar-chart .chart-scroll {
                    flex: 1 1 auto;
                    width: 100%;
                    gap: 1rem;
                    min-height: 340px;
                }

                #jurusan-jabatan-bar-chart .chart-visual {
                    width: 100%;
                    gap: .6rem;
                }

                #jurusan-jabatan-bar-chart .chart-axis {
                    flex: 0 0 28px;
                    align-items: flex-end;
                    padding: 1.15rem 0 4.35rem;
                    line-height: 1;
                    position: sticky;
                    left: 0;
                    z-index: 2;
                    background: linear-gradient(to right, #fff 75%, rgba(255, 255, 255, 0));
                }

                #jurusan-jabatan-bar-chart .chart-group {
                    min-width: 140px;
                    flex: 1 1 0;
                }

                #jurusan-jabatan-bar-chart .chart-bars {
                    width: 100%;
                    height: 220px;
                    border-bottom: 1px solid #e9ecef;
                    background-image: repeating-linear-gradient(
                        to top,
                        transparent,
                        transparent calc(25% - 1px),
                        rgba(108, 117, 125, 0.12) calc(25% - 1px),
                        rgba(108, 117, 125, 0.12) 25%
                    );
                    background-size: 100% 100%;
                }

                #jurusan-jabatan-bar-chart .chart-stack-wrapper {
                    width: 100%;
                    max-width: 56px;
                    height: 100%;
                }

                #jurusan-jabatan-bar-chart .chart-bar-value {
                    line-height: 1;
                    margin-bottom: .4rem;
                }

                #jurusan-jabatan-bar-chart .chart-stack-shell {
                    width: 100%;
                    max-width: 44px;
                    min-height: 0;
                    display: flex;
                    flex-direction: column-reverse;
                    justify-content: flex-start;
                    overflow: hidden;
                    border-radius: 6px 6px 0 0;
                    background-color: #f1f3f5;
                    box-shadow: inset 0 0 0 1px rgba(103, 119, 239, 0.08);
                    transition: box-shadow .2s ease, transform .2s ease;
                    animation: chart-stack-enter .55s ease-out both;
                    animation-delay: var(--chart-delay, 0ms);
                    transform-origin: bottom center;
                }

                #jurusan-jabatan-bar-chart .chart-stack-segment {
                    width: 100%;
                    min-height: 6px;
                    transition: filter .2s ease, opacity .2s ease, transform .2s ease, box-shadow .2s ease;
                    transform-origin: center;
                    cursor: pointer;
                }

                #jurusan-jabatan-bar-chart .chart-stack-segment.is-muted {
                    opacity: .22 !important;
                    filter: grayscale(.18) saturate(.7) brightness(.94);
                    transform: scaleX(.92);
                }

                #jurusan-jabatan-bar-chart .chart-stack-segment.is-highlighted {
                    opacity: 1 !important;
                    filter: brightness(1.1) saturate(1.2);
                    transform: scaleX(1.08);
                    box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.32);
                }

                #jurusan-jabatan-bar-chart .chart-stack-shell:hover,
                #jurusan-jabatan-bar-chart .chart-stack-shell:focus-within {
                    box-shadow: inset 0 0 0 1px rgba(103, 119, 239, 0.2), 0 8px 20px rgba(103, 119, 239, 0.12);
                    transform: translateY(-2px);
                }

                #jurusan-jabatan-bar-chart .chart-stack-shell:hover .chart-stack-segment,
                #jurusan-jabatan-bar-chart .chart-stack-shell:focus-within .chart-stack-segment {
                    opacity: .65;
                    filter: saturate(.9);
                }

                #jurusan-jabatan-bar-chart .chart-stack-segment:hover,
                #jurusan-jabatan-bar-chart .chart-stack-segment:focus {
                    opacity: 1 !important;
                    filter: brightness(1.08) saturate(1.15);
                    transform: scaleX(1.08);
                    box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.28);
                    outline: none;
                }

                #jurusan-jabatan-bar-chart .chart-stack-empty {
                    width: 100%;
                    height: 100%;
                    min-height: 12px;
                }

                #jurusan-jabatan-bar-chart .chart-group-title {
                    font-size: .9rem;
                    line-height: 1.3;
                }

                #jurusan-jabatan-bar-chart .chart-badges {
                    gap: .25rem;
                }

                #jurusan-jabatan-bar-chart .chart-badges .badge {
                    font-size: .65rem;
                    font-weight: 500;
                    white-space: normal;
                    line-height: 1.25;
                    padding: .3rem .45rem;
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
                    #jurusan-jabatan-bar-chart .chart-stack-shell,
                    #jurusan-jabatan-bar-chart .chart-stack-segment {
                        animation: none !important;
                        transition: none !important;
                    }
                }

                @media (max-width: 991.98px) {
                    #jurusan-jabatan-bar-chart .chart-scroll {
                        min-height: 300px;
                        gap: .85rem;
                    }

                    #jurusan-jabatan-bar-chart .chart-summary-count {
                        font-size: 1.15rem;
                    }

                    #jurusan-jabatan-bar-chart .chart-axis {
                        flex-basis: 24px;
                        padding-bottom: 4rem;
                    }

                    #jurusan-jabatan-bar-chart .chart-group {
                        min-width: 118px;
                    }

                    #jurusan-jabatan-bar-chart .chart-bars {
                        height: 190px;
                    }

                    #jurusan-jabatan-bar-chart .chart-stack-wrapper {
                        max-width: 46px;
                    }

                    #jurusan-jabatan-bar-chart .chart-stack-shell {
                        max-width: 36px;
                    }
                }

                @media (max-width: 767.98px) {
                    #jurusan-jabatan-bar-chart .chart-legend {
                        gap: .35rem .75rem;
                        margin-bottom: .75rem !important;
                    }

                    #jurusan-jabatan-bar-chart .chart-summary-card {
                        padding: .65rem .7rem;
                    }

                    #jurusan-jabatan-bar-chart .chart-group {
                        min-width: 98px;
                        flex: 0 0 98px;
                    }

                    #jurusan-jabatan-bar-chart .chart-scroll {
                        min-height: 260px;
                        gap: .65rem;
                    }

                    #jurusan-jabatan-bar-chart .chart-bars {
                        height: 160px;
                    }

                    #jurusan-jabatan-bar-chart .chart-stack-wrapper {
                        max-width: 32px;
                    }

                    #jurusan-jabatan-bar-chart .chart-stack-shell {
                        max-width: 24px;
                    }

                    #jurusan-jabatan-bar-chart .chart-group-title {
                        font-size: .8rem;
                        margin-bottom: 0;
                    }

                    #jurusan-jabatan-bar-chart .chart-badges .badge {
                        font-size: .6rem;
                        padding: .25rem .35rem;
                    }
                }
            </style>
        @endpush

        @push('page_js')
            <script>
                (function () {
                    var chart = document.getElementById('jurusan-jabatan-bar-chart');

                    if (!chart) {
                        return;
                    }

                    var buttons = Array.prototype.slice.call(chart.querySelectorAll('.chart-summary-button'));
                    var segments = Array.prototype.slice.call(chart.querySelectorAll('.chart-stack-segment'));
                    var activeKey = null;

                    var renderState = function () {
                        buttons.forEach(function (button) {
                            var isActive = activeKey !== null && button.dataset.jabatanKey === activeKey;
                            var isInactive = activeKey !== null && button.dataset.jabatanKey !== activeKey;

                            button.classList.toggle('is-active', isActive);
                            button.classList.toggle('is-inactive', isInactive);
                            button.setAttribute('aria-pressed', isActive ? 'true' : 'false');
                        });

                        segments.forEach(function (segment) {
                            var matches = activeKey !== null && segment.dataset.jabatanKey === activeKey;
                            var shouldMute = activeKey !== null && segment.dataset.jabatanKey !== activeKey;

                            segment.classList.toggle('is-highlighted', matches);
                            segment.classList.toggle('is-muted', shouldMute);
                        });
                    };

                    buttons.forEach(function (button) {
                        button.addEventListener('click', function () {
                            activeKey = activeKey === button.dataset.jabatanKey ? null : button.dataset.jabatanKey;
                            renderState();
                        });
                    });
                })();
            </script>
        @endpush

</x-app-layout>


