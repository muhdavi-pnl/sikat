<x-app-layout>
    @section('title', $title)
    <x-slot name="header">
        <h1>{{ $title }}</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{ route('dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item active"><a href="{{ route('lokasi-arsip.index') }}">Lokasi Arsip</a></div>
            <div class="breadcrumb-item">Detail</div>
        </div>
    </x-slot>

    <div class="section-body">
        <h2 class="section-title">Detail Lokasi Arsip</h2>
        <p class="section-lead">Informasi lengkap lokasi penyimpanan arsip fisik pegawai.</p>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>Informasi Lokasi Arsip</h4>
                <div>
                    <a href="{{ route('lokasi-arsip.edit', $lokasiArsip) }}" class="btn btn-primary mr-2">Edit</a>
                    <a href="{{ route('lokasi-arsip.index') }}" class="btn btn-outline-secondary">Kembali</a>
                </div>
            </div>
            <div class="card-body">
                <table class="table table-sm table-striped">
                    <tbody>
                        <tr><th style="width:220px;">Pegawai</th><td>{{ optional($lokasiArsip->pegawai)->nama ?: '-' }}</td></tr>
                        <tr><th>NIP</th><td>{{ optional($lokasiArsip->pegawai)->nip ?: '-' }}</td></tr>
                        <tr><th>Gedung</th><td>{{ optional(optional(optional(optional($lokasiArsip->rak)->lemari)->ruang)->gedung)->nama_gedung ?: '-' }}</td></tr>
                        <tr><th>Ruang</th><td>{{ optional(optional(optional($lokasiArsip->rak)->lemari)->ruang)->kode_ruang }} - {{ optional(optional(optional($lokasiArsip->rak)->lemari)->ruang)->nama_ruang }}</td></tr>
                        <tr><th>Lemari</th><td>{{ optional(optional($lokasiArsip->rak)->lemari)->lemari ?: '-' }}</td></tr>
                        <tr><th>Rak</th><td>{{ optional($lokasiArsip->rak)->rak ?: '-' }}</td></tr>
                        <tr><th>Keterangan</th><td>{{ $lokasiArsip->keterangan ?: '-' }}</td></tr>
                        <tr><th>Dibuat</th><td>{{ optional($lokasiArsip->created_at)->format('d-m-Y H:i') ?: '-' }}</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>

