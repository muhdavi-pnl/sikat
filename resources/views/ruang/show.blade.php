<x-app-layout>
    @section('title', $title)
    <x-slot name="header">
        <h1>{{ $title }}</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{ route('dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item active"><a href="{{ route('ruang.index') }}">Ruang</a></div>
            <div class="breadcrumb-item">{{ $ruang->nama_ruang }}</div>
        </div>
    </x-slot>

    <div class="section-body">
        <h2 class="section-title">{{ $ruang->nama_ruang }}</h2>
        <p class="section-lead">Detail ruang dan daftar lemari yang berada di dalamnya.</p>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>Informasi Ruang</h4>
                <div>
                    <a href="{{ route('ruang.edit', $ruang) }}" class="btn btn-primary mr-2">Edit</a>
                    <a href="{{ route('ruang.index') }}" class="btn btn-outline-secondary">Kembali</a>
                </div>
            </div>
            <div class="card-body">
                <table class="table table-sm table-striped">
                    <tbody>
                        <tr><th style="width:220px;">Gedung</th><td><a href="{{ route('gedung.show', $ruang->gedung) }}">{{ optional($ruang->gedung)->nama_gedung ?: '-' }}</a></td></tr>
                        <tr><th>Kode Ruang</th><td>{{ $ruang->kode_ruang }}</td></tr>
                        <tr><th>Nama Ruang</th><td>{{ $ruang->nama_ruang }}</td></tr>
                        <tr><th>Keterangan</th><td>{{ $ruang->keterangan ?: '-' }}</td></tr>
                        <tr><th>Jumlah Lemari</th><td>{{ $ruang->lemaris_count }}</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h4>Lemari di Ruang Ini</h4></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped mb-0">
                        <thead><tr><th>#</th><th>Lemari</th><th>Keterangan</th></tr></thead>
                        <tbody>
                            @forelse($ruang->lemaris as $lemari)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td><a href="{{ route('lemari.show', $lemari) }}">{{ $lemari->lemari }}</a></td>
                                    <td>{{ $lemari->keterangan ?: '-' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center text-muted">Belum ada lemari terdaftar.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

