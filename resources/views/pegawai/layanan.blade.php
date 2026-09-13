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
        @if(!$pegawai)
            <div class="card">
                <div class="card-body">
                    <div class="alert alert-warning mb-0">
                        Data pegawai Anda belum tersedia. Silakan hubungi admin kepegawaian untuk menghubungkan akun Anda ke data pegawai.
                    </div>
                </div>
            </div>
        @else
            <h2 class="section-title">{{ $title }}</h2>
            <p class="section-lead">Riwayat semua usulan layanan untuk <strong>{{ strtoupper($pegawai->nama) }}</strong>.</p>

            <div class="mb-4 d-flex flex-wrap" style="gap: 0.5rem;">
                <a href="{{ route('layanan.fungsional') }}" class="btn btn-primary">Usul Layanan Fungsional</a>
                <a href="{{ route('layanan.kepegawaian') }}" class="btn btn-info">Usul Layanan Kepegawaian</a>
            </div>

            <div class="card">
                <div class="card-header">
                    <h4>Riwayat Usulan</h4>
                </div>
                <div class="card-body p-0">
                    @if($riwayatLayanans->isEmpty())
                        <div class="empty-state" data-height="230">
                            <div class="empty-state-icon bg-primary">
                                <i class="fas fa-concierge-bell"></i>
                            </div>
                            <h2>Belum Ada Usulan</h2>
                            <p class="lead">Silakan pilih layanan di atas untuk mulai membuat usulan.</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-striped mb-0">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Layanan</th>
                                        <th>Status</th>
                                        <th>Catatan Pengusul</th>
                                        <th>Catatan Proses</th>
                                        <th>Output</th>
                                        <th>Diproses Oleh</th>
                                        <th>Tanggal Usul</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($riwayatLayanans as $index => $riwayat)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ optional($riwayat->layanan)->layanan ?: '-' }}</td>
                                            <td>
                                                @php
                                                    $statusOptions = \App\Models\LayananPegawai::statusOptions();
                                                    $statusLabel = $statusOptions[$riwayat->status] ?? ucfirst((string) $riwayat->status);
                                                @endphp
                                                <span class="badge {{ \App\Models\LayananPegawai::statusBadgeClass($riwayat->status) }}">{{ $statusLabel }}</span>
                                            </td>
                                            <td>{{ $riwayat->catatan_pengusul ?: '-' }}</td>
                                            <td>{{ $riwayat->catatan_proses ?: '-' }}</td>
                                            <td>
                                                @if($riwayat->output_path)
                                                    <a href="{{ asset('file/' . ltrim($riwayat->output_path, '/')) }}" target="_blank" class="btn btn-sm btn-info">
                                                        <i class="fas fa-download"></i> Unduh Output
                                                    </a>
                                                    <div class="text-muted small mt-1">{{ $riwayat->output_original_name ?: basename((string) $riwayat->output_path) }}</div>
                                                @else
                                                    <span class="text-muted">Belum tersedia</span>
                                                @endif
                                            </td>
                                            <td>{{ optional($riwayat->processor)->name ?: '-' }}</td>
                                            <td>{{ optional($riwayat->created_at)->format('d-m-Y H:i') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-header">
                    <h4>Rekap Cuti</h4>
                </div>
                <div class="card-body p-0">
                    @if($riwayatCutis->isEmpty())
                        <div class="p-4 text-muted mb-0">Belum ada data usulan cuti tersimpan pada modul cuti.</div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-striped mb-0">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Layanan</th>
                                        <th>Tanggal Cuti</th>
                                        <th>Hari Kerja</th>
                                        <th>Sisa Jatah Cuti Saat Pengajuan</th>
                                        <th>Status</th>
                                        <th>Output</th>
                                        <th>Formulir</th>
                                        <th>Tanggal Usul</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($riwayatCutis as $index => $cuti)
                                        @php
                                            $layananPegawai = $cuti->layananPegawai;
                                            $statusOptions = \App\Models\LayananPegawai::statusOptions();
                                            $statusLabel = $statusOptions[$layananPegawai->status ?? ''] ?? ucfirst((string) ($layananPegawai->status ?? '-'));
                                        @endphp
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ optional(optional($cuti->layananPegawai)->layanan)->layanan ?: 'Layanan Cuti Pegawai' }}</td>
                                            <td>
                                                @if($cuti->tanggal_mulai && $cuti->tanggal_selesai)
                                                    {{ $cuti->tanggal_mulai->format('d-m-Y') }} s.d. {{ $cuti->tanggal_selesai->format('d-m-Y') }}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>{{ $cuti->hari_diminta !== null ? ((int) $cuti->hari_diminta . ' hari') : '-' }}</td>
                                            <td>{{ $cuti->hari_tersedia_saat_usul !== null ? ((int) $cuti->hari_tersedia_saat_usul . ' hari') : '-' }}</td>
                                            <td>
                                                <span class="badge {{ \App\Models\LayananPegawai::statusBadgeClass(optional($layananPegawai)->status) }}">{{ $statusLabel }}</span>
                                            </td>
                                            <td>
                                                @if(optional($layananPegawai)->output_path)
                                                    <a href="{{ asset('file/' . ltrim((string) $layananPegawai->output_path, '/')) }}" target="_blank" class="btn btn-sm btn-info">
                                                        <i class="fas fa-download"></i> Unduh Output
                                                    </a>
                                                    <div class="text-muted small mt-1">{{ $layananPegawai->output_original_name ?: basename((string) $layananPegawai->output_path) }}</div>
                                                @else
                                                    <span class="text-muted">Belum tersedia</span>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('pegawai.layanan.cuti.print', $layananPegawai) }}" target="_blank" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-print"></i> Cetak
                                                </a>
                                            </td>
                                            <td>{{ optional(optional($cuti->layananPegawai)->created_at)->format('d-m-Y H:i') ?: '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
