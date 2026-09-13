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
        <p class="section-lead">Kelola referensi pendidikan untuk pegawai, termasuk asal perguruan tinggi dan tingkat pendidikan.</p>

        <div class="row">
            <div class="col-12">
                <div class="card">
                <div class="card-header">
                    <h4>Daftar {{ $title }}</h4>
                    <form class="card-header-form">
                        <div class="input-group">
                            <input type="text" name="search" class="form-control" placeholder="Cari nama pendidikan">
                            <div class="input-group-btn">
                                <button class="btn btn-primary btn-icon"><i class="fas fa-search"></i></button>
                            </div>
                        </div>
                    </form>
                    <div class="card-header-action ml-2">
                        <a href="{{ route('layanan.create') }}" class="btn btn-icon icon-left btn-primary">
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
                                <th>Program Studi</th>
                                <th>Perguruan Tinggi</th>
                                <th>Tingkat Pendidikan</th>
                                <th>Jumlah Pegawai</th>
                                <th class="table-actions-col">Aksi</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($pendidikans as $pendidikan)
                                <tr>
                                    <td class="text-center">{{ $pendidikan->id }}</td>
                                    <td>{{ $pendidikan->pendidikan }}</td>
                                    <td>{{ $pendidikan->perguruan_tinggi }}</td>
                                    <td>{{ $pendidikan->tingkat_pendidikan->tingkat_pendidikan }}</td>
                                    <td>{{ $pendidikan->pegawais->count() }}</td>
                                    <td class="table-actions-cell">
                                        <a href="#" title="Detail Pendidikan" class="btn btn-icon btn-info my-1"><i class="fas fa-info-circle"></i></a>
                                        <a href="#" title="Edit Pendidikan" class="btn btn-icon btn-primary my-1"><i class="fas fa-edit"></i></a>
                                        <a href="#" title="Hapus Pendidikan" class="btn btn-icon btn-danger my-1"><i class="fas fa-trash-alt"></i></a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="table-empty-row">Belum ada data.</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{ $pendidikans->links() }}

    @push('plugins_js')
    @endpush

    @push('page_js')
    @endpush
</x-app-layout>


