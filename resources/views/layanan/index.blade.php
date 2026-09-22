<x-app-layout>
    @push('plugins_css')
        <link rel="stylesheet" href="{{ asset('assets/modules/datatables/datatables.min.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/modules/datatables/dataTables.bootstrap4.min.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/modules/datatables/select.bootstrap4.min.css') }}">
    @endpush

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
        <p class="section-lead">Kelola daftar layanan beserta jenis dan jumlah persyaratan yang harus dipenuhi pegawai.</p>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Daftar {{ $title }}</h4>
                        <form class="card-header-form">
                            <div class="input-group">
                                <input type="text" name="search" class="form-control" placeholder="Cari nama layanan" value="{{ $keyword }}">
                                <div class="input-group-btn">
                                    <button class="btn btn-primary btn-icon"><i class="fas fa-search"></i></button>
                                </div>
                            </div>
                        </form>
                        <div class="card-header-action ml-2">
                            <a href="{{ route('layanan.create') }}" class="btn btn-primary btn-icon icon-left">
                                <i class="fas fa-plus"></i> Tambah {{ $title }}
                            </a>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped" id="table-1">
                                <thead class="table-dark">
                                    <tr>
                                        <th class="text-center">#</th>
                                        <th>Layanan</th>
                                        <th>Jenis Layanan</th>
                                        <th>Jumlah Persyaratan</th>
                                        <th class="table-actions-col">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($layanans as $layanan)
                                        <tr>
                                            <td class="text-center">{{ $layanans->firstItem() + $loop->index }}</td>
                                            <td>{{ $layanan->layanan }}</td>
                                            <td>
                                                @if($layanan->jenis == 'kepegawaian')
                                                    <span class="badge badge-warning">Kepegawaian</span>
                                                @elseif($layanan->jenis == 'fungsional')
                                                    <span class="badge badge-success">Fungsional</span>
                                                @else
                                                    <span class="badge badge-secondary">Cuti</span>
                                                @endif
                                            </td>
                                            <td>{{ $layanan->syarat_count }}</td>
                                            <td class="table-actions-cell">
                                                <a href="{{ route('layanan.show', $layanan) }}" title="Detail Layanan" class="btn btn-icon btn-info my-1"><i class="fas fa-info-circle"></i></a>
                                                <a href="{{ route('layanan.edit', $layanan) }}" title="Edit Layanan" class="btn btn-icon btn-primary my-1"><i class="fas fa-edit"></i></a>
                                                <form action="{{ route('layanan.destroy', $layanan) }}" method="POST" class="d-inline js-confirm-submit" data-confirm-variant="delete" data-confirm-title="Yakin ingin menghapus data ini?" data-confirm-text="Data layanan ini akan dihapus dari master layanan." data-confirm-item-label="Nama Layanan" data-confirm-item-name="{{ $layanan->layanan }}" data-confirm-button="Ya, hapus">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" title="Hapus Layanan" class="btn btn-icon btn-danger my-1"><i class="fas fa-trash-alt"></i></button>
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
            </div>
        </div>
        {{ $layanans->links() }}
    </div>

    @push('plugins_js')
        <script src="{{ asset('assets/modules/datatables/datatables.min.js') }}"></script>
        <script src="{{ asset('assets/modules/datatables/dataTables.bootstrap4.min.js') }}"></script>
        <script src="{{ asset('assets/modules/datatables/dataTables.select.min.js') }}"></script>
        <script src="{{ asset('assets/modules/jquery-ui/jquery-ui.min.js') }}"></script>
    @endpush

    @push('page_js')
        <script src="{{ asset('assets/js/page/modules-datatables.js') }}"></script>
    @endpush
</x-app-layout>


