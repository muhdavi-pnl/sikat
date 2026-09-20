<x-app-layout>
    @section('title', $title)
    <x-slot name="header">
        <h1>{{ $title }}</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item"><a href="{{ route('pengumuman.index') }}">Pengumuman</a></div>
            <div class="breadcrumb-item active">Detail</div>
        </div>
    </x-slot>

    <div class="section-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h2 class="section-title my-0">{{ $pengumuman->judul }}</h2>
                <p class="section-lead mb-0">Informasi detail dan simulasi tampilan pop-up pengumuman.</p>
            </div>
            <div>
                <a href="{{ route('pengumuman.index') }}" class="btn btn-secondary mr-1"><i class="fas fa-arrow-left mr-1"></i> Kembali</a>
                <a href="{{ route('pengumuman.edit', ['pengumuman' => $pengumuman]) }}" class="btn btn-primary mr-1"><i class="fas fa-edit mr-1"></i> Edit</a>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-5 col-md-12">
                <div class="card card-primary">
                    <div class="card-header">
                        <h4><i class="fas fa-info-circle mr-1"></i> Informasi Metadata</h4>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-striped mb-0">
                            <tbody>
                                <tr>
                                    <th style="width: 40%;">Tipe</th>
                                    <td>
                                        @if($pengumuman->tipe === 'teks')
                                            <span class="badge badge-info"><i class="fas fa-align-left mr-1"></i> Teks Saja</span>
                                        @elseif($pengumuman->tipe === 'gambar')
                                            <span class="badge badge-success"><i class="fas fa-image mr-1"></i> Gambar Saja</span>
                                        @else
                                            <span class="badge badge-warning text-dark"><i class="fas fa-photo-video mr-1"></i> Teks & Gambar</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Status</th>
                                    <td>
                                        <span class="badge {{ $pengumuman->is_aktif ? 'badge-success' : 'badge-danger' }}">
                                            <i class="fas {{ $pengumuman->is_aktif ? 'fa-check-circle' : 'fa-times-circle' }} mr-1"></i>
                                            {{ $pengumuman->is_aktif ? 'Aktif (Tampil di Pop-up)' : 'Nonaktif (Draf)' }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Target Penerima</th>
                                    <td><span class="badge badge-secondary text-uppercase">{{ $pengumuman->target_role ?? 'Semua Pengguna' }}</span></td>
                                </tr>
                                <tr>
                                    <th>Tanggal Mulai</th>
                                    <td>{{ $pengumuman->tanggal_mulai ? $pengumuman->tanggal_mulai->format('d F Y') : 'Langsung Berlaku' }}</td>
                                </tr>
                                <tr>
                                    <th>Tanggal Selesai</th>
                                    <td>{{ $pengumuman->tanggal_selesai ? $pengumuman->tanggal_selesai->format('d F Y') : 'Tanpa Batas Waktu' }}</td>
                                </tr>
                                <tr>
                                    <th>Dibuat Oleh</th>
                                    <td>{{ $pengumuman->creator->name ?? 'Sistem' }}</td>
                                </tr>
                                <tr>
                                    <th>Waktu Dibuat</th>
                                    <td>{{ $pengumuman->created_at ? $pengumuman->created_at->format('d M Y H:i') : '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Terakhir Diubah</th>
                                    <td>{{ $pengumuman->updated_at ? $pengumuman->updated_at->format('d M Y H:i') : '-' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-lg-7 col-md-12">
                <div class="card card-info">
                    <div class="card-header bg-primary text-white">
                        <h4><i class="fas fa-desktop mr-1"></i> Simulasi Tampilan Pop-up</h4>
                    </div>
                    <div class="card-body bg-light">
                        <div class="border rounded bg-white shadow-sm overflow-hidden mx-auto" style="max-width: 600px;">
                            <div class="p-3 bg-primary text-white d-flex justify-content-between align-items-center">
                                <h5 class="mb-0 font-weight-bold"><i class="fas fa-bullhorn mr-2"></i> {{ $pengumuman->judul }}</h5>
                                <span class="badge badge-light text-primary font-weight-bold">Pengumuman</span>
                            </div>

                            @if($pengumuman->gambar)
                                <div class="text-center bg-dark p-2">
                                    <img src="{{ asset($pengumuman->gambar) }}" alt="{{ $pengumuman->judul }}" class="img-fluid rounded" style="max-height: 380px; object-fit: contain;">
                                </div>
                            @endif

                            @if($pengumuman->isi)
                                <div class="p-4 text-dark" style="white-space: pre-line; line-height: 1.6; font-size: 15px;">
                                    {{ $pengumuman->isi }}
                                </div>
                            @endif

                            <div class="p-3 bg-light border-top text-right d-flex justify-content-between align-items-center">
                                <small class="text-muted"><i class="far fa-calendar-alt mr-1"></i> {{ $pengumuman->created_at ? $pengumuman->created_at->format('d M Y') : '' }}</small>
                                <button type="button" class="btn btn-secondary btn-sm" disabled>Tutup</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
