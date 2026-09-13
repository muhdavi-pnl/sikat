<x-app-layout>
    @push('plugins_css')
    @endpush

    @push('page_css')
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
        <p class="section-lead">Kelola referensi jabatan dan pantau jumlah pegawai yang memakai setiap jabatan pada data kepegawaian.</p>

        <div class="row">
            <div class="col-12">
                <div class="card">
                <div class="card-header">
                    <h4>Daftar {{ $title }}</h4>
                    <form class="card-header-form">
                        <div class="input-group">
                            <input type="text" name="search" class="form-control" placeholder="Cari nama jabatan">
                            <div class="input-group-btn">
                                <button class="btn btn-primary btn-icon"><i class="fas fa-search"></i></button>
                            </div>
                        </div>
                    </form>
                    <div class="card-header-action ml-2">
                        <a href="{{ route('jabatan.create') }}" class="btn btn-icon icon-left btn-primary">
                            <i class="fas fa-plus"></i> Tambah {{ $title }}
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead class="table-dark">
                            <tr>
                                <th class="text-center">#</th>
                                <th>Jabatan</th>
                                <th>Jumlah Pegawai</th>
                                <th class="table-actions-col">Aksi</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($jabatans as $jabatan)
                                <tr>
                                    <td class="text-center">{{ $jabatan->id }}</td>
                                    <td>{{ $jabatan->jabatan }}</td>
                                    <td>{{ $jabatan->pegawais->count() }}</td>
                                    <td class="table-actions-cell">
                                        <a href="#" title="Detail Jabatan" class="btn btn-icon btn-info my-1"><i class="fas fa-info-circle"></i></a>
                                        <a href="#" title="Edit Jabatan" class="btn btn-icon btn-primary my-1"><i class="fas fa-edit"></i></a>
                                        <a href="#" title="Hapus Jabatan" class="btn btn-icon btn-danger my-1"><i class="fas fa-trash-alt"></i></a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="table-empty-row">Belum ada data.</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{ $jabatans->links() }}

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


