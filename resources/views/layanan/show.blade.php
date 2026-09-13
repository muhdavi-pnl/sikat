<x-app-layout>
    @section('title', $title)
    <x-slot name="header">
        <h1>{{ $title }}</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{ route('dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item active"><a href="{{ route('layanan.index') }}">Layanan</a></div>
            <div class="breadcrumb-item">{{ $title }}</div>
        </div>
    </x-slot>

    <div class="section-body">
        <h2 class="section-title">{{ $layanan->layanan }}</h2>
        <p class="section-lead">Ringkasan layanan dan persyaratan yang akan dipakai untuk eligibility precheck.</p>

        <div class="card">
            <div class="card-header">
                <h4>Informasi Layanan</h4>
                <div class="card-header-action">
                    <a href="{{ route('layanan.edit', $layanan) }}" class="btn btn-primary">Edit & Kelola Syarat</a>
                    <a href="{{ route('layanan.index') }}" class="btn btn-outline-dark">Kembali</a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <small class="text-muted d-block">Nama Layanan</small>
                        <strong>{{ $layanan->layanan }}</strong>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted d-block">Jenis</small>
                        <span class="badge {{ $layanan->jenis === 'kepegawaian' ? 'badge-warning' : 'badge-success' }}">{{ ucfirst($layanan->jenis) }}</span>
                    </div>
                    <div class="col-md-5">
                        <small class="text-muted d-block">Deskripsi</small>
                        <span>{{ $layanan->deskripsi ?: '-' }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h4>Persyaratan Terkait</h4>
            </div>
            <div class="card-body p-0">
                @if($layanan->syarat->isEmpty())
                    <div class="p-4 text-muted">Belum ada persyaratan terpasang. Klik tombol edit untuk menambahkan syarat.</div>
                @else
                    <div class="table-responsive">
                        <table class="table table-striped mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>#</th>
                                    <th>Syarat</th>
                                    <th>Mapping</th>
                                    <th>Target</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($layanan->syarat as $syarat)
                                    @php
                                        $isCutiOptionalRequirement = in_array((string) $syarat->kode_syarat, $cutiOptionalRequirementCodes ?? [], true);
                                    @endphp
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>
                                            {{ $syarat->syarat }}
                                            @if($isCutiOptionalRequirement)
                                                <div class="mt-1">
                                                    <span class="badge badge-secondary">Opsional untuk layanan cuti</span>
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge {{ $syarat->mappingBadgeClass() }}">{{ $syarat->mappingLabel() }}</span>
                                        </td>
                                        <td>
                                            @if($syarat->dokumen_id)
                                                {{ optional($syarat->dokumen)->nama_dokumen ?: '-' }}
                                            @elseif($syarat->kode_syarat)
                                                {{ $syarat->kode_syarat }}
                                            @else
                                                Verifikasi Manual
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>

