<x-app-layout>
    @section('title', $title)
    <x-slot name="header">
        <h1>{{ $title }}</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{ route('dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item active"><a href="{{ route('dokumen.index') }}">Dokumen</a></div>
            <div class="breadcrumb-item">{{ $dokumen->nama_dokumen }}</div>
        </div>
    </x-slot>

    <div class="section-body">
        <h2 class="section-title">{{ $dokumen->nama_dokumen }}</h2>
        <p class="section-lead">Detail master dokumen dan jumlah pemakaiannya di data pegawai.</p>

        <div class="card">
            <div class="card-header">
                <h4>Informasi Dokumen</h4>
                <div class="card-header-action">
                    <a href="{{ route('dokumen.edit', ['dokumen' => $dokumen]) }}" class="btn btn-primary">Edit</a>
                    <a href="{{ route('dokumen.index') }}" class="btn btn-outline-dark">Kembali</a>
                </div>
            </div>
            <div class="card-body">
                <table class="table table-sm table-striped">
                    <tbody>
                        <tr><th style="width:220px;">Kode Dokumen</th><td>{{ $dokumen->kode_dokumen }}</td></tr>
                        <tr><th>Nama Dokumen</th><td>{{ $dokumen->nama_dokumen }}</td></tr>
                        <tr><th>Jumlah Upload Pegawai</th><td>{{ $dokumen->dokumen_pegawais_count }}</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>

