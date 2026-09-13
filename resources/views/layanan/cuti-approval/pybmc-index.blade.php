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
            Daftar usulan cuti pegawai yang telah diverifikasi oleh Atasan Langsung untuk diputuskan oleh Pejabat Yang Berwenang Memberikan Cuti (PYBMC).
        </p>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Daftar Usulan Cuti Masuk (PYBMC)</h4>
                        <div class="card-header-form d-flex align-items-center">
                            <div class="btn-group mr-3" role="group">
                                <a href="{{ route('cuti.approval.pybmc.index', ['stage' => 'pending', 'q' => $keyword]) }}" class="btn btn-sm {{ ($stageFilter ?? 'pending') === 'pending' ? 'btn-primary' : 'btn-outline-primary' }}">
                                    Menunggu Keputusan
                                </a>
                                <a href="{{ route('cuti.approval.pybmc.index', ['stage' => 'approved', 'q' => $keyword]) }}" class="btn btn-sm {{ ($stageFilter ?? '') === 'approved' ? 'btn-primary' : 'btn-outline-primary' }}">
                                    Riwayat Keputusan
                                </a>
                                <a href="{{ route('cuti.approval.pybmc.index', ['stage' => 'all', 'q' => $keyword]) }}" class="btn btn-sm {{ ($stageFilter ?? '') === 'all' ? 'btn-primary' : 'btn-outline-primary' }}">
                                    Semua
                                </a>
                            </div>
                            <form method="GET" action="{{ route('cuti.approval.pybmc.index') }}">
                                <input type="hidden" name="stage" value="{{ $stageFilter ?? 'pending' }}">
                                <div class="input-group">
                                    <input type="text" name="q" class="form-control" value="{{ $keyword ?? '' }}" placeholder="Cari nama atau NIP...">
                                    <div class="input-group-btn">
                                        <button class="btn btn-primary btn-icon"><i class="fas fa-search"></i></button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead class="table-dark">
                                    <tr>
                                        <th class="text-center">#</th>
                                        <th>Pegawai</th>
                                        <th>Jabatan / Unit Kerja</th>
                                        <th>Tanggal Cuti</th>
                                        <th>Durasi</th>
                                        <th>Pertimbangan Atasan</th>
                                        <th>Keputusan PYBMC</th>
                                        <th>Tanggal Masuk</th>
                                        <th class="table-actions-col">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($usulans as $index => $usulan)
                                        @php
                                            $cuti = $usulan->cutiDetail;
                                            $pegawai = $usulan->pegawai;
                                            $atasanStatus = $cuti?->atasan_status;
                                            $pybmcStatus = $cuti?->pybmc_status;
                                        @endphp
                                        <tr>
                                            <td class="text-center">{{ $usulans->firstItem() + $index }}</td>
                                            <td>
                                                <strong>{{ $pegawai->nama ?? '-' }}</strong>
                                                <div class="text-muted small">NIP: {{ $pegawai->nip ?? '-' }}</div>
                                            </td>
                                            <td>
                                                <div>{{ optional($pegawai->jabatan)->jabatan ?: '-' }}</div>
                                                <div class="text-muted small">{{ optional($pegawai->unit_kerja)->unit_kerja ?: '-' }}</div>
                                            </td>
                                            <td>
                                                @if($cuti && $cuti->tanggal_mulai && $cuti->tanggal_selesai)
                                                    {{ $cuti->tanggal_mulai->format('d/m/Y') }} - {{ $cuti->tanggal_selesai->format('d/m/Y') }}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>{{ $cuti && $cuti->hari_diminta !== null ? ((int) $cuti->hari_diminta . ' hari') : '-' }}</td>
                                            <td>
                                                @if($atasanStatus === 'disetujui')
                                                    <span class="badge badge-success">Disetujui</span>
                                                @elseif($atasanStatus === 'perubahan')
                                                    <span class="badge badge-info">Perubahan</span>
                                                @elseif($atasanStatus === 'ditangguhkan')
                                                    <span class="badge badge-warning">Ditangguhkan</span>
                                                @elseif($atasanStatus === 'tidak_disetujui')
                                                    <span class="badge badge-danger">Tidak Disetujui</span>
                                                @else
                                                    <span class="badge badge-secondary">-</span>
                                                @endif
                                                @if($cuti?->atasanPegawai)
                                                    <div class="text-muted small font-italic">{{ $cuti->atasanPegawai->nama }}</div>
                                                @endif
                                            </td>
                                            <td>
                                                @if($pybmcStatus === 'disetujui')
                                                    <span class="badge badge-success">Disetujui (Selesai)</span>
                                                @elseif($pybmcStatus === 'perubahan')
                                                    <span class="badge badge-info">Perubahan</span>
                                                @elseif($pybmcStatus === 'ditangguhkan')
                                                    <span class="badge badge-warning">Ditangguhkan</span>
                                                @elseif($pybmcStatus === 'tidak_disetujui')
                                                    <span class="badge badge-danger">Ditolak</span>
                                                @else
                                                    <span class="badge badge-warning">Menunggu Keputusan</span>
                                                @endif
                                            </td>
                                            <td>{{ optional($usulan->created_at)->format('d/m/Y H:i') }}</td>
                                            <td class="table-actions-cell">
                                                <a href="{{ route('cuti.approval.pybmc.show', $usulan->id) }}" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-gavel mr-1"></i> {{ $pybmcStatus ? 'Detail' : 'Putuskan' }}
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="text-center py-4 text-muted">Belum ada data usulan cuti untuk diputuskan PYBMC.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @if($usulans instanceof \Illuminate\Pagination\LengthAwarePaginator && $usulans->hasPages())
                        <div class="card-footer text-right">
                            {{ $usulans->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
