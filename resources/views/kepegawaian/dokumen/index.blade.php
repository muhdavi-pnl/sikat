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
        <p class="section-lead">Tinjau, validasi, dan kelola dokumen pegawai dari satu modul kepegawaian.</p>

        <div class="card">
            <div class="card-header">
                <h4>Daftar Pegawai</h4>
                <form class="card-header-form" method="GET" action="{{ route('kepegawaian.dokumen.index') }}">
                    <div class="input-group">
                        <input type="text" name="q" class="form-control" value="{{ $keyword }}" placeholder="Cari nama, NIP, atau unit kerja">
                        <div class="input-group-btn">
                            <button class="btn btn-primary btn-icon"><i class="fas fa-search"></i></button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <th>Pegawai</th>
                                <th>Unit Kerja</th>
                                <th>Dokumen</th>
                                <th>Dokumen Valid</th>
                                <th class="table-actions-col">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pegawais as $pegawai)
                                <tr>
                                    <td>{{ $pegawais->firstItem() + $loop->index }}</td>
                                    <td>
                                        <div class="font-weight-bold">{{ strtoupper($pegawai->nama) }}</div>
                                        <div class="text-muted small">{{ $pegawai->nip }}</div>
                                    </td>
                                    <td>{{ optional($pegawai->unit_kerja)->unit_kerja ?: '-' }}</td>
                                    <td>{{ $pegawai->dokumen_pegawais_count }}</td>
                                    <td>{{ $pegawai->dokumen_valid_count }}</td>
                                    <td class="table-actions-cell">
                                        <a href="{{ route('kepegawaian.dokumen.show', $pegawai) }}" class="btn btn-primary btn-sm">Kelola Dokumen</a>
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
            <div class="card-footer text-right">
                {{ $pegawais->links() }}
            </div>
        </div>
    </div>
</x-app-layout>

