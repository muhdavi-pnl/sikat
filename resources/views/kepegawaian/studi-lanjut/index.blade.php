<x-app-layout>
    @section('title', $title)
    <x-slot name="header">
        <h1>{{ $title }}</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{ route('dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item">Kepegawaian</div>
            <div class="breadcrumb-item">{{ $title }}</div>
        </div>
    </x-slot>

    <div class="section-body">
        <h2 class="section-title">Pemantauan Pegawai Studi Lanjut</h2>
        <p class="section-lead">Pantau progres studi lanjut pegawai (defer, ongoing, selesai), jenis pembiayaan (beasiswa, mandiri), jenis penugasan, program studi, dan bidang ilmu.</p>

        {{-- Row 1: Statistics Summary Cards --}}
        <div class="row">
            <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                <div class="card card-statistic-1 shadow-sm">
                    <div class="card-icon bg-primary">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>Total Studi Lanjut</h4>
                        </div>
                        <div class="card-body">
                            {{ $stats['total'] }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                <div class="card card-statistic-1 shadow-sm">
                    <div class="card-icon bg-info">
                        <i class="fas fa-spinner fa-spin"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>Ongoing (Berjalan)</h4>
                        </div>
                        <div class="card-body">
                            {{ $stats['ongoing'] }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                <div class="card card-statistic-1 shadow-sm">
                    <div class="card-icon bg-warning">
                        <i class="fas fa-pause-circle"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>Defer (Ditunda)</h4>
                        </div>
                        <div class="card-body">
                            {{ $stats['defer'] }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                <div class="card card-statistic-1 shadow-sm">
                    <div class="card-icon bg-success">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>Selesai (Lulus)</h4>
                        </div>
                        <div class="card-body">
                            {{ $stats['selesai'] }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Row 2: Secondary stats (Pembiayaan & Penugasan) --}}
        <div class="row mb-3">
            <div class="col-md-6 col-12">
                <div class="card mb-0 shadow-sm border-left-primary">
                    <div class="card-body py-2 px-3 d-flex justify-content-between align-items-center">
                        <span class="font-weight-bold text-dark"><i class="fas fa-hand-holding-usd text-primary mr-1"></i> Jenis Pembiayaan:</span>
                        <div>
                            <span class="badge badge-primary mr-1"><i class="fas fa-award mr-1"></i> Beasiswa: <strong>{{ $stats['beasiswa'] }}</strong></span>
                            <span class="badge badge-secondary"><i class="fas fa-user mr-1"></i> Mandiri: <strong>{{ $stats['mandiri'] }}</strong></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-12 mt-2 mt-md-0">
                <div class="card mb-0 shadow-sm border-left-info">
                    <div class="card-body py-2 px-3 d-flex justify-content-between align-items-center">
                        <span class="font-weight-bold text-dark"><i class="fas fa-briefcase text-info mr-1"></i> Jenis Tugas:</span>
                        <div>
                            <span class="badge badge-info mr-1"><i class="fas fa-plane-departure mr-1"></i> Meninggalkan Tugas: <strong>{{ $stats['meninggalkan_tugas'] }}</strong></span>
                            <span class="badge badge-dark"><i class="fas fa-laptop-code mr-1"></i> Menjalankan Tugas: <strong>{{ $stats['menjalankan_tugas'] }}</strong></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filter & Main Table --}}
        <div class="card card-primary shadow-sm">
            <div class="card-header d-flex flex-wrap justify-content-between align-items-center">
                <h4><i class="fas fa-list-alt text-primary mr-2"></i>Daftar Pemantauan Studi Lanjut Pegawai</h4>
                <div class="card-header-action d-flex flex-wrap gap-2">
                    <a href="{{ route('kepegawaian.studi-lanjut.export', request()->query()) }}" class="btn btn-outline-success btn-icon icon-left mr-2" title="Unduh data dalam format CSV/Excel">
                        <i class="fas fa-file-excel"></i> Ekspor CSV
                    </a>
                    <a href="{{ route('kepegawaian.studi-lanjut.create') }}" class="btn btn-primary btn-icon icon-left">
                        <i class="fas fa-plus"></i> Tambah Pegawai Studi
                    </a>
                </div>
            </div>
            <div class="card-body border-bottom bg-light py-3">
                <form method="GET" action="{{ route('kepegawaian.studi-lanjut.index') }}" class="row g-2 align-items-end">
                    <div class="col-lg-3 col-md-4 col-sm-6 col-12">
                        <label class="font-weight-bold text-xs text-uppercase text-muted mb-1">Cari Data</label>
                        <input type="text" name="search" class="form-control form-control-sm" placeholder="Nama, NIP, Prodi, Kampus, SK..." value="{{ $filters['search'] }}">
                    </div>
                    <div class="col-lg-2 col-md-3 col-sm-6 col-12">
                        <label class="font-weight-bold text-xs text-uppercase text-muted mb-1">Progres</label>
                        <select name="progres" class="form-control form-control-sm">
                            <option value="">-- Semua Progres --</option>
                            @foreach($progresOptions as $val => $lbl)
                                <option value="{{ $val }}" {{ $filters['progres'] === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-3 col-sm-6 col-12">
                        <label class="font-weight-bold text-xs text-uppercase text-muted mb-1">Pembiayaan</label>
                        <select name="jenis_pembiayaan" class="form-control form-control-sm">
                            <option value="">-- Semua Pembiayaan --</option>
                            @foreach($jenisPembiayaanOptions as $val => $lbl)
                                <option value="{{ $val }}" {{ $filters['jenis_pembiayaan'] === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-3 col-sm-6 col-12">
                        <label class="font-weight-bold text-xs text-uppercase text-muted mb-1">Jenis Tugas</label>
                        <select name="jenis_tugas" class="form-control form-control-sm">
                            <option value="">-- Semua Tugas --</option>
                            @foreach($jenisTugasOptions as $val => $lbl)
                                <option value="{{ $val }}" {{ $filters['jenis_tugas'] === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-3 col-sm-6 col-12">
                        <label class="font-weight-bold text-xs text-uppercase text-muted mb-1">Bidang Ilmu</label>
                        <select name="bidang_ilmu" class="form-control form-control-sm">
                            <option value="">-- Semua Bidang --</option>
                            @foreach($bidangIlmuOptions as $val => $lbl)
                                <option value="{{ $val }}" {{ $filters['bidang_ilmu'] === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-1 col-md-2 col-sm-6 col-12 d-flex">
                        <button type="submit" class="btn btn-sm btn-primary w-100 mr-1" title="Terapkan Filter"><i class="fas fa-filter"></i></button>
                        @if(!empty(array_filter($filters)))
                            <a href="{{ route('kepegawaian.studi-lanjut.index') }}" class="btn btn-sm btn-outline-secondary" title="Reset Filter"><i class="fas fa-undo"></i></a>
                        @endif
                    </div>
                </form>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th style="width: 45px;">#</th>
                                <th>Pegawai</th>
                                <th>Program Studi & Kampus</th>
                                <th>Bidang Ilmu</th>
                                <th>Jenis Tugas & Biaya</th>
                                <th>Progres</th>
                                <th>Periode</th>
                                <th class="text-center" style="width: 140px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($studiLanjuts as $item)
                                <tr>
                                    <td>{{ $i + $loop->iteration }}</td>
                                    <td>
                                        <div class="font-weight-bold text-dark">
                                            <a href="{{ route('kepegawaian.studi-lanjut.show', $item) }}" class="text-primary font-weight-bold text-decoration-none">
                                                {{ $item->pegawai?->nama_lengkap ?? 'Pegawai' }}
                                            </a>
                                        </div>
                                        <small class="text-muted d-block">
                                            <i class="fas fa-id-card mr-1"></i> {{ $item->pegawai?->nip ?? '-' }}
                                        </small>
                                        <small class="text-muted d-block">
                                            <i class="fas fa-building mr-1"></i> {{ $item->pegawai?->program_studi?->program_studi ?? $item->pegawai?->unit_kerja?->unit_kerja ?? '-' }}
                                        </small>
                                    </td>
                                    <td>
                                        <div>
                                            @if($item->jenjang)
                                                <span class="badge badge-secondary badge-sm mr-1">{{ $item->jenjang }}</span>
                                            @endif
                                            <span class="font-weight-bold text-dark">{{ $item->program_studi }}</span>
                                        </div>
                                        <small class="text-muted d-block">
                                            <i class="fas fa-university mr-1"></i> {{ $item->nama_institusi }}
                                            @if($item->negara && strtolower($item->negara) !== 'indonesia')
                                                <span class="badge badge-light border text-xs ml-1"><i class="fas fa-globe-americas mr-1"></i> {{ $item->negara }}</span>
                                            @endif
                                        </small>
                                    </td>
                                    <td>
                                        <span class="badge {{ $item->bidang_ilmu_badge_class }} font-weight-bold text-uppercase">
                                            {{ $item->bidang_ilmu }}
                                        </span>
                                    </td>
                                    <td>
                                        <div>
                                            <span class="badge badge-light border text-dark font-weight-bold">
                                                <i class="fas fa-briefcase mr-1 text-info"></i> {{ $item->jenis_tugas }}
                                            </span>
                                        </div>
                                        <div class="mt-1">
                                            @if($item->jenis_pembiayaan === 'beasiswa')
                                                <span class="badge badge-primary">
                                                    <i class="fas fa-award mr-1"></i> Beasiswa
                                                </span>
                                                @if($item->nama_beasiswa)
                                                    <small class="text-muted d-block mt-0 font-italic">({{ $item->nama_beasiswa }})</small>
                                                @endif
                                            @else
                                                <span class="badge badge-secondary">
                                                    <i class="fas fa-user mr-1"></i> Mandiri
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge {{ $item->progres_badge_class }} font-weight-bold">
                                            @if($item->progres === 'ongoing')
                                                <i class="fas fa-spinner fa-spin mr-1"></i>
                                            @elseif($item->progres === 'defer')
                                                <i class="fas fa-pause-circle mr-1"></i>
                                            @elseif($item->progres === 'selesai')
                                                <i class="fas fa-check-circle mr-1"></i>
                                            @endif
                                            {{ $item->progres_label }}
                                        </span>
                                    </td>
                                    <td>
                                        <small class="d-block text-muted">
                                            <strong>Mulai:</strong> {{ $item->tanggal_mulai ? $item->tanggal_mulai->format('d M Y') : '-' }}
                                        </small>
                                        @if($item->progres === 'selesai' && $item->tanggal_selesai)
                                            <small class="d-block text-success font-weight-bold">
                                                <strong>Lulus:</strong> {{ $item->tanggal_selesai->format('d M Y') }}
                                            </small>
                                        @elseif($item->target_selesai)
                                            <small class="d-block text-muted">
                                                <strong>Target:</strong> {{ $item->target_selesai->format('d M Y') }}
                                            </small>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="{{ route('kepegawaian.studi-lanjut.show', $item) }}" class="btn btn-info" title="Lihat Detail"><i class="fas fa-eye"></i></a>
                                            <a href="{{ route('kepegawaian.studi-lanjut.edit', $item) }}" class="btn btn-warning" title="Edit Data"><i class="fas fa-edit"></i></a>
                                            <button type="button" class="btn btn-danger" title="Hapus Data" onclick="confirmDelete('{{ $item->id }}', '{{ addslashes($item->pegawai?->nama_lengkap ?? 'Pegawai') }}')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                        <form id="delete-form-{{ $item->id }}" action="{{ route('kepegawaian.studi-lanjut.destroy', $item) }}" method="POST" style="display: none;">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5 text-muted">
                                        <i class="fas fa-user-graduate fa-3x mb-3 text-secondary d-block"></i>
                                        <h5 class="text-muted">Tidak ada data pegawai studi lanjut</h5>
                                        <p class="mb-3">Belum ada catatan studi lanjut atau data tidak ditemukan dengan filter yang dipilih.</p>
                                        <a href="{{ route('kepegawaian.studi-lanjut.create') }}" class="btn btn-sm btn-primary">
                                            <i class="fas fa-plus mr-1"></i> Tambah Data Baru
                                        </a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($studiLanjuts->hasPages())
                <div class="card-footer d-flex justify-content-between align-items-center">
                    <div class="small text-muted">
                        Menampilkan {{ $studiLanjuts->firstItem() ?? 0 }} sampai {{ $studiLanjuts->lastItem() ?? 0 }} dari {{ $studiLanjuts->total() }} data
                    </div>
                    <div>
                        {{ $studiLanjuts->links() }}
                    </div>
                </div>
            @endif
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
