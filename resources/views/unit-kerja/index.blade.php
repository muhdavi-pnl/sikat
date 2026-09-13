<x-app-layout>
    @push('plugins_css')
    @endpush

    @push('plugins_css')
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
        <p class="section-lead">Kelola referensi unit kerja dan jumlah pegawai yang saat ini tercatat pada tiap unit.</p>

        <div class="row">
            <div class="col-12">
                <div class="card">
                <div class="card-header">
                    <h4>Daftar {{ $title }}</h4>
                    <form class="card-header-form">
                        <div class="input-group">
                            <input type="text" name="search" class="form-control" placeholder="Cari nama unit kerja">
                            <div class="input-group-btn">
                                <button class="btn btn-primary btn-icon"><i class="fas fa-search"></i></button>
                            </div>
                        </div>
                    </form>
                    <div class="card-header-action ml-2">
                        <a href="#" class="btn btn-primary btn-icon icon-left">
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
                                <th>Unit Kerja</th>
                                <th>Jumlah Pegawai</th>
                                <th class="table-actions-col">Aksi</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($unit_kerjas as $unit_kerja)
                                <tr>
                                    <td class="text-center">{{ $unit_kerja->id }}</td>
                                    <td>{{ $unit_kerja->unit_kerja }}</td>
                                    <td>{{ $unit_kerja->pegawais->count() }}</td>
                                    <td class="table-actions-cell">
                                        <a href="#" title="Detail Unit Kerja" class="btn btn-icon btn-info my-1"><i class="fas fa-info-circle"></i></a>
                                        <a href="#" title="Edit Unit Kerja" class="btn btn-icon btn-primary my-1"><i class="fas fa-edit"></i></a>
                                        <a href="#" title="Hapus Unit Kerja" class="btn btn-icon btn-danger my-1"><i class="fas fa-trash-alt"></i></a>
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
    {{ $unit_kerjas->links() }}

    @push('plugins_js')
    @endpush

    @push('page_js')
    @endpush
</x-app-layout>


