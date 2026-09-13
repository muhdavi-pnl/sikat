<x-app-layout>
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
            Daftar usulan cuti pegawai yang diproses terpisah pada modul cuti.
        </p>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Daftar {{ $title }}</h4>
                        <div class="card-header-action d-flex align-items-center">
                            <a href="{{ route('kepegawaian.cuti.pybmc-setting') }}" class="btn btn-outline-primary mr-3">
                                <i class="fas fa-user-shield mr-1"></i> Atur PYBMC
                            </a>
                            <form class="card-header-form" method="GET" action="{{ route('kepegawaian.cuti.proses') }}">
                                <div class="input-group">
                                    <input type="text" name="q" class="form-control" value="{{ $keyword ?? '' }}" placeholder="Cari nama pegawai atau nama layanan cuti">
                                    <div class="input-group-btn">
                                        <button class="btn btn-primary btn-icon"><i class="fas fa-search"></i></button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped" id="table-1">
                                <thead class="table-dark">
                                    <tr>
                                        <th class="text-center">#</th>
                                        <th>Pegawai</th>
                                        <th>Layanan</th>
                                        <th>Tanggal Cuti</th>
                                        <th>Hari Kerja</th>
                                        <th>Sisa Jatah Cuti Saat Pengajuan</th>
                                        <th>Status</th>
                                        <th>Tanggal Usul</th>
                                        <th class="table-actions-col">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($usulans as $index => $usulan)
                                        @php
                                            $cuti = $usulan->cutiDetail;
                                            $statusOptions = \App\Models\LayananPegawai::statusOptions();
                                            $statusLabel = $statusOptions[$usulan->status] ?? ucfirst((string) $usulan->status);
                                        @endphp
                                        <tr>
                                            <td class="text-center">{{ $usulans->firstItem() + $index }}</td>
                                            <td>{{ optional($usulan->pegawai)->nama ?: '-' }}</td>
                                            <td>{{ optional($usulan->layanan)->layanan ?: 'Layanan Cuti Pegawai' }}</td>
                                            <td>
                                                @if($cuti && $cuti->tanggal_mulai && $cuti->tanggal_selesai)
                                                    {{ $cuti->tanggal_mulai->format('d-m-Y') }} s.d. {{ $cuti->tanggal_selesai->format('d-m-Y') }}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>{{ $cuti && $cuti->hari_diminta !== null ? ((int) $cuti->hari_diminta . ' hari') : '-' }}</td>
                                            <td>{{ $cuti && $cuti->hari_tersedia_saat_usul !== null ? ((int) $cuti->hari_tersedia_saat_usul . ' hari') : '-' }}</td>
                                            <td>
                                                <span class="badge {{ \App\Models\LayananPegawai::statusBadgeClass($usulan->status) }}">{{ $statusLabel }}</span>
                                            </td>
                                            <td>{{ optional($usulan->created_at)->format('d-m-Y H:i') }}</td>
                                            <td class="table-actions-cell">
                                                <a href="{{ route('kepegawaian.cuti.edit', $usulan->id) }}" class="btn btn-sm btn-primary">Proses</a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="table-empty-row">Belum ada data.</td>
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
</x-app-layout>

