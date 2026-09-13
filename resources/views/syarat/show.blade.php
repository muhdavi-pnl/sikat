<x-app-layout>
    @section('title', $title)
    <x-slot name="header">
        <h1>{{ $title }}</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{ route('dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item active"><a href="{{ route('syarat.index') }}">Syarat</a></div>
            <div class="breadcrumb-item">{{ $title }}</div>
        </div>
    </x-slot>

    @php
        $profileOption = collect($profileRequirementOptions)->firstWhere('code', $syarat->kode_syarat);
        $isCutiOptionalRequirement = in_array((string) $syarat->kode_syarat, $cutiOptionalRequirementCodes ?? [], true);
    @endphp

    <div class="section-body">
        <h2 class="section-title">{{ $syarat->syarat }}</h2>
        <p class="section-lead">Detail mapping syarat untuk kebutuhan eligibility precheck layanan.</p>

        <div class="card">
            <div class="card-header">
                <h4>Informasi Syarat</h4>
                <div class="card-header-action">
                    <a href="{{ route('syarat.edit', $syarat) }}" class="btn btn-primary">Edit</a>
                    <a href="{{ route('syarat.index') }}" class="btn btn-outline-dark">Kembali</a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <small class="text-muted d-block">Nama Syarat</small>
                            <strong>{{ $syarat->syarat }}</strong>
                            @if($isCutiOptionalRequirement)
                                <div class="mt-2">
                                    <span class="badge badge-secondary">Opsional untuk layanan cuti</span>
                                </div>
                            @endif
                        </div>
                        <div class="mb-3">
                            <small class="text-muted d-block">Jenis Mapping</small>
                            <span class="badge {{ $syarat->mappingBadgeClass() }}">{{ $syarat->mappingLabel() }}</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <small class="text-muted d-block">Target Mapping</small>
                            @if($syarat->dokumen_id)
                                <strong>{{ optional($syarat->dokumen)->nama_dokumen ?: '-' }}</strong>
                                <div class="text-muted small">{{ optional($syarat->dokumen)->kode_dokumen ?: '' }}</div>
                            @elseif($syarat->kode_syarat)
                                <strong>{{ $profileOption['label'] ?? $syarat->kode_syarat }}</strong>
                                <div class="text-muted small">{{ $syarat->kode_syarat }}</div>
                            @else
                                <span class="text-muted">Diverifikasi manual oleh petugas</span>
                            @endif
                        </div>
                        <div class="mb-3">
                            <small class="text-muted d-block">Digunakan pada Layanan</small>
                            <strong>{{ $syarat->layanan->count() }}</strong>
                        </div>
                    </div>
                </div>

                @if($isCutiOptionalRequirement)
                    <div class="alert alert-light border mb-0 mt-2">
                        <i class="fas fa-info-circle mr-1"></i>
                        Kode syarat ini ditandai <strong>opsional</strong> saat dipakai pada layanan cuti. Pada layanan selain cuti, kode yang sama tetap mengikuti evaluasi normal.
                    </div>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h4>Daftar Layanan Terkait</h4>
            </div>
            <div class="card-body p-0">
                @if($syarat->layanan->isEmpty())
                    <div class="p-4 text-muted">Syarat ini belum dipasang pada layanan mana pun.</div>
                @else
                    <div class="table-responsive">
                        <table class="table table-striped mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>#</th>
                                    <th>Layanan</th>
                                    <th>Jenis</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($syarat->layanan as $layanan)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $layanan->layanan }}</td>
                                        <td>{{ ucfirst($layanan->jenis) }}</td>
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

