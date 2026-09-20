<x-app-layout>
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
        <p class="section-lead">Kelola unggah dan riwayat dokumen pribadi Anda di halaman ini.</p>

        @if(!$pegawai)
            <div class="card">
                <div class="card-body">
                    <div class="alert alert-warning mb-0">
                        Data pegawai Anda belum tersedia. Silakan hubungi admin kepegawaian untuk menghubungkan akun Anda ke data pegawai.
                    </div>
                </div>
            </div>
        @else
            <div class="card">
                <div class="card-header">
                    <h4>Skor Kelengkapan Data</h4>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3" style="gap: 0.75rem;">
                        <span class="badge {{ $insight['score'] >= 80 ? 'badge-success' : ($insight['score'] >= 60 ? 'badge-warning' : 'badge-danger') }}">{{ $insight['score'] }}%</span>
                        <small class="text-muted">Profil {{ $insight['profile_score'] }}% | Dokumen {{ $insight['document_score'] }}% (valid {{ $insight['total_owned_documents'] }}/{{ $insight['total_scored_documents'] }})</small>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <small class="font-weight-bold">Progress Kelengkapan Total</small>
                            <small class="text-muted">{{ $insight['score'] }}%</small>
                        </div>
                        <div class="progress" data-testid="dokumen-progress-total" style="height: 0.75rem;">
                            <div class="progress-bar {{ $insight['score'] >= 80 ? 'bg-success' : ($insight['score'] >= 60 ? 'bg-warning' : 'bg-danger') }}"
                                 role="progressbar"
                                 aria-valuemin="0"
                                 aria-valuemax="100"
                                 aria-valuenow="{{ $insight['score'] }}"
                                 style="width: {{ $insight['score'] }}%;">
                            </div>
                        </div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-12 col-md-6 mb-2 mb-md-0">
                            <div class="d-flex justify-content-between mb-1">
                                <small>Progress Profil</small>
                                <small class="text-muted">{{ $insight['profile_score'] }}%</small>
                            </div>
                            <div class="progress" style="height: 0.55rem;">
                                <div class="progress-bar bg-info"
                                     role="progressbar"
                                     aria-valuemin="0"
                                     aria-valuemax="100"
                                     aria-valuenow="{{ $insight['profile_score'] }}"
                                     style="width: {{ $insight['profile_score'] }}%;">
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="d-flex justify-content-between mb-1">
                                <small>Progress Dokumen</small>
                                <small class="text-muted">{{ $insight['document_score'] }}%</small>
                            </div>
                            <div class="progress" style="height: 0.55rem;">
                                <div class="progress-bar bg-primary"
                                     role="progressbar"
                                     aria-valuemin="0"
                                     aria-valuemax="100"
                                     aria-valuenow="{{ $insight['document_score'] }}"
                                     style="width: {{ $insight['document_score'] }}%;">
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($insight['total_scored_documents'] === 0)
                        <div class="alert alert-warning mb-2">
                            Master dokumen belum tersedia, skor dokumen sementara dianggap lengkap.
                        </div>
                    @elseif(!empty($insight['missing_documents']))
                        <div class="alert alert-warning mb-0">
                            Dokumen master valid belum lengkap:
                            <ul>
                                @foreach($insight['missing_documents'] as $item)
                                    <li>
                                        <strong>{{ $item['nama'] }}</strong>
                                        @if(!empty($item['kode']))
                                            <span class="text-muted">({{ $item['kode'] }})</span>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @else
                        <div class="alert alert-success mb-0">
                            Semua dokumen pada master sudah valid.
                        </div>
                    @endif
                </div>
            </div>

            <div class="row">
                <div class="col-12 col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h4>Unggah Dokumen</h4>
                        </div>
                        <form action="{{ route('pegawai.dokumen.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="card-body">
                                @if($dokumenOptions->isEmpty())
                                    <div class="alert alert-warning">
                                        Master jenis dokumen belum tersedia. Silakan hubungi admin kepegawaian untuk menambahkan data dokumen terlebih dahulu.
                                    </div>
                                @endif

                                @if($errors->any())
                                    <div class="alert alert-danger">
                                        <ul class="mb-0 pl-3">
                                            @foreach($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                <div class="form-group">
                                    <label>Jenis Dokumen <span class="text-danger">*</span></label>
                                    <select name="dokumen_id" class="form-control" required {{ $dokumenOptions->isEmpty() ? 'disabled' : '' }}>
                                        <option value="">-- Pilih Jenis Dokumen --</option>
                                        @foreach($dokumenOptions as $dokumen)
                                            <option value="{{ $dokumen->id }}" {{ (string) old('dokumen_id') === (string) $dokumen->id ? 'selected' : '' }}>{{ $dokumen->nama_dokumen }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>Nomor Dokumen</label>
                                    <input type="text" name="nomor" class="form-control" value="{{ old('nomor') }}">
                                </div>

                                <div class="form-group">
                                    <label>Tanggal Dokumen</label>
                                    <input type="date" name="tanggal" class="form-control" value="{{ old('tanggal') }}">
                                </div>

                                <div class="form-group">
                                    <label>File <span class="text-danger">*</span></label>
                                    <input type="file" name="file" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required {{ $dokumenOptions->isEmpty() ? 'disabled' : '' }}>
                                    <small class="text-muted">Format: PDF/JPG/JPEG/PNG, maksimal 2MB.</small>
                                </div>

                                <div class="form-group mb-0">
                                    <label>Keterangan</label>
                                    <textarea name="keterangan" rows="3" class="form-control">{{ old('keterangan') }}</textarea>
                                </div>
                            </div>
                            <div class="card-footer text-right">
                                <button type="submit" class="btn btn-primary" {{ $dokumenOptions->isEmpty() ? 'disabled' : '' }}>Unggah</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="col-12 col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h4>Riwayat Dokumen ({{ strtoupper($pegawai->nama) }})</h4>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-striped mb-0">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Dokumen</th>
                                            <th>Nomor / Tanggal</th>
                                            <th>Uploader</th>
                                            <th>Status</th>
                                            <th>File</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($dokumenUploads as $upload)
                                            @php
                                                $uploader = $upload->uploader;
                                                $uploaderRole = $uploader && $uploader->hasRole('kepegawaian') ? 'Kepegawaian' : 'Pegawai';
                                            @endphp
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>
                                                    <div class="font-weight-bold">{{ optional($upload->dokumen)->nama_dokumen ?? '-' }}</div>
                                                    <div class="text-muted small">{{ optional($upload->dokumen)->kode_dokumen ?? '-' }}</div>
                                                </td>
                                                <td>
                                                    <div>{{ $upload->nomor ?: '-' }}</div>
                                                    <div class="text-muted small">{{ optional($upload->tanggal)->format('d-m-Y') ?? '-' }}</div>
                                                </td>
                                                <td>
                                                    <div>{{ optional($uploader)->name ?? '-' }}</div>
                                                    <span class="badge {{ $uploaderRole === 'Kepegawaian' ? 'badge-primary' : 'badge-info' }}">{{ $uploaderRole }}</span>
                                                </td>
                                                <td>
                                                    <span class="badge {{ \App\Models\DokumenPegawai::statusBadgeClass($upload->status) }}">{{ \App\Models\DokumenPegawai::statusLabel($upload->status) }}</span>
                                                @if((int) $upload->status === \App\Models\DokumenPegawai::STATUS_REJECTED && $upload->alasan_penolakan)
                                                    <div class="text-danger small mt-1 font-italic">
                                                        <i class="fas fa-exclamation-circle"></i> <strong>Alasan:</strong> {{ $upload->alasan_penolakan }}
                                                    </div>
                                                @endif
                                            </td>
                                                <td>
                                                    <a href="{{ route('arsip.download', [$upload->file, $pegawaiNipCipher]) }}" class="btn btn-sm btn-secondary">
                                                        <i class="fas fa-download"></i> Unduh
                                                    </a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center text-muted">Belum ada dokumen yang diunggah.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-app-layout>


