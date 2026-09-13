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
        <p class="section-lead">Lihat referensi agama yang tersedia dan jumlah pegawai yang terhubung pada setiap data.</p>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Daftar {{ $title }}</h4>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped" id="table-1">
                            <thead class="table-dark">
                            <tr>
                                <th class="text-center">#</th>
                                <th>Agama</th>
                                <th>Jumlah Pegawai</th>
                                <th class="table-actions-col">Aksi</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($agamas as $agama)
                                <tr>
                                    <td class="text-center">{{ $agama->id }}</td>
                                    <td>{{ $agama->agama }}</td>
                                    <td>{{ $agama->pegawais->count() }}</td>
                                    <td class="table-actions-cell">
                                        <a href="#" title="Detail Agama" class="btn btn-icon btn-info my-1"><i class="fas fa-info-circle"></i></a>
                                        <a href="#" title="Edit Agama" class="btn btn-icon btn-primary my-1"><i class="fas fa-edit"></i></a>
                                        <a href="#" title="Hapus Agama" class="btn btn-icon btn-danger my-1"><i class="fas fa-trash-alt"></i></a>
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
    </div>

    @push('plugins_js')
    @endpush

    @push('page_js')
    @endpush
</x-app-layout>


