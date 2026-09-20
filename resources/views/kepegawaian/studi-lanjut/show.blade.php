<x-app-layout>
    @section('title', $title)
    <x-slot name="header">
        <h1>{{ $title }}</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{ route('dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item active"><a href="{{ route('kepegawaian.studi-lanjut.index') }}">Studi Lanjut</a></div>
            <div class="breadcrumb-item">{{ $title }}</div>
        </div>
    </x-slot>

    <div class="section-body">
        <h2 class="section-title">Detail Pemantauan Studi Lanjut</h2>
        <p class="section-lead">Informasi lengkap data pegawai studi lanjut, status progres, pembiayaan, penugasan, dan dokumen legalitas.</p>

        <div class="row">
            {{-- Left Column: Pegawai Profile & Progress Status --}}
            <div class="col-lg-4 col-md-5 col-12">
                {{-- Pegawai Profile Card --}}
                <div class="card card-primary shadow-sm text-center">
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center shadow" style="width: 80px; height: 80px; font-size: 32px;">
                                <i class="fas fa-user-graduate"></i>
                            </div>
                        </div>
                        <h5 class="mb-1 text-dark">{{ $studiLanjut->pegawai?->nama_lengkap ?? 'Pegawai' }}</h5>
                        <p class="text-muted small mb-2">
                            <i class="fas fa-id-card mr-1"></i> NIP: {{ $studiLanjut->pegawai?->nip ?? '-' }}
                        </p>
                        <div class="mb-3">
                            <span class="badge badge-light border text-muted">
                                {{ $studiLanjut->pegawai?->program_studi?->program_studi ?? $studiLanjut->pegawai?->unit_kerja?->unit_kerja ?? '-' }}
                            </span>
                        </div>
                        <hr>
                        <div class="text-left">
                            <div class="mb-2 small">
                                <strong><i class="fas fa-briefcase text-muted mr-1"></i> Jabatan:</strong><br>
                                <span class="text-dark">{{ $studiLanjut->pegawai?->jabatan?->jabatan ?? '-' }}</span>
                            </div>
                            <div class="mb-2 small">
                                <strong><i class="fas fa-medal text-muted mr-1"></i> Pangkat/Golongan:</strong><br>
                                <span class="text-dark">{{ $studiLanjut->pegawai?->pangkat?->pangkat ?? '-' }}</span>
                            </div>
                            <div class="mb-2 small">
                                <strong><i class="fas fa-envelope text-muted mr-1"></i> Email:</strong><br>
                                <span class="text-dark">{{ $studiLanjut->pegawai?->email ?? '-' }}</span>
                            </div>
                            <div class="small">
                                <strong><i class="fas fa-phone text-muted mr-1"></i> No. HP:</strong><br>
                                <span class="text-dark">{{ $studiLanjut->pegawai?->no_hp ?? '-' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Status Progres Card --}}
                <div class="card card-{{ $studiLanjut->progres === 'selesai' ? 'success' : ($studiLanjut->progres === 'defer' ? 'warning' : 'info') }} shadow-sm">
                    <div class="card-header">
                        <h4><i class="fas fa-chart-line mr-2"></i>Status & Progres</h4>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-3">
                            <span class="badge {{ $studiLanjut->progres_badge_class }} py-2 px-3" style="font-size: 1rem;">
                                @if($studiLanjut->progres === 'ongoing')
                                    <i class="fas fa-spinner fa-spin mr-1"></i>
                                @elseif($studiLanjut->progres === 'defer')
                                    <i class="fas fa-pause-circle mr-1"></i>
                                @elseif($studiLanjut->progres === 'selesai')
                                    <i class="fas fa-check-circle mr-1"></i>
                                @endif
                                {{ $studiLanjut->progres_label }}
                            </span>
                        </div>

                        <ul class="list-group list-group-flush small">
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <span><i class="far fa-calendar-alt text-muted mr-1"></i> Tanggal Mulai:</span>
                                <span class="font-weight-bold text-dark">{{ $studiLanjut->tanggal_mulai ? $studiLanjut->tanggal_mulai->format('d M Y') : '-' }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <span><i class="far fa-calendar-check text-muted mr-1"></i> Target Selesai:</span>
                                <span class="font-weight-bold text-dark">{{ $studiLanjut->target_selesai ? $studiLanjut->target_selesai->format('d M Y') : '-' }}</span>
                            </li>
                            @if($studiLanjut->progres === 'selesai' && $studiLanjut->tanggal_selesai)
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0 text-success font-weight-bold">
                                    <span><i class="fas fa-graduation-cap mr-1"></i> Tanggal Lulus:</span>
                                    <span>{{ $studiLanjut->tanggal_selesai->format('d M Y') }}</span>
                                </li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>

            {{-- Right Column: Academic Details, Funding, Assignment & Documents --}}
            <div class="col-lg-8 col-md-7 col-12">
                {{-- Academic Details Card --}}
                <div class="card shadow-sm">
                    <div class="card-header bg-whitesmoke">
                        <h4><i class="fas fa-university text-primary mr-2"></i>Informasi Akademik & Perguruan Tinggi</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 col-12 mb-3">
                                <label class="text-xs text-uppercase font-weight-bold text-muted">Program Studi Tujuan</label>
                                <div class="font-weight-bold text-dark h6 mb-0">
                                    @if($studiLanjut->jenjang)
                                        <span class="badge badge-secondary mr-1">{{ $studiLanjut->jenjang }}</span>
                                    @endif
                                    {{ $studiLanjut->program_studi }}
                                </div>
                            </div>
                            <div class="col-md-6 col-12 mb-3">
                                <label class="text-xs text-uppercase font-weight-bold text-muted">Bidang Ilmu</label>
                                <div>
                                    <span class="badge {{ $studiLanjut->bidang_ilmu_badge_class }} py-1 px-2 font-weight-bold">
                                        <i class="fas fa-atom mr-1"></i> {{ $studiLanjut->bidang_ilmu }}
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-6 col-12 mb-3">
                                <label class="text-xs text-uppercase font-weight-bold text-muted">Perguruan Tinggi / Institusi</label>
                                <div class="text-dark font-weight-bold">{{ $studiLanjut->nama_institusi }}</div>
                            </div>
                            <div class="col-md-6 col-12 mb-3">
                                <label class="text-xs text-uppercase font-weight-bold text-muted">Negara</label>
                                <div class="text-dark">
                                    <i class="fas fa-globe mr-1 text-info"></i> {{ $studiLanjut->negara ?? 'Indonesia' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Funding & Assignment Card --}}
                <div class="card shadow-sm mt-3">
                    <div class="card-header bg-whitesmoke">
                        <h4><i class="fas fa-hand-holding-usd text-primary mr-2"></i>Pembiayaan & Penugasan</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 col-12 mb-3">
                                <label class="text-xs text-uppercase font-weight-bold text-muted">Jenis Pembiayaan</label>
                                <div>
                                    @if($studiLanjut->jenis_pembiayaan === 'beasiswa')
                                        <span class="badge badge-primary py-1 px-2">
                                            <i class="fas fa-award mr-1"></i> Beasiswa
                                        </span>
                                        @if($studiLanjut->nama_beasiswa)
                                            <div class="text-dark font-weight-bold mt-1">{{ $studiLanjut->nama_beasiswa }}</div>
                                        @endif
                                    @else
                                        <span class="badge badge-secondary py-1 px-2">
                                            <i class="fas fa-user mr-1"></i> Mandiri
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6 col-12 mb-3">
                                <label class="text-xs text-uppercase font-weight-bold text-muted">Jenis Penugasan</label>
                                <div>
                                    <span class="badge badge-light border text-dark py-1 px-2 font-weight-bold">
                                        <i class="fas fa-briefcase text-info mr-1"></i> {{ $studiLanjut->jenis_tugas }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- SK Legalitas & Notes Card --}}
                <div class="card shadow-sm mt-3">
                    <div class="card-header bg-whitesmoke">
                        <h4><i class="fas fa-file-contract text-primary mr-2"></i>Surat Keputusan (SK) & Catatan</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 col-12 mb-3">
                                <label class="text-xs text-uppercase font-weight-bold text-muted">Nomor SK</label>
                                <div class="text-dark font-weight-bold">{{ $studiLanjut->nomor_sk ?? '-' }}</div>
                            </div>
                            <div class="col-md-6 col-12 mb-3">
                                <label class="text-xs text-uppercase font-weight-bold text-muted">Tanggal SK</label>
                                <div class="text-dark">{{ $studiLanjut->tanggal_sk ? $studiLanjut->tanggal_sk->format('d F Y') : '-' }}</div>
                            </div>
                            <div class="col-12 mb-3">
                                <label class="text-xs text-uppercase font-weight-bold text-muted">Dokumen SK</label>
                                <div>
                                    @if($studiLanjut->dokumen_sk)
                                        <a href="{{ asset($studiLanjut->dokumen_sk) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-download mr-1"></i> Unduh / Buka Dokumen SK
                                        </a>
                                    @else
                                        <span class="text-muted font-italic">Tidak ada file SK yang diunggah.</span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="text-xs text-uppercase font-weight-bold text-muted">Catatan Perkembangan</label>
                                <div class="p-3 bg-light rounded text-dark">
                                    {{ $studiLanjut->keterangan ?: 'Belum ada catatan tambahan.' }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-whitesmoke d-flex justify-content-between">
                        <a href="{{ route('kepegawaian.studi-lanjut.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left mr-1"></i> Kembali ke Daftar
                        </a>
                        <div>
                            <a href="{{ route('kepegawaian.studi-lanjut.edit', $studiLanjut) }}" class="btn btn-warning mr-1">
                                <i class="fas fa-edit mr-1"></i> Edit Data
                            </a>
                            <button type="button" class="btn btn-danger" onclick="confirmDelete('{{ $studiLanjut->id }}', '{{ addslashes($studiLanjut->pegawai?->nama_lengkap ?? 'Pegawai') }}')">
                                <i class="fas fa-trash mr-1"></i> Hapus
                            </button>
                            <form id="delete-form-{{ $studiLanjut->id }}" action="{{ route('kepegawaian.studi-lanjut.destroy', $studiLanjut) }}" method="POST" style="display: none;">
                                @csrf
                                @method('DELETE')
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function confirmDelete(id, name) {
            Swal.fire({
                title: 'Hapus Data Studi Lanjut?',
                text: 'Data pemantauan studi lanjut untuk "' + name + '" akan dihapus.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('delete-form-' + id).submit();
                }
            });
        }
    </script>
    @endpush
</x-app-layout>
