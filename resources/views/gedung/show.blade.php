<x-app-layout>
    @section('title', $title)
    <x-slot name="header">
        <h1>{{ $title }}</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{ route('dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item active"><a href="{{ route('gedung.index') }}">Gedung</a></div>
            <div class="breadcrumb-item">{{ $gedung->nama_gedung }}</div>
        </div>
    </x-slot>

    <div class="section-body">
        <h2 class="section-title">{{ $gedung->nama_gedung }}</h2>
        <p class="section-lead">Detail master gedung dan ringkasan ruang yang terhubung.</p>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>Informasi Gedung</h4>
                <div>
                    <a href="{{ route('gedung.edit', $gedung) }}" class="btn btn-primary mr-2">Edit</a>
                    <a href="{{ route('gedung.index') }}" class="btn btn-outline-secondary">Kembali</a>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-striped">
                        <tbody>
                            <tr><th style="width: 220px;">Nama Gedung</th><td>{{ $gedung->nama_gedung }}</td></tr>
                            <tr><th>Alamat Gedung</th><td>{{ $gedung->alamat_gedung ?: '-' }}</td></tr>
                            <tr><th>Keterangan</th><td>{{ $gedung->keterangan ?: '-' }}</td></tr>
                            <tr><th>Jumlah Ruang</th><td>{{ $gedung->ruangs_count }}</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h4>Ruang di Gedung Ini</h4></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped mb-0">
                        <thead>
                            <tr><th>#</th><th>Kode</th><th>Nama Ruang</th><th>Keterangan</th></tr>
                        </thead>
                        <tbody>
                            @forelse($gedung->ruangs as $ruang)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $ruang->kode_ruang }}</td>
                                    <td><a href="{{ route('ruang.show', $ruang) }}">{{ $ruang->nama_ruang }}</a></td>
                                    <td>{{ $ruang->keterangan ?: '-' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted">Belum ada ruang terdaftar.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

