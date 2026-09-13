<x-app-layout>
    @section('title', $title)
    <x-slot name="header">
        <h1>{{ $title }}</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{ route('dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item active"><a href="{{ route('lemari.index') }}">Lemari</a></div>
            <div class="breadcrumb-item">{{ $lemari->lemari }}</div>
        </div>
    </x-slot>

    <div class="section-body">
        <h2 class="section-title">{{ $lemari->lemari }}</h2>
        <p class="section-lead">Detail lemari arsip dan daftar rak yang terhubung.</p>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>Informasi Lemari</h4>
                <div>
                    <a href="{{ route('lemari.edit', $lemari) }}" class="btn btn-primary mr-2">Edit</a>
                    <a href="{{ route('lemari.index') }}" class="btn btn-outline-secondary">Kembali</a>
                </div>
            </div>
            <div class="card-body">
                <table class="table table-sm table-striped">
                    <tbody>
                        <tr><th style="width:220px;">Gedung</th><td>{{ optional(optional($lemari->ruang)->gedung)->nama_gedung ?: '-' }}</td></tr>
                        <tr><th>Ruang</th><td><a href="{{ route('ruang.show', $lemari->ruang) }}">{{ optional($lemari->ruang)->kode_ruang }} - {{ optional($lemari->ruang)->nama_ruang }}</a></td></tr>
                        <tr><th>Nama Lemari</th><td>{{ $lemari->lemari }}</td></tr>
                        <tr><th>Keterangan</th><td>{{ $lemari->keterangan ?: '-' }}</td></tr>
                        <tr><th>Jumlah Rak</th><td>{{ $lemari->raks_count }}</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h4>Rak di Lemari Ini</h4></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped mb-0">
                        <thead><tr><th>#</th><th>Rak</th><th>Keterangan</th></tr></thead>
                        <tbody>
                            @forelse($lemari->raks as $rak)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td><a href="{{ route('rak.show', $rak) }}">{{ $rak->rak }}</a></td>
                                    <td>{{ $rak->keterangan ?: '-' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center text-muted">Belum ada rak terdaftar.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

