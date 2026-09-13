<x-app-layout>
    @section('title', $title)

    <x-slot name="header">
        <h1>{{ $title }}</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{ route('dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item"><a href="{{ route('peta-jabatan.index') }}">Peta Jabatan</a></div>
            <div class="breadcrumb-item">{{ $title }}</div>
        </div>
    </x-slot>

    <div class="section-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h2 class="section-title my-0">{{ $title }}</h2>
                <p class="section-lead mb-0">
                    @if ($slug === 'jabatan')
                        Kelola seluruh master data jabatan, struktur formasi, hierarki atasan, dan kualifikasi kepegawaian.
                    @else
                        Ringkasan alur career path yang telah terdefinisi.
                    @endif
                </p>
            </div>
            <div>
                <a href="{{ route('peta-jabatan.index') }}" class="btn btn-outline-secondary mr-1">
                    <i class="fas fa-arrow-left"></i> Peta Jabatan
                </a>
                <a href="{{ route('peta-jabatan.manage.create', ['slug' => $slug]) }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Tambah {{ $slug === 'jabatan' ? 'Jabatan' : 'Career Path' }}
                </a>
            </div>
        </div>

        @if ($slug === 'jabatan')
            <div class="card mb-3">
                <div class="card-body py-3">
                    <form method="GET" action="{{ route('peta-jabatan.manage.index', ['slug' => 'jabatan']) }}" class="row g-2 align-items-center">
                        <div class="col-md-3">
                            <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" class="form-control" placeholder="Cari nama, kode, jenjang...">
                        </div>
                        <div class="col-md-3">
                            <select name="unit_kerja_id" class="form-control selectric">
                                <option value="">-- Semua Unit Kerja --</option>
                                @foreach ($unitKerjas ?? [] as $uk)
                                    <option value="{{ $uk->id }}" @selected(($filters['unit_kerja_id'] ?? '') == $uk->id)>{{ $uk->unit_kerja }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="jenis_jabatan_id" class="form-control selectric">
                                <option value="">-- Semua Jenis --</option>
                                @foreach ($jenisJabatans ?? [] as $jj)
                                    <option value="{{ $jj->id }}" @selected(($filters['jenis_jabatan_id'] ?? '') == $jj->id)>{{ $jj->jenis_jabatan }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="status_jabatan" class="form-control selectric">
                                <option value="">-- Status --</option>
                                <option value="Aktif" @selected(($filters['status_jabatan'] ?? '') === 'Aktif')>Aktif</option>
                                <option value="Definitif" @selected(($filters['status_jabatan'] ?? '') === 'Definitif')>Definitif</option>
                                <option value="PLT" @selected(($filters['status_jabatan'] ?? '') === 'PLT')>PLT</option>
                                <option value="PLH" @selected(($filters['status_jabatan'] ?? '') === 'PLH')>PLH</option>
                                <option value="Lowong" @selected(($filters['status_jabatan'] ?? '') === 'Lowong')>Lowong</option>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex">
                            <button type="submit" class="btn btn-primary flex-fill mr-1"><i class="fas fa-search"></i> Cari</button>
                            <a href="{{ route('peta-jabatan.manage.index', ['slug' => 'jabatan']) }}" class="btn btn-light" title="Reset Filter"><i class="fas fa-undo"></i></a>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h4>Daftar {{ $title }}</h4>
                <span class="badge badge-light font-weight-bold">Total: {{ $rows->total() ?? count($rows) }} Data</span>
            </div>
            <div class="card-body p-0 table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th class="text-center" style="width: 50px;">#</th>
                            @if ($slug === 'jabatan')
                                <th>Jabatan &amp; Kode</th>
                                <th>Jenis Jabatan</th>
                                <th>Unit Kerja</th>
                                <th>Atasan Langsung</th>
                                <th class="text-center">Formasi (Isi/Butuh)</th>
                                <th class="text-center">Status</th>
                                <th class="text-center" style="width: 140px;">Aksi</th>
                            @else
                                <th>Jabatan Asal</th>
                                <th>Jabatan Tujuan</th>
                                <th>Persyaratan</th>
                                <th class="text-center" style="width: 140px;">Aksi</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rows as $index => $row)
                            <tr>
                                <td class="text-center align-middle">
                                    {{ method_exists($rows, 'firstItem') ? ($rows->firstItem() + $index) : ($index + 1) }}
                                </td>
                                @if ($slug === 'jabatan')
                                    <td class="align-middle">
                                        <div class="font-weight-bold">
                                            <a href="{{ route('peta-jabatan.manage.show', ['slug' => 'jabatan', 'id' => $row->id]) }}" class="text-primary">
                                                {{ $row->jabatan }}
                                            </a>
                                        </div>
                                        <div class="text-muted small">
                                            @if ($row->kode_jabatan)
                                                <span class="badge badge-light px-2 py-0 mr-1"><code>{{ $row->kode_jabatan }}</code></span>
                                            @endif
                                            @if ($row->jenjang_jabatan)
                                                <span class="badge badge-info px-2 py-0 mr-1">{{ $row->jenjang_jabatan }}</span>
                                            @endif
                                            @if ($row->kelas_jabatan)
                                                <span class="badge badge-secondary px-2 py-0">Kelas {{ $row->kelas_jabatan }}</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="align-middle">
                                        <span class="badge badge-outline-primary">{{ $row->jenis_jabatan?->jenis_jabatan ?? '-' }}</span>
                                    </td>
                                    <td class="align-middle">{{ $row->unit_kerja?->unit_kerja ?? '-' }}</td>
                                    <td class="align-middle">
                                        @if ($row->atasan_langsung)
                                            <a href="{{ route('peta-jabatan.manage.show', ['slug' => 'jabatan', 'id' => $row->atasan_langsung->id]) }}" class="text-dark font-weight-600">
                                                <i class="fas fa-level-up-alt text-muted mr-1"></i>{{ $row->atasan_langsung->jabatan }}
                                            </a>
                                        @else
                                            <span class="text-muted fst-italic">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center align-middle">
                                        @php
                                            $terisi = $row->pegawais_count ?? 0;
                                            $butuh = $row->kebutuhan_pegawai ?? 0;
                                            $badgeColor = ($butuh > 0 && $terisi >= $butuh) ? 'badge-success' : ($terisi > 0 ? 'badge-warning' : 'badge-danger');
                                        @endphp
                                        <span class="badge {{ $badgeColor }} font-weight-bold" title="Terisi: {{ $terisi }}, Kebutuhan: {{ $butuh }}">
                                            {{ $terisi }} / {{ $butuh }}
                                        </span>
                                    </td>
                                    <td class="text-center align-middle">
                                        @if(($row->status_jabatan ?? 'Aktif') === 'Aktif' || ($row->status_jabatan ?? '') === 'Definitif')
                                            <span class="badge badge-success">{{ $row->status_jabatan ?? 'Aktif' }}</span>
                                        @else
                                            <span class="badge badge-secondary">{{ $row->status_jabatan ?? 'Aktif' }}</span>
                                        @endif
                                    </td>
                                    <td class="text-center align-middle">
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('peta-jabatan.manage.show', ['slug' => 'jabatan', 'id' => $row->id]) }}" class="btn btn-sm btn-info" title="Detail Jabatan">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('peta-jabatan.manage.edit', ['slug' => 'jabatan', 'id' => $row->id]) }}" class="btn btn-sm btn-primary" title="Edit Jabatan">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('peta-jabatan.manage.destroy', ['slug' => 'jabatan', 'id' => $row->id]) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus jabatan {{ addslashes($row->jabatan) }}?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" title="Hapus Jabatan">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                @else
                                    <td class="align-middle">{{ $row->jabatan_asal?->jabatan ?? '-' }}</td>
                                    <td class="align-middle">{{ $row->jabatan_tujuan?->jabatan ?? '-' }}</td>
                                    <td class="align-middle">{{ $row->persyaratan ? json_encode($row->persyaratan) : '-' }}</td>
                                    <td class="text-center align-middle">
                                        <span class="text-muted small">Read-Only</span>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $slug === 'jabatan' ? 8 : 4 }}" class="text-center text-muted py-4">
                                    <i class="fas fa-folder-open fa-2x mb-2 d-block text-secondary"></i>
                                    Belum ada data {{ $title }} yang sesuai kriteria.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if(method_exists($rows, 'hasPages') && $rows->hasPages())
                <div class="card-footer bg-white border-top py-3 d-flex justify-content-end">
                    {{ $rows->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
