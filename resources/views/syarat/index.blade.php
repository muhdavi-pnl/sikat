<x-app-layout>
    @push('plugins_css')
    @endpush

    @push('page_css')
    @endpush

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
        <p class="section-lead">Kelola persyaratan layanan, mapping ke dokumen atau profil, dan jumlah layanan yang menggunakannya.</p>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Daftar {{ $title }}</h4>
                        <form class="card-header-form">
                            <div class="input-group">
                                <input type="text" name="search" class="form-control" placeholder="Cari nama syarat" value="{{ $keyword }}">
                                <div class="input-group-btn">
                                    <button class="btn btn-primary btn-icon"><i class="fas fa-search"></i></button>
                                </div>
                            </div>
                        </form>
                        <div class="card-header-action ml-2">
                            <a href="{{ route('syarat.create') }}" class="btn btn-icon icon-left btn-primary">
                                <i class="fas fa-plus"></i> Tambah Syarat
                            </a>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead class="table-dark">
                                <tr>
                                    <th class="text-center">#</th>
                                    <th>Kode Syarat</th>
                                    <th>Syarat</th>
                                    <th>Mapping</th>
                                    <th>Target</th>
                                    <th>Jumlah Layanan</th>
                                    <th class="table-actions-col">Aksi</th>
                                </tr>
                                </thead>
                                <tbody>
                            @forelse($syarats as $syarat)
                                @php
                                    $isCutiOptionalRequirement = in_array((string) $syarat->kode_syarat, $cutiOptionalRequirementCodes ?? [], true);
                                @endphp
                                <tr>
                                    <td class="text-center">{{ $syarats->firstItem() + $loop->index }}</td>
                                    <td>{{ $syarat->kode_syarat ?: '-' }}</td>
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
                                            <span class="text-muted">Diverifikasi manual oleh petugas</span>
                                        @endif
                                    </td>
                                    <td>{{ $syarat->layanan_count }}</td>
                                    <td class="table-actions-cell">
                                        <a href="{{ route('syarat.show', $syarat) }}" title="Detail Syarat" class="btn btn-icon btn-info my-1"><i class="fas fa-info-circle"></i></a>
                                        <a href="{{ route('syarat.edit', $syarat) }}" title="Edit Syarat" class="btn btn-icon btn-primary my-1"><i class="fas fa-edit"></i></a>
                                        <form action="{{ route('syarat.destroy', $syarat) }}" method="POST" class="d-inline js-confirm-submit" data-confirm-variant="delete" data-confirm-title="Yakin ingin menghapus data ini?" data-confirm-text="Data syarat ini akan dihapus dari master persyaratan layanan." data-confirm-item-label="Nama Syarat" data-confirm-item-name="{{ $syarat->syarat }}" data-confirm-button="Ya, hapus">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" title="Hapus Syarat" class="btn btn-icon btn-danger my-1"><i class="fas fa-trash-alt"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="table-empty-row">Belum ada data.</td>
                                </tr>
                            @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        {{ $syarats->links() }}
    </div>

    @push('plugins_js')
    @endpush

    @push('page_js')
    @endpush
</x-app-layout>


