<x-app-layout>
    @push('plugins_css')
    @endpush

    @section('title', $title)
    <x-slot name="header">
        <h1>{{ $title }}</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{ route('dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item active"><a href="{{ route('layanan.kepegawaian') }}">Layanan</a></div>
            <div class="breadcrumb-item">{{ $title }}</div>
        </div>
    </x-slot>

    <div class="section-body">
        <h2 class="section-title">{{ $title }}</h2>
        <p class="section-lead">
            Usulan layanan Anda telah masuk ke sistem dan siap diproses.
        </p>

        <div class="row">
            <div class="col-12 col-md-12 col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Form {{ $title }}</h4>
                        <div class="card-header-action">
                            @if(optional($usulan)->cutiDetail)
                                <a href="{{ route('pegawai.layanan.cuti.print', $usulan) }}" target="_blank" class="btn btn-icon icon-left btn-primary mr-2">
                                    <i class="fas fa-print"></i> Cetak Formulir Cuti
                                </a>
                            @endif
                            <a href="{{ route('pegawai.layanan') }}" class="btn btn-icon icon-left btn-dark">
                                <i class="fas fa-list"></i> Riwayat Usulan
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row mt-2">
                            <div class="col-12 col-lg-8 offset-lg-2">
                                <div class="wizard-steps">
                                    <div class="wizard-step wizard-step-success">
                                        <div class="wizard-step-icon">
                                            <i class="fas fa-clipboard-list"></i>
                                        </div>
                                        <div class="wizard-step-label">
                                            Ceklist Persyaratan
                                        </div>
                                    </div>
                                    <div class="wizard-step wizard-step-success">
                                        <div class="wizard-step-icon">
                                            <i class="fas fa-check"></i>
                                        </div>
                                        <div class="wizard-step-label">
                                            Selesai
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <form class="wizard-content mt-2">
                            <div class="wizard-pane">
                                <div class="col-12 mb-4">
                                    <div class="hero align-items-center bg-success text-white">
                                        <div class="hero-inner text-center">
                                            <h2>Usulan Berhasil Dikirim</h2>
                                            <p class="lead">Layanan yang Anda usulkan berhasil disimpan dan saat ini berstatus Usulan.</p>
                                            @if($usulan)
                                                <div class="mt-3">
                                                    <span class="badge badge-light">No. Usulan #{{ $usulan->id }}</span>
                                                    <span class="badge badge-light">{{ optional($usulan->created_at)->format('d-m-Y H:i') }}</span>
                                                </div>
                                            @endif
                                            <div class="mt-4">
                                                <a href="{{ route('pegawai.layanan') }}" class="btn btn-outline-white btn-lg btn-icon icon-left">
                                                    <i class="fas fa-list"></i> Lihat layanan yang pernah diusulkan
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                @if($usulan)
                                    @php
                                        $cuti = $usulan->cutiDetail;
                                    @endphp
                                    <div class="row">
                                        <div class="col-12 col-lg-6">
                                            <div class="card card-primary">
                                                <div class="card-header">
                                                    <h4>Ringkasan Layanan</h4>
                                                </div>
                                                <div class="card-body">
                                                    <div class="mb-2">
                                                        <small class="text-muted d-block">Layanan</small>
                                                        <strong>{{ optional($usulan->layanan)->layanan ?: '-' }}</strong>
                                                    </div>
                                                    <div class="mb-2">
                                                        <small class="text-muted d-block">Jenis</small>
                                                        <div>{{ ucfirst(optional($usulan->layanan)->jenis ?: '-') }}</div>
                                                    </div>
                                                    <div class="mb-2">
                                                        <small class="text-muted d-block">Status Usulan</small>
                                                        @php
                                                            $statusOptions = \App\Models\LayananPegawai::statusOptions();
                                                            $statusLabel = $statusOptions[$usulan->status] ?? ucfirst((string) $usulan->status);
                                                        @endphp
                                                        <span class="badge {{ \App\Models\LayananPegawai::statusBadgeClass($usulan->status) }}">{{ $statusLabel }}</span>
                                                    </div>
                                                    <div>
                                                        <small class="text-muted d-block">Catatan Pengusul</small>
                                                        <div>{{ $usulan->catatan_pengusul ?: '-' }}</div>
                                                    </div>
                                                    @if($cuti && $cuti->hari_diminta)
                                                        <hr>
                                                        <div class="mb-2">
                                                            <small class="text-muted d-block">Jenis Cuti</small>
                                                            <div>{{ \App\Services\CutiService::jenisCutiLabel($cuti->jenis_cuti) }}</div>
                                                        </div>
                                                        <div class="mb-2">
                                                            <small class="text-muted d-block">Tanggal Cuti</small>
                                                            <div>
                                                                @if($cuti->tanggal_mulai && $cuti->tanggal_selesai)
                                                                    {{ $cuti->tanggal_mulai->format('d-m-Y') }} s.d. {{ $cuti->tanggal_selesai->format('d-m-Y') }}
                                                                @else
                                                                    -
                                                                @endif
                                                            </div>
                                                        </div>
                                                        <div>
                                                            <small class="text-muted d-block">Jumlah Hari Cuti (Hari Kerja)</small>
                                                            <div>{{ (int) $cuti->hari_diminta }} hari</div>
                                                        </div>
                                                        <div class="mt-2">
                                                            <small class="text-muted d-block">Alasan Cuti</small>
                                                            <div>{{ $cuti->alasan_cuti ?: '-' }}</div>
                                                        </div>
                                                        <div class="mt-2">
                                                            <small class="text-muted d-block">Alamat Selama Cuti</small>
                                                            <div>{{ $cuti->alamat_menjalankan_cuti ?: '-' }}</div>
                                                        </div>
                                                        <div class="mt-2">
                                                            <small class="text-muted d-block">No. Telepon Selama Cuti</small>
                                                            <div>{{ $cuti->nomor_telepon_cuti ?: '-' }}</div>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12 col-lg-6">
                                            <div class="card card-info">
                                                <div class="card-header">
                                                    <h4>Data Pegawai</h4>
                                                </div>
                                                <div class="card-body">
                                                    <div class="mb-2">
                                                        <small class="text-muted d-block">Nama</small>
                                                        <strong>{{ strtoupper(optional($usulan->pegawai)->nama ?: '-') }}</strong>
                                                    </div>
                                                    <div class="mb-2">
                                                        <small class="text-muted d-block">NIP</small>
                                                        <div>{{ optional($usulan->pegawai)->nip ?: '-' }}</div>
                                                    </div>
                                                    <div class="mb-2">
                                                        <small class="text-muted d-block">Unit Kerja</small>
                                                        <div>{{ optional(optional($usulan->pegawai)->unit_kerja)->unit_kerja ?: '-' }}</div>
                                                    </div>
                                                    <div>
                                                        <small class="text-muted d-block">Program Studi</small>
                                                        <div>{{ optional(optional($usulan->pegawai)->program_studi)->nama_prodi ?: '-' }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="card mt-2">
                                        <div class="card-header">
                                            <h4>Persyaratan Layanan</h4>
                                        </div>
                                        <div class="card-body">
                                            <ol class="pl-3 mb-0">
                                                @forelse(optional($usulan->layanan)->syarat ?? collect() as $syarat)
                                                    <li class="mb-2">{{ $syarat->syarat }}</li>
                                                @empty
                                                    <li>Tidak ada persyaratan khusus.</li>
                                                @endforelse
                                            </ol>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    @push('plugins_js')
    @endpush

    @push('page_js')
    @endpush
</x-app-layout>
