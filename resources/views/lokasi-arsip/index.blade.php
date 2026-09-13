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
        <p class="section-lead">Kelola penempatan arsip fisik pegawai sampai ke level rak penyimpanan.</p>

        <div class="card">
            <div class="card-header">
                <h4>Daftar {{ $title }}</h4>
                <div class="card-header-form mr-2">
                    <form method="GET" action="{{ route('lokasi-arsip.index') }}">
                        <div class="input-group">
                            <input type="text" name="search" class="form-control" placeholder="Cari pegawai, NIP, rak, lemari, ruang, atau gedung" value="{{ $keyword }}">
                            <div class="input-group-btn"><button class="btn btn-primary btn-icon"><i class="fas fa-search"></i></button></div>
                        </div>
                    </form>
                </div>
                <div class="card-header-action">
                    <a href="{{ route('lokasi-arsip.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Tambah Lokasi Arsip</a>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped mb-0">
                        <thead class="table-dark"><tr><th>#</th><th>Pegawai</th><th>Rak</th><th>Lemari</th><th>Ruang / Gedung</th><th class="table-actions-col">Aksi</th></tr></thead>
                        <tbody>
                            @forelse($lokasiArsips as $lokasiArsip)
                                <tr>
                                    <td>{{ $i + $loop->iteration }}</td>
                                    <td>
                                        <div class="font-weight-bold">{{ optional($lokasiArsip->pegawai)->nama ?: '-' }}</div>
                                        <div class="text-muted small">{{ optional($lokasiArsip->pegawai)->nip ?: '-' }}</div>
                                    </td>
                                    <td>{{ optional($lokasiArsip->rak)->rak ?: '-' }}</td>
                                    <td>{{ optional(optional($lokasiArsip->rak)->lemari)->lemari ?: '-' }}</td>
                                    <td>{{ optional(optional(optional($lokasiArsip->rak)->lemari)->ruang)->kode_ruang }} - {{ optional(optional(optional(optional($lokasiArsip->rak)->lemari)->ruang)->gedung)->nama_gedung }}</td>
                                    <td class="table-actions-cell">
                                        <a href="{{ route('lokasi-arsip.show', $lokasiArsip) }}" title="Detail Lokasi Arsip" class="btn btn-icon btn-info my-1">
                                            <i class="fas fa-info-circle"></i>
                                        </a>
                                        <a href="{{ route('lokasi-arsip.edit', $lokasiArsip) }}" title="Edit Lokasi Arsip" class="btn btn-icon btn-primary my-1">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('lokasi-arsip.destroy', $lokasiArsip) }}" method="POST" class="d-inline js-confirm-submit" data-confirm-variant="delete" data-confirm-title="Yakin ingin menghapus data ini?" data-confirm-text="Data lokasi arsip ini akan dihapus dari master penempatan arsip." data-confirm-item-label="Lokasi Arsip" data-confirm-item-name="{{ optional($lokasiArsip->pegawai)->nama ?: ('Lokasi Arsip #' . $lokasiArsip->id) }}" data-confirm-button="Ya, hapus">
                                            @csrf @method('DELETE')
                                            <button type="submit" title="Hapus Lokasi Arsip" class="btn btn-icon btn-danger my-1">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="table-empty-row">Belum ada data.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{ $lokasiArsips->links() }}
    </div>
</x-app-layout>

