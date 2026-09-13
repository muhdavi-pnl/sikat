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
        <p class="section-lead">Kelola master nama dokumen yang dipakai dalam arsip fisik, unggah pegawai, dan syarat layanan.</p>

        <div class="card">
            <div class="card-header">
                <h4>Daftar {{ $title }}</h4>
                <div class="card-header-form mr-2">
                    <form method="GET" action="{{ route('dokumen.index') }}">
                        <div class="input-group">
                            <input type="text" name="search" class="form-control" placeholder="Cari kode atau nama dokumen" value="{{ $keyword }}">
                            <div class="input-group-btn">
                                <button class="btn btn-primary btn-icon"><i class="fas fa-search"></i></button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="card-header-action">
                    <a href="{{ route('dokumen.create') }}" class="btn btn-icon icon-left btn-primary">
                        <i class="fas fa-plus"></i> Tambah {{ $title }}
                    </a>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <th>Kode</th>
                                <th>Nama Dokumen</th>
                                <th>Jumlah Upload</th>
                                <th class="table-actions-col">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($dokumens as $dokumen)
                                <tr>
                                    <td>{{ $i + $loop->iteration }}</td>
                                    <td>{{ $dokumen->kode_dokumen }}</td>
                                    <td>{{ $dokumen->nama_dokumen }}</td>
                                    <td>{{ $dokumen->dokumen_pegawais_count }}</td>
                                    <td class="table-actions-cell">
                                        <a href="{{ route('dokumen.show', ['dokumen' => $dokumen]) }}" title="Detail Dokumen" class="btn btn-icon btn-info my-1">
                                            <i class="fas fa-info-circle"></i>
                                        </a>
                                        <a href="{{ route('dokumen.edit', ['dokumen' => $dokumen]) }}" title="Edit Dokumen" class="btn btn-icon btn-primary my-1">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('dokumen.destroy', ['dokumen' => $dokumen]) }}" method="POST" class="d-inline js-confirm-submit" data-confirm-variant="delete" data-confirm-title="Yakin ingin menghapus data ini?" data-confirm-text="Master nama dokumen ini akan dihapus dari sistem." data-confirm-item-label="Nama Dokumen" data-confirm-item-name="{{ $dokumen->nama_dokumen }}" data-confirm-button="Ya, hapus">
                                            {!! csrf_field() !!}
                                            {!! method_field('DELETE') !!}
                                            <button type="submit" title="Hapus Dokumen" class="btn btn-icon btn-danger my-1">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="table-empty-row">Belum ada data.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{ $dokumens->links() }}
    </div>
</x-app-layout>


