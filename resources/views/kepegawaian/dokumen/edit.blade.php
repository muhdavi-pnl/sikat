<x-app-layout>
    @section('title', $title)
    <x-slot name="header">
        <h1>{{ $title }}</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{ route('dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item active"><a href="{{ route('kepegawaian.dokumen.index') }}">Review Dokumen</a></div>
            <div class="breadcrumb-item active"><a href="{{ route('kepegawaian.dokumen.show', $pegawai) }}">{{ strtoupper($pegawai->nama) }}</a></div>
            <div class="breadcrumb-item">{{ optional($dokumenUpload->dokumen)->nama_dokumen }}</div>
        </div>
    </x-slot>

    <div class="section-body">
        <h2 class="section-title">{{ $title }}</h2>
        <p class="section-lead">Perbarui metadata, validasi, atau file dokumen pegawai.</p>

        <div class="card">
            <div class="card-header">
                <h4>{{ optional($dokumenUpload->dokumen)->nama_dokumen }} - {{ strtoupper($pegawai->nama) }}</h4>
                <div class="card-header-action">
                    <a href="{{ route('kepegawaian.dokumen.show', $pegawai) }}" class="btn btn-outline-dark">Kembali</a>
                </div>
            </div>
            <form method="POST" action="{{ route('kepegawaian.dokumen.update', [$pegawai, $dokumen]) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0 pl-3">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Jenis Dokumen</label>
                                <input type="text" class="form-control" value="{{ optional($dokumenUpload->dokumen)->nama_dokumen }} ({{ optional($dokumenUpload->dokumen)->kode_dokumen }})" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Uploader Terakhir</label>
                                <input type="text" class="form-control" value="{{ optional($dokumenUpload->uploader)->name ?: '-' }}" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Nomor Dokumen</label>
                                <input type="text" name="nomor" class="form-control" value="{{ old('nomor', $dokumenUpload->nomor) }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tanggal Dokumen</label>
                                <input type="date" name="tanggal" class="form-control" value="{{ old('tanggal', optional($dokumenUpload->tanggal)->format('Y-m-d')) }}">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Status Dokumen <span class="text-danger">*</span></label>
                                <select name="status" class="form-control" required>
                                    <option value="1" {{ (string) old('status', (int) $dokumenUpload->status) === '1' ? 'selected' : '' }}>Valid</option>
                                    <option value="0" {{ (string) old('status', (int) $dokumenUpload->status) === '0' ? 'selected' : '' }}>Menunggu Verifikasi</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>File Baru (opsional)</label>
                                <input type="file" name="file" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                                <small class="text-muted">Kosongkan jika tidak ingin mengganti file saat ini.</small>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Keterangan</label>
                        <textarea name="keterangan" rows="4" class="form-control">{{ old('keterangan', $dokumenUpload->keterangan) }}</textarea>
                    </div>

                    <div class="d-flex justify-content-between flex-wrap" style="gap: 0.75rem;">
                        <a href="{{ route('arsip.download', [$dokumenUpload->file, $pegawaiNipCipher]) }}" class="btn btn-secondary">
                            <i class="fas fa-download"></i> Unduh File Saat Ini
                        </a>
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>

