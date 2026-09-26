<x-app-layout>
    @section('title', $title)

    <x-slot name="header">
        <h1>Detail Jabatan</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{ route('dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item"><a href="{{ route('peta-jabatan.index') }}">Peta Jabatan</a></div>
            <div class="breadcrumb-item"><a href="{{ route('peta-jabatan.manage.index', ['slug' => 'jabatan']) }}">Kelola Jabatan</a></div>
            <div class="breadcrumb-item">{{ $jabatan->jabatan }}</div>
        </div>
    </x-slot>

    <div class="section-body">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
            <div>
                <h2 class="section-title mt-0">{{ $title }}</h2>
                <p class="section-lead mb-0">Informasi komprehensif profil jabatan, hierarki atasan-bawahan, dan pegawai terhubung.</p>
            </div>
            <div class="mt-2 mt-md-0 text-right">
                <a href="{{ route('peta-jabatan.manage.index', ['slug' => 'jabatan']) }}" class="btn btn-outline-secondary mr-1">
                    <i class="fas fa-arrow-left"></i> Kembali ke Daftar
                </a>
                <a href="{{ route('peta-jabatan.manage.edit', ['slug' => 'jabatan', 'id' => $jabatan->id]) }}" class="btn btn-primary mr-1">
                    <i class="fas fa-edit"></i> Edit Jabatan
                </a>
                <form action="{{ route('peta-jabatan.manage.destroy', ['slug' => 'jabatan', 'id' => $jabatan->id]) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus jabatan {{ addslashes($jabatan->jabatan) }}?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash-alt"></i> Hapus
                    </button>
                </form>
            </div>
        </div>

        {{-- Row 1: Profile Summary Cards --}}
        <div class="row">
            <div class="col-lg-8 col-12">
                <div class="card card-hero">
                    <div class="card-header">
                        <div class="card-icon"><i class="fas fa-briefcase"></i></div>
                        <h4>{{ $jabatan->jabatan }}</h4>
                        <div class="card-description">
                            @if ($jabatan->kode_jabatan)
                                <span class="badge badge-light mr-2">Kode: <strong>{{ $jabatan->kode_jabatan }}</strong></span>
                            @endif
                            <span class="badge badge-light mr-2">{{ $jabatan->jenis_jabatan?->jenis_jabatan ?? 'Jenis Tidak Ditentukan' }}</span>
                            <span class="badge badge-light">{{ $jabatan->unit_kerja?->unit_kerja ?? 'Unit Kerja Umum' }}</span>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="tickets-list">
                            <div class="ticket-item border-bottom px-4 py-3">
                                <div class="ticket-title font-weight-bold mb-1"><i class="fas fa-align-left text-primary mr-1"></i> Ikhtisar Jabatan</div>
                                <div class="ticket-desc text-muted">{{ $jabatan->ikhtisar_jabatan ?: 'Belum ada ikhtisar tugas jabatan yang dicatat.' }}</div>
                            </div>
                            <div class="ticket-item px-4 py-3">
                                <div class="row">
                                    <div class="col-md-4 mb-2">
                                        <div class="text-muted small">Jenjang Jabatan</div>
                                        <div class="font-weight-600">{{ $jabatan->jenjang_jabatan ?: '-' }}</div>
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <div class="text-muted small">Kelas Jabatan</div>
                                        <div class="font-weight-600">{{ $jabatan->kelas_jabatan ? 'Kelas ' . $jabatan->kelas_jabatan : '-' }}</div>
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <div class="text-muted small">Pangkat Minimal</div>
                                        <div class="font-weight-600">
                                            @if ($jabatan->pangkat_minimal_rel)
                                                {{ $jabatan->pangkat_minimal_rel->pangkat }} ({{ $jabatan->pangkat_minimal_rel->golongan_ruang }})
                                            @elseif ($jabatan->pangkat_golongan)
                                                {{ $jabatan->pangkat_golongan }}
                                            @else
                                                -
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <div class="text-muted small">Pendidikan Minimal</div>
                                        <div class="font-weight-600">{{ $jabatan->pendidikan_minimal ?: '-' }}</div>
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <div class="text-muted small">Beban Kerja</div>
                                        <div class="font-weight-600">{{ $jabatan->beban_kerja ?: '-' }}</div>
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <div class="text-muted small">Status Jabatan</div>
                                        <div class="font-weight-600">
                                            <span class="badge badge-{{ ($jabatan->status_jabatan ?? 'Aktif') === 'Aktif' ? 'success' : 'secondary' }}">
                                                {{ $jabatan->status_jabatan ?? 'Aktif' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-12">
                {{-- Formasi Card --}}
                <div class="card card-statistic-1">
                    <div class="card-icon bg-primary"><i class="fas fa-users"></i></div>
                    <div class="card-wrap">
                        <div class="card-header"><h4>Pegawai Terisi</h4></div>
                        <div class="card-body">
                            {{ $jabatan->pegawais_count }} <small class="text-muted">/ {{ $jabatan->kebutuhan_pegawai }} Formasi</small>
                        </div>
                    </div>
                </div>

                {{-- Hierarki Card --}}
                <div class="card">
                    <div class="card-header">
                        <h4><i class="fas fa-sitemap text-info mr-1"></i> Hierarki Organisasi</h4>
                    </div>
                    <div class="card-body py-2">
                        <div class="mb-3">
                            <span class="text-muted small d-block mb-1">Atasan Langsung:</span>
                            @if ($jabatan->atasan_langsung)
                                <a href="{{ route('peta-jabatan.manage.show', ['slug' => 'jabatan', 'id' => $jabatan->atasan_langsung->id]) }}" class="d-flex align-items-center text-decoration-none">
                                    <div class="badge badge-primary mr-2"><i class="fas fa-level-up-alt"></i></div>
                                    <div>
                                        <div class="font-weight-bold text-dark">{{ $jabatan->atasan_langsung->jabatan }}</div>
                                        <small class="text-muted">{{ $jabatan->atasan_langsung->unit_kerja?->unit_kerja ?? '-' }}</small>
                                    </div>
                                </a>
                            @else
                                <span class="badge badge-light text-muted"><i class="fas fa-crown text-warning mr-1"></i> Pucuk Pimpinan / Tidak Ada Atasan</span>
                            @endif
                        </div>

                        <div>
                            <span class="text-muted small d-block mb-1">Bawahan Langsung ({{ $jabatan->bawahan->count() }}):</span>
                            @if ($jabatan->bawahan->isNotEmpty())
                                <ul class="list-group list-group-flush">
                                    @foreach ($jabatan->bawahan as $sub)
                                        <li class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center">
                                            <a href="{{ route('peta-jabatan.manage.show', ['slug' => 'jabatan', 'id' => $sub->id]) }}" class="text-primary font-weight-600">
                                                <i class="fas fa-level-down-alt text-muted mr-1"></i>{{ $sub->jabatan }}
                                            </a>
                                            <span class="badge badge-light small">{{ $sub->unit_kerja?->unit_kerja ?? '-' }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <span class="text-muted small fst-italic">Tidak memiliki jabatan bawahan langsung.</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Row 2: Uraian Tugas & Kualifikasi --}}
        <div class="row">
            <div class="col-lg-6 col-12">
                <div class="card">
                    <div class="card-header">
                        <h4><i class="fas fa-tasks text-success mr-2"></i> Tugas &amp; Wewenang</h4>
                    </div>
                    <div class="card-body">
                        <h6 class="font-weight-bold text-dark mb-1">Uraian Tugas:</h6>
                        <p class="text-muted mb-3">{{ $jabatan->uraian_tugas ?: '-' }}</p>

                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="font-weight-bold text-dark mb-1">Tanggung Jawab:</h6>
                                <p class="text-muted mb-0">{{ $jabatan->tanggung_jawab ?: '-' }}</p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="font-weight-bold text-dark mb-1">Wewenang:</h6>
                                <p class="text-muted mb-0">{{ $jabatan->wewenang ?: '-' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6 col-12">
                <div class="card">
                    <div class="card-header">
                        <h4><i class="fas fa-graduation-cap text-warning mr-2"></i> Kualifikasi &amp; Persyaratan</h4>
                    </div>
                    <div class="card-body">
                        <h6 class="font-weight-bold text-dark mb-1">Kompetensi yang Dibutuhkan:</h6>
                        <p class="text-muted mb-3">{{ $jabatan->kompetensi ?: '-' }}</p>

                        <h6 class="font-weight-bold text-dark mb-1">Persyaratan Khusus:</h6>
                        <p class="text-muted mb-0">{{ $jabatan->persyaratan_jabatan ?: '-' }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Row 3: Daftar Pegawai yang Menduduki Jabatan Ini --}}
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4><i class="fas fa-user-tie text-primary mr-2"></i> Pegawai yang Menduduki Jabatan Ini ({{ $jabatan->pegawais->count() }})</h4>
                @if (auth()->user()->can('create', \App\Models\Pegawai::class) || auth()->user()->hasAnyRole(['super-admin', 'kepegawaian']))
                    <a href="{{ route('kepegawaian.pegawai.create') }}" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-user-plus"></i> Tambah Pegawai
                    </a>
                @endif
            </div>
            <div class="card-body p-0 table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width: 50px;">#</th>
                            <th>NIP / NIDN</th>
                            <th>Nama Lengkap</th>
                            <th>Pangkat / Golongan</th>
                            <th>Unit Kerja</th>
                            <th>Status</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($jabatan->pegawais as $idx => $pegawai)
                            <tr>
                                <td class="text-center align-middle">{{ $idx + 1 }}</td>
                                <td class="align-middle">
                                    <span class="font-weight-bold"><code>{{ $pegawai->nip ?? '-' }}</code></span>
                                    @if ($pegawai->nidn)
                                        <div class="text-muted small">NIDN: {{ $pegawai->nidn }}</div>
                                    @endif
                                </td>
                                <td class="align-middle font-weight-600">
                                    {{ $pegawai->nama_lengkap ?? $pegawai->nama }}
                                </td>
                                <td class="align-middle">
                                    {{ $pegawai->pangkat?->pangkat ?? '-' }} {{ $pegawai->pangkat?->golongan_ruang ? '(' . $pegawai->pangkat->golongan_ruang . ')' : '' }}
                                </td>
                                <td class="align-middle">{{ $pegawai->unit_kerja?->unit_kerja ?? '-' }}</td>
                                <td class="align-middle">
                                    <span class="badge badge-success">{{ $pegawai->status_pegawai ?? 'Aktif' }}</span>
                                </td>
                                <td class="text-center align-middle">
                                    <a href="{{ route('kepegawaian.pegawai.show', $pegawai->id) }}" class="btn btn-sm btn-info" title="Lihat Profil Pegawai">
                                        <i class="fas fa-eye"></i> Detail
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    <i class="fas fa-user-slash fa-2x mb-2 d-block text-secondary"></i>
                                    Saat ini belum ada pegawai yang ditugaskan pada jabatan ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Row 4: Career Path (jika ada) --}}
        @if ($jabatan->careerPaths->isNotEmpty())
            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-route text-info mr-2"></i> Career Path Terkait</h4>
                </div>
                <div class="card-body p-0 table-responsive">
                    <table class="table table-striped mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Jabatan Asal</th>
                                <th>Jabatan Promosi / Tujuan</th>
                                <th>Persyaratan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($jabatan->careerPaths as $cp)
                                <tr>
                                    <td>{{ $jabatan->jabatan }}</td>
                                    <td class="font-weight-600 text-primary">{{ $cp->jabatan_tujuan?->jabatan ?? '-' }}</td>
                                    <td>{{ $cp->persyaratan ? json_encode($cp->persyaratan) : '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
