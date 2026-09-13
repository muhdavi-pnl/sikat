<x-app-layout>
    @push('plugins_css')
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
        <p class="section-lead">
            Semua dokumen yang pernah Anda unggah ada disini.
        </p>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Daftar {{ $title }}</h4>
                        {{--<form class="card-header-form">
                            <div class="input-group">
                                <input type="text" name="search" class="form-control" placeholder="Cari nama dokumen">
                                <div class="input-group-btn">
                                    <button class="btn btn-primary btn-icon"><i class="fas fa-search"></i></button>
                                </div>
                            </div>
                        </form>--}}
                        <div class="card-header-action">
                            <a href="{{ route('dokumen-arsip.create') }}" class="btn btn-icon icon-left btn-primary">
                                <i class="fas fa-plus"></i> Unggah Dokumen
                            </a>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead class="table-dark">
                                <tr>
                                    <th scope="col" class="w-50">Nama Dokumen</th>
                                    <th scope="col" class="text-center">Nomor</th>
                                    <th scope="col" class="text-center">Tanggal</th>
                                    <th scope="col" class="text-center">Status</th>
                                    <th scope="col" class="table-actions-col">Aksi</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($dokumen_arsip->dokumen as $dokumen)
                                    <tr>
                                        <td>{{ $dokumen->nama_dokumen }}</td>
                                        <td class="text-center">{{ $dokumen->pivot->nomor ? $dokumen->pivot->nomor : '-' }}</td>
                                        <td class="text-center">{{ $dokumen->pivot->tanggal ? Date('d-m-Y', strtotime($dokumen->pivot->tanggal)) : '-' }}</td>
                                        <td class="table-actions-cell">
                                            @if($dokumen->pivot->status == 0)
                                                <div class="badge btn-icon icon-left badge-dark my-1">Menunggu Verifikasi</div>
                                            @elseif($dokumen->pivot->status == 1)
                                                <div class="badge btn-icon icon-left badge-success my-1">Valid</div>
                                            @else
                                                <div class="badge btn-icon icon-left badge-danger my-1">Ditolak</div>
                                                <div class="text-small text-muted"><em>{{ $dokumen->pivot->keterangan }}</em></div>
                                            @endif
                                        </td>
                                        <td class="table-actions-cell">
                                            <a href="{{ route('download', [$dokumen->pivot->file, Crypt::encryptString($dokumen_arsip->nip)]) }}"
                                               title="Download Dokumen" class="btn btn-icon icon-left btn-primary my-1">
                                                <i class="fa fa-download"></i>
                                            </a>
                                            <a href="{{ route("dokumen-arsip.detach", [$dokumen->id, Crypt::encryptString($dokumen_arsip->id)]) }}"
                                               title="Hapus Dokumen" class="btn btn-icon icon-left btn-danger my-1 js-confirm-link @if($dokumen->pivot->status == 1)disabled @endif" data-confirm-variant="delete" data-confirm-title="Yakin ingin menghapus data ini?" data-confirm-text="Dokumen arsip ini akan dihapus dari daftar dokumen Anda." data-confirm-item-label="Nama Dokumen" data-confirm-item-name="{{ $dokumen->nama_dokumen }}" @if($dokumen->pivot->status == 1) aria-disabled="true" @endif>
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="table-empty-row">Belum ada data.</td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('plugins_js')
    @endpush

    @push('page_js')
    @endpush
</x-app-layout>


