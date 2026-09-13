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
        <p class="section-lead">Kelola master data gedung sebagai tingkat teratas struktur arsip fisik.</p>

        <div class="card">
            <div class="card-header">
                <h4>Daftar {{ $title }}</h4>
                <div class="card-header-form mr-2">
                    <form method="GET" action="{{ route('gedung.index') }}">
                        <div class="input-group">
                            <input type="text" name="search" class="form-control" placeholder="Cari nama atau alamat gedung" value="{{ $keyword }}">
                            <div class="input-group-btn"><button class="btn btn-primary btn-icon"><i class="fas fa-search"></i></button></div>
                        </div>
                    </form>
                </div>
                <div class="card-header-action">
                    <a href="{{ route('gedung.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Tambah Gedung</a>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped mb-0">
                        <thead class="table-dark">
                            <tr><th>#</th><th>Nama Gedung</th><th>Alamat</th><th>Jumlah Ruang</th><th class="table-actions-col">Aksi</th></tr>
                        </thead>
                        <tbody>
                            @forelse($gedungs as $gedung)
                                <tr>
                                    <td>{{ $i + $loop->iteration }}</td>
                                    <td>{{ $gedung->nama_gedung }}</td>
                                    <td>{{ $gedung->alamat_gedung ?: '-' }}</td>
                                    <td>{{ $gedung->ruangs_count }}</td>
                                    <td class="table-actions-cell">
                                        <a href="{{ route('gedung.show', $gedung) }}" title="Detail Gedung" class="btn btn-icon btn-info my-1">
                                            <i class="fas fa-info-circle"></i>
                                        </a>
                                        <a href="{{ route('gedung.edit', $gedung) }}" title="Edit Gedung" class="btn btn-icon btn-primary my-1">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('gedung.destroy', $gedung) }}" method="POST" class="d-inline js-confirm-submit" data-confirm-variant="delete" data-confirm-title="Yakin ingin menghapus data ini?" data-confirm-text="Data gedung ini akan dihapus dari master arsip." data-confirm-item-label="Nama Gedung" data-confirm-item-name="{{ $gedung->nama_gedung }}" data-confirm-button="Ya, hapus">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" title="Hapus Gedung" class="btn btn-icon btn-danger my-1">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="table-empty-row">Belum ada data.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{ $gedungs->links() }}
    </div>
</x-app-layout>


