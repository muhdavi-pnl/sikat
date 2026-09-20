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
        <p class="section-lead">Kelola pengumuman teks maupun unggahan gambar banner yang akan tampil otomatis dalam bentuk pop-up saat pengguna login ke sistem.</p>

        <div class="card card-primary">
            <div class="card-header">
                <h4>Daftar {{ $title }}</h4>
                <div class="card-header-action d-flex flex-wrap align-items-center">
                    <form method="GET" action="{{ route('pengumuman.index') }}" class="form-inline mr-2 my-1">
                        <div class="input-group input-group-sm mr-2">
                            <select name="tipe" class="form-control" onchange="this.form.submit()">
                                <option value="">-- Semua Tipe --</option>
                                <option value="teks" {{ $tipe === 'teks' ? 'selected' : '' }}>Teks</option>
                                <option value="gambar" {{ $tipe === 'gambar' ? 'selected' : '' }}>Gambar</option>
                                <option value="keduanya" {{ $tipe === 'keduanya' ? 'selected' : '' }}>Teks & Gambar</option>
                            </select>
                        </div>
                        <div class="input-group input-group-sm mr-2">
                            <select name="status" class="form-control" onchange="this.form.submit()">
                                <option value="">-- Semua Status --</option>
                                <option value="1" {{ $status === '1' ? 'selected' : '' }}>Aktif</option>
                                <option value="0" {{ $status === '0' ? 'selected' : '' }}>Nonaktif</option>
                            </select>
                        </div>
                        <div class="input-group input-group-sm">
                            <input type="text" name="search" class="form-control" placeholder="Cari judul / isi..." value="{{ $keyword }}">
                            <div class="input-group-append">
                                <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i></button>
                                @if($keyword || $tipe || $status !== null && $status !== '')
                                    <a href="{{ route('pengumuman.index') }}" class="btn btn-outline-secondary" title="Reset filter"><i class="fas fa-undo"></i></a>
                                @endif
                            </div>
                        </div>
                    </form>
                    <a href="{{ route('pengumuman.create') }}" class="btn btn-icon icon-left btn-primary my-1">
                        <i class="fas fa-plus"></i> Tambah Pengumuman
                    </a>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th style="width: 50px;">#</th>
                                <th>Judul & Pembuat</th>
                                <th>Tipe</th>
                                <th>Pratinjau</th>
                                <th>Periode Berlaku</th>
                                <th>Target</th>
                                <th>Status</th>
                                <th class="table-actions-col text-center" style="width: 170px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pengumumans as $item)
                                <tr>
                                    <td>{{ $i + $loop->iteration }}</td>
                                    <td>
                                        <div class="font-weight-bold text-dark">{{ $item->judul }}</div>
                                        <small class="text-muted">
                                            <i class="far fa-user mr-1"></i> {{ $item->creator->name ?? 'Sistem' }} &bull;
                                            <i class="far fa-clock mr-1"></i> {{ $item->created_at ? $item->created_at->format('d M Y H:i') : '-' }}
                                        </small>
                                    </td>
                                    <td>
                                        @if($item->tipe === 'teks')
                                            <span class="badge badge-info"><i class="fas fa-align-left mr-1"></i> Teks</span>
                                        @elseif($item->tipe === 'gambar')
                                            <span class="badge badge-success"><i class="fas fa-image mr-1"></i> Gambar</span>
                                        @else
                                            <span class="badge badge-warning text-dark"><i class="fas fa-photo-video mr-1"></i> Teks & Gambar</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($item->gambar)
                                            <a href="{{ asset($item->gambar) }}" target="_blank" title="Lihat gambar ukuran penuh">
                                                <img src="{{ asset($item->gambar) }}" alt="Thumbnail" class="img-thumbnail rounded shadow-sm" style="max-height: 48px; max-width: 80px; object-fit: cover;">
                                            </a>
                                        @elseif($item->isi)
                                            <span class="text-muted small" title="{{ $item->isi }}">
                                                {{ \Illuminate\Support\Str::limit($item->isi, 45) }}
                                            </span>
                                        @else
                                            <span class="text-muted small">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($item->tanggal_mulai || $item->tanggal_selesai)
                                            <small class="d-block text-muted">
                                                {{ $item->tanggal_mulai ? $item->tanggal_mulai->format('d/m/Y') : 'Sekarang' }}
                                                s.d.
                                                {{ $item->tanggal_selesai ? $item->tanggal_selesai->format('d/m/Y') : 'Selamanya' }}
                                            </small>
                                        @else
                                            <span class="badge badge-light border">Selamanya</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge badge-secondary text-uppercase">{{ $item->target_role ?? 'Semua' }}</span>
                                    </td>
                                    <td>
                                        <form action="{{ route('pengumuman.toggle-status', ['pengumuman' => $item]) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm {{ $item->is_aktif ? 'btn-success' : 'btn-secondary' }}" title="Klik untuk mengubah status">
                                                <i class="fas {{ $item->is_aktif ? 'fa-check-circle' : 'fa-times-circle' }} mr-1"></i>
                                                {{ $item->is_aktif ? 'Aktif' : 'Nonaktif' }}
                                            </button>
                                        </form>
                                    </td>
                                    <td class="table-actions-cell text-center">
                                        <a href="{{ route('pengumuman.show', ['pengumuman' => $item]) }}" title="Detail Pengumuman" class="btn btn-icon btn-info btn-sm my-1">
                                            <i class="fas fa-info-circle"></i>
                                        </a>
                                        <a href="{{ route('pengumuman.edit', ['pengumuman' => $item]) }}" title="Edit Pengumuman" class="btn btn-icon btn-primary btn-sm my-1">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('pengumuman.destroy', ['pengumuman' => $item]) }}" method="POST" class="d-inline js-confirm-submit" data-confirm-variant="delete" data-confirm-title="Yakin ingin menghapus pengumuman ini?" data-confirm-text="Data pengumuman dan file banner terkait akan dihapus secara permanen." data-confirm-item-label="Judul Pengumuman" data-confirm-item-name="{{ $item->judul }}" data-confirm-button="Ya, hapus">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" title="Hapus Pengumuman" class="btn btn-icon btn-danger btn-sm my-1">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted">
                                        <i class="fas fa-bullhorn fa-2x mb-2 d-block text-secondary"></i>
                                        Belum ada data pengumuman yang tersedia.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($pengumumans->hasPages())
                <div class="card-footer bg-whitesmoke text-right">
                    {{ $pengumumans->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
