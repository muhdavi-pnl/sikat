<x-app-layout>
    @push('plugins_css')
    @endpush

    @push('page_css')
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
        <p class="section-lead">
            Daftar usulan layanan pegawai untuk diproses oleh kepegawaian (diurutkan otomatis dari risiko SLA dan prioritas tertinggi).
        </p>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Daftar {{ $title }}</h4>
                        <form class="card-header-form" method="GET" action="{{ route('kepegawaian.layanan.proses') }}">
                            <div class="input-group">
                                <input type="text" name="q" class="form-control" value="{{ $keyword ?? '' }}" placeholder="Cari nama pegawai atau nama layanan">
                                <div class="input-group-btn">
                                    <button class="btn btn-primary btn-icon"><i class="fas fa-search"></i></button>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped" id="table-1">
                                <thead class="table-dark">
                                    <tr>
                                        <th class="text-center">#</th>
                                        <th>Pegawai</th>
                                        <th>Layanan</th>
                                        <th>Status</th>
                                        <th>Prioritas</th>
                                        <th>Risiko SLA</th>
                                        <th>Jatuh Tempo SLA</th>
                                        <th>Catatan Pengusul</th>
                                        <th>Diusulkan Oleh</th>
                                        <th>Tanggal Usul</th>
                                        <th class="table-actions-col">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($usulans as $index => $usulan)
                                        <tr>
                                            <td class="text-center">{{ $usulans->firstItem() + $index }}</td>
                                            <td>{{ optional($usulan->pegawai)->nama ?: '-' }}</td>
                                            <td>{{ optional($usulan->layanan)->layanan ?: '-' }}</td>
                                            <td>
                                                @php
                                                    $statusOptions = \App\Models\LayananPegawai::statusOptions();
                                                    $statusLabel = $statusOptions[$usulan->status] ?? ucfirst((string) $usulan->status);
                                                    $slaRiskLabel = \App\Models\LayananPegawai::slaRiskLabel($usulan->sla_risk);
                                                @endphp
                                                <span class="badge {{ \App\Models\LayananPegawai::statusBadgeClass($usulan->status) }}">{{ $statusLabel }}</span>
                                            </td>
                                            <td>
                                                <span class="font-weight-bold">{{ (int) $usulan->priority_score }}</span>/100
                                            </td>
                                            <td>
                                                <span class="badge {{ \App\Models\LayananPegawai::slaRiskBadgeClass($usulan->sla_risk) }}">{{ $slaRiskLabel }}</span>
                                            </td>
                                            <td>{{ optional($usulan->sla_due_at)->format('d-m-Y H:i') ?: '-' }}</td>
                                            <td>{{ $usulan->catatan_pengusul ?: '-' }}</td>
                                            <td>{{ optional($usulan->pengusul)->name ?: '-' }}</td>
                                            <td>{{ optional($usulan->created_at)->format('d-m-Y H:i') }}</td>
                                            <td class="table-actions-cell">
                                                <a href="{{ route('kepegawaian.layanan.edit', $usulan->id) }}" class="btn btn-sm btn-primary">Proses</a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="11" class="table-empty-row">Belum ada data.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer text-right">
                        {{ $usulans->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('plugins_js')
    @endpush

    @push('page_js')
    @endpush
</x-app-layout>



