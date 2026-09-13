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
        <p class="section-lead">Kelola rak arsip dan pantau jumlah lokasi arsip yang tersimpan di setiap rak.</p>

        <div class="card">
            <div class="card-header">
                <h4>Daftar {{ $title }}</h4>
                <div class="card-header-form mr-2">
                    <form method="GET" action="{{ route('rak.index') }}">
                        <div class="input-group">
                            <input type="text" name="search" class="form-control" placeholder="Cari rak, lemari, ruang, atau gedung" value="{{ $keyword }}">
                            <div class="input-group-btn"><button class="btn btn-primary btn-icon"><i class="fas fa-search"></i></button></div>
                        </div>
                    </form>
                </div>
                <div class="card-header-action">
                    <a href="{{ route('rak.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Tambah Rak</a>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped mb-0">
                        <thead class="table-dark"><tr><th>#</th><th>Rak</th><th>Lemari</th><th>Ruang</th><th>Lokasi Arsip</th><th class="table-actions-col">Aksi</th></tr></thead>
                        <tbody>
                            @forelse($raks as $rak)
                                <tr>
                                    <td>{{ $i + $loop->iteration }}</td>
                                    <td>{{ $rak->rak }}</td>
                                    <td>{{ optional($rak->lemari)->lemari ?: '-' }}</td>
                                    <td>{{ optional(optional($rak->lemari)->ruang)->kode_ruang }} - {{ optional(optional($rak->lemari)->ruang)->nama_ruang }}</td>
                                    <td>{{ $rak->lokasi_arsips_count }}</td>
                                    <td class="table-actions-cell">
                                        <a href="{{ route('rak.show', $rak) }}" title="Detail Rak" class="btn btn-icon btn-info my-1">
                                            <i class="fas fa-info-circle"></i>
                                        </a>
                                        <a href="{{ route('rak.edit', $rak) }}" title="Edit Rak" class="btn btn-icon btn-primary my-1">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('rak.destroy', $rak) }}" method="POST" class="d-inline js-confirm-submit" data-confirm-variant="delete" data-confirm-title="Yakin ingin menghapus data ini?" data-confirm-text="Data rak ini akan dihapus dari master arsip." data-confirm-item-label="Nama Rak" data-confirm-item-name="{{ $rak->rak }}" data-confirm-button="Ya, hapus">
                                            @csrf @method('DELETE')
                                            <button type="submit" title="Hapus Rak" class="btn btn-icon btn-danger my-1">
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

        {{ $raks->links() }}
    </div>
</x-app-layout>

