<x-app-layout>
    @section('title', $title)
    <x-slot name="header">
        <h1>{{ $title }}</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{ route('dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item active"><a href="{{ route('rak.index') }}">Rak</a></div>
            <div class="breadcrumb-item">{{ $rak->rak }}</div>
        </div>
    </x-slot>

    <div class="section-body">
        <h2 class="section-title">{{ $rak->rak }}</h2>
        <p class="section-lead">Detail rak arsip dan riwayat lokasi arsip yang ditempatkan di dalamnya.</p>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>Informasi Rak</h4>
                <div>
                    <a href="{{ route('rak.edit', $rak) }}" class="btn btn-primary mr-2">Edit</a>
                    <a href="{{ route('rak.index') }}" class="btn btn-outline-secondary">Kembali</a>
                </div>
            </div>
            <div class="card-body">
                <table class="table table-sm table-striped">
                    <tbody>
                        <tr><th style="width:220px;">Gedung</th><td>{{ optional(optional(optional($rak->lemari)->ruang)->gedung)->nama_gedung ?: '-' }}</td></tr>
                        <tr><th>Ruang</th><td>{{ optional(optional($rak->lemari)->ruang)->kode_ruang }} - {{ optional(optional($rak->lemari)->ruang)->nama_ruang }}</td></tr>
                        <tr><th>Lemari</th><td><a href="{{ route('lemari.show', $rak->lemari) }}">{{ optional($rak->lemari)->lemari ?: '-' }}</a></td></tr>
                        <tr><th>Nama Rak</th><td>{{ $rak->rak }}</td></tr>
                        <tr><th>Keterangan</th><td>{{ $rak->keterangan ?: '-' }}</td></tr>
                        <tr><th>Jumlah Lokasi Arsip</th><td>{{ $rak->lokasi_arsips_count }}</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h4>Lokasi Arsip di Rak Ini</h4></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped mb-0">
                        <thead><tr><th>#</th><th>Pegawai</th><th>NIP</th><th>Keterangan</th></tr></thead>
                        <tbody>
                            @forelse($rak->lokasiArsips as $lokasi)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td><a href="{{ route('lokasi-arsip.show', $lokasi) }}">{{ optional($lokasi->pegawai)->nama ?: '-' }}</a></td>
                                    <td>{{ optional($lokasi->pegawai)->nip ?: '-' }}</td>
                                    <td>{{ $lokasi->keterangan ?: '-' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted">Belum ada lokasi arsip terdaftar.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

