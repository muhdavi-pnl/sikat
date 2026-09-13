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
        <p class="section-lead">Kelola lemari arsip berdasarkan ruang dan gedung penyimpanannya.</p>

        <div class="card">
            <div class="card-header">
                <h4>Daftar {{ $title }}</h4>
                <div class="card-header-form mr-2">
                    <form method="GET" action="{{ route('lemari.index') }}">
                        <div class="input-group">
                            <input type="text" name="search" class="form-control" placeholder="Cari lemari, ruang, atau gedung" value="{{ $keyword }}">
                            <div class="input-group-btn"><button class="btn btn-primary btn-icon"><i class="fas fa-search"></i></button></div>
                        </div>
                    </form>
                </div>
                <div class="card-header-action">
                    <a href="{{ route('lemari.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Tambah Lemari</a>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped mb-0">
                        <thead class="table-dark"><tr><th>#</th><th>Lemari</th><th>Ruang</th><th>Gedung</th><th>Jumlah Rak</th><th class="table-actions-col">Aksi</th></tr></thead>
                        <tbody>
                            @forelse($lemaris as $lemari)
                                <tr>
                                    <td>{{ $i + $loop->iteration }}</td>
                                    <td>{{ $lemari->lemari }}</td>
                                    <td>{{ optional($lemari->ruang)->kode_ruang }} - {{ optional($lemari->ruang)->nama_ruang }}</td>
                                    <td>{{ optional(optional($lemari->ruang)->gedung)->nama_gedung ?: '-' }}</td>
                                    <td>{{ $lemari->raks_count }}</td>
                                    <td class="table-actions-cell">
                                        <a href="{{ route('lemari.show', $lemari) }}" title="Detail Lemari" class="btn btn-icon btn-info my-1">
                                            <i class="fas fa-info-circle"></i>
                                        </a>
                                        <a href="{{ route('lemari.edit', $lemari) }}" title="Edit Lemari" class="btn btn-icon btn-primary my-1">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('lemari.destroy', $lemari) }}" method="POST" class="d-inline js-confirm-submit" data-confirm-variant="delete" data-confirm-title="Yakin ingin menghapus data ini?" data-confirm-text="Data lemari ini akan dihapus dari master arsip." data-confirm-item-label="Nama Lemari" data-confirm-item-name="{{ $lemari->lemari }}" data-confirm-button="Ya, hapus">
                                            @csrf @method('DELETE')
                                            <button type="submit" title="Hapus Lemari" class="btn btn-icon btn-danger my-1">
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

        {{ $lemaris->links() }}
    </div>
</x-app-layout>

