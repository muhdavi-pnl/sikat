<x-app-layout>
    @push('plugins_css')
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    @endpush

    @push('page_css')
        <style>
            .select2-container {
                width: 100% !important;
            }
            .stat-card-modern {
                border-radius: 10px;
                transition: all 0.3s ease;
            }
            .stat-card-modern:hover {
                box-shadow: 0 4px 15px rgba(0,0,0,0.06);
            }
            .document-upload-card {
                border-radius: 10px;
                border-top: 3px solid #6777ef;
            }
            .document-history-card {
                border-radius: 10px;
                border-top: 3px solid #3abaf4;
            }
            .custom-file-label::after {
                content: "Cari";
            }
        </style>
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
        <p class="section-lead">Kelola kelengkapan dokumen arsip, unggah dokumen mandiri, dan pantau riwayat verifikasi kepegawaian.</p>

        @if(!$pegawai)
            <div class="card">
                <div class="card-body">
                    <div class="alert alert-warning mb-0">
                        <i class="fas fa-exclamation-triangle mr-2"></i> Data pegawai Anda belum tersedia. Silakan hubungi admin kepegawaian untuk menghubungkan akun Anda ke data pegawai.
                    </div>
                </div>
            </div>
        @else
            {{-- Card Skor Kelengkapan Data --}}
            <div class="card stat-card-modern border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h4 class="text-primary mb-0 font-weight-bold">
                        <i class="fas fa-chart-pie mr-2"></i> Skor Kelengkapan Data
                    </h4>
                    <span class="badge {{ $insight['score'] >= 80 ? 'badge-success' : ($insight['score'] >= 60 ? 'badge-warning' : 'badge-danger') }} px-3 py-2 font-weight-bold" style="font-size: 0.95rem;">
                        {{ $insight['score'] }}%
                    </span>
                </div>
                <div class="card-body">
                    <div class="row align-items-center mb-4">
                        <div class="col-12 col-md-4 mb-3 mb-md-0">
                            <div class="p-3 bg-light rounded text-center border">
                                <small class="text-muted text-uppercase d-block font-weight-bold" style="font-size: 0.75rem;">Status Kepegawaian</small>
                                <span class="badge {{ $pegawai->status_pegawai_badge_class }} px-3 py-1 mt-1 font-weight-bold" style="font-size: 0.88rem;">
                                    {{ $pegawai->status_pegawai ?: 'PNS' }}
                                </span>
                                <div class="mt-2 text-muted small">
                                    Dokumen {{ $insight['document_score'] }}% (valid {{ $insight['total_owned_documents'] }}/{{ $insight['total_scored_documents'] }})
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-8">
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="font-weight-bold text-dark"><i class="fas fa-tasks text-primary mr-1"></i> Progress Kelengkapan Total</span>
                                    <span class="font-weight-bold text-primary">{{ $insight['score'] }}%</span>
                                </div>
                                <div class="progress" data-testid="dokumen-progress-total" style="height: 0.85rem; border-radius: 6px;">
                                    <div class="progress-bar progress-bar-striped progress-bar-animated {{ $insight['score'] >= 80 ? 'bg-success' : ($insight['score'] >= 60 ? 'bg-warning' : 'bg-danger') }}"
                                         role="progressbar"
                                         aria-valuemin="0"
                                         aria-valuemax="100"
                                         aria-valuenow="{{ $insight['score'] }}"
                                         style="width: {{ $insight['score'] }}%;">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12 col-sm-6 mb-2 mb-sm-0">
                                    <div class="d-flex justify-content-between mb-1">
                                        <small class="text-muted"><i class="fas fa-user-circle text-info mr-1"></i> Progress Profil</small>
                                        <small class="font-weight-bold">{{ $insight['profile_score'] }}%</small>
                                    </div>
                                    <div class="progress" style="height: 0.55rem; border-radius: 4px;">
                                        <div class="progress-bar bg-info"
                                             role="progressbar"
                                             aria-valuemin="0"
                                             aria-valuemax="100"
                                             aria-valuenow="{{ $insight['profile_score'] }}"
                                             style="width: {{ $insight['profile_score'] }}%;">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-sm-6">
                                    <div class="d-flex justify-content-between mb-1">
                                        <small class="text-muted"><i class="fas fa-folder text-primary mr-1"></i> Progress Dokumen</small>
                                        <small class="font-weight-bold">{{ $insight['document_score'] }}%</small>
                                    </div>
                                    <div class="progress" style="height: 0.55rem; border-radius: 4px;">
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
                        </div>
                    </div>

                    @if($insight['total_scored_documents'] === 0)
                        <div class="alert alert-warning mb-0 border-0 shadow-sm d-flex align-items-center">
                            <i class="fas fa-info-circle mr-2 fa-lg"></i>
                            <div>Master dokumen belum tersedia, skor dokumen sementara dianggap lengkap.</div>
                        </div>
                    @elseif(!empty($insight['missing_documents']))
                        <div class="alert alert-warning mb-0 border-0 shadow-sm">
                            <div class="font-weight-bold mb-2 d-flex align-items-center">
                                <i class="fas fa-exclamation-triangle mr-2 text-warning"></i>
                                <span>Dokumen master valid belum lengkap:</span>
                            </div>
                            <ul class="mb-0 pl-3">
                                @foreach($insight['missing_documents'] as $item)
                                    <li class="mb-1">
                                        <strong>{{ $item['nama'] }}</strong>
                                        @if(!empty($item['kode']))
                                            <span class="text-muted">({{ $item['kode'] }})</span>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @else
                        <div class="alert alert-success mb-0 border-0 shadow-sm d-flex align-items-center">
                            <i class="fas fa-check-circle mr-2 fa-lg text-success"></i>
                            <div class="font-weight-bold">Semua dokumen pada master sudah valid.</div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Form Unggah Dokumen --}}
            <div class="row">
                <div class="col-12 col-lg-12 mb-4">
                    <div class="card document-upload-card shadow-sm">
                        <div class="card-header bg-white border-bottom py-3">
                            <h4 class="text-primary mb-0 font-weight-bold">
                                <i class="fas fa-cloud-upload-alt mr-2"></i> Unggah Dokumen
                            </h4>
                        </div>
                        <form action="{{ route('pegawai.dokumen.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="card-body">
                                @if($dokumenOptions->isEmpty())
                                    <div class="alert alert-warning">
                                        <i class="fas fa-exclamation-circle mr-1"></i> Master jenis dokumen belum tersedia. Silakan hubungi admin kepegawaian untuk menambahkan data dokumen terlebih dahulu.
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

                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label class="font-weight-bold"><i class="fas fa-file-alt text-primary mr-1"></i> Jenis Dokumen <span class="text-danger">*</span></label>
                                        <select name="dokumen_id" class="form-control select2" required {{ $dokumenOptions->isEmpty() ? 'disabled' : '' }}>
                                            <option value="">-- Pilih Jenis Dokumen --</option>
                                            @foreach($dokumenOptions as $dokumen)
                                                <option value="{{ $dokumen->id }}" {{ (string) old('dokumen_id') === (string) $dokumen->id ? 'selected' : '' }}>
                                                    {{ $dokumen->nama_dokumen }} ({{ $dokumen->kode_dokumen }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label class="font-weight-bold"><i class="fas fa-hashtag text-info mr-1"></i> Nomor Dokumen</label>
                                        <input type="text" name="nomor" class="form-control" placeholder="Contoh: 123/SK/2026" value="{{ old('nomor') }}">
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label class="font-weight-bold"><i class="fas fa-calendar-alt text-warning mr-1"></i> Tanggal Dokumen</label>
                                        <input type="date" name="tanggal" class="form-control" value="{{ old('tanggal') }}">
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label class="font-weight-bold"><i class="fas fa-paperclip text-success mr-1"></i> File Dokumen <span class="text-danger">*</span></label>
                                        <input type="file" name="file" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required {{ $dokumenOptions->isEmpty() ? 'disabled' : '' }}>
                                        <small class="text-muted d-block mt-1">Format diperbolehkan: PDF, JPG, JPEG, PNG (Maksimal 2MB).</small>
                                    </div>
                                </div>

                                <div class="form-group mb-0">
                                    <label class="font-weight-bold"><i class="fas fa-comment-dots text-secondary mr-1"></i> Keterangan / Catatan</label>
                                    <textarea name="keterangan" rows="2" class="form-control" placeholder="Tuliskan keterangan tambahan bila ada...">{{ old('keterangan') }}</textarea>
                                </div>
                            </div>
                            <div class="card-footer bg-light text-right py-3">
                                <button type="submit" class="btn btn-primary px-4 font-weight-bold" {{ $dokumenOptions->isEmpty() ? 'disabled' : '' }}>
                                    <i class="fas fa-upload mr-1"></i> Unggah Dokumen
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- Riwayat Dokumen Pegawai --}}
                <div class="col-12 col-lg-12">
                    <div class="card document-history-card shadow-sm">
                        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                            <h4 class="text-primary mb-0 font-weight-bold">
                                <i class="fas fa-history mr-2"></i> Riwayat Dokumen ({{ strtoupper($pegawai->nama) }})
                            </h4>
                            <span class="badge badge-light border text-muted px-2 py-1 font-weight-normal">
                                {{ $dokumenUploads->count() }} Berkas
                            </span>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover mb-0">
                                    <thead class="thead-light">
                                        <tr>
                                            <th style="width: 50px;">#</th>
                                            <th>Dokumen</th>
                                            <th>Nomor / Tanggal</th>
                                            <th>Uploader</th>
                                            <th>Status</th>
                                            <th class="text-center" style="width: 120px;">File</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($dokumenUploads as $upload)
                                            @php
                                                $uploader = $upload->uploader;
                                                $uploaderRole = $uploader && $uploader->hasRole('kepegawaian') ? 'Kepegawaian' : 'Pegawai';
                                            @endphp
                                            <tr>
                                                <td class="align-middle text-center">{{ $loop->iteration }}</td>
                                                <td class="align-middle">
                                                    <div class="font-weight-bold text-dark">{{ optional($upload->dokumen)->nama_dokumen ?? '-' }}</div>
                                                    <div class="text-muted small"><code>{{ optional($upload->dokumen)->kode_dokumen ?? '-' }}</code></div>
                                                </td>
                                                <td class="align-middle">
                                                    <div class="font-weight-600">{{ $upload->nomor ?: '-' }}</div>
                                                    <div class="text-muted small">
                                                        <i class="far fa-calendar-alt mr-1"></i>{{ optional($upload->tanggal)->format('d-m-Y') ?? '-' }}
                                                    </div>
                                                </td>
                                                <td class="align-middle">
                                                    <div class="font-weight-600">{{ optional($uploader)->name ?? '-' }}</div>
                                                    <span class="badge {{ $uploaderRole === 'Kepegawaian' ? 'badge-primary' : 'badge-info' }} px-2 py-1" style="font-size: 0.72rem;">
                                                        {{ $uploaderRole }}
                                                    </span>
                                                </td>
                                                <td class="align-middle">
                                                    <span class="badge {{ \App\Models\DokumenPegawai::statusBadgeClass($upload->status) }} px-2 py-1">
                                                        {{ \App\Models\DokumenPegawai::statusLabel($upload->status) }}
                                                    </span>
                                                    @if((int) $upload->status === \App\Models\DokumenPegawai::STATUS_REJECTED && $upload->alasan_penolakan)
                                                        <div class="alert alert-danger p-2 mt-2 mb-0 small" style="border-radius: 6px; line-height: 1.3;">
                                                            <i class="fas fa-exclamation-circle mr-1"></i> <strong>Alasan:</strong> {{ $upload->alasan_penolakan }}
                                                        </div>
                                                    @endif
                                                </td>
                                                <td class="align-middle text-center">
                                                    <a href="{{ route('arsip.download', [$upload->file, $pegawaiNipCipher]) }}" class="btn btn-sm btn-outline-primary px-3 shadow-none">
                                                        <i class="fas fa-download mr-1"></i> Unduh
                                                    </a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center text-muted py-5">
                                                    <i class="fas fa-folder-open fa-3x text-muted mb-2 d-block" style="opacity: 0.5;"></i>
                                                    Belum ada dokumen yang diunggah.
                                                </td>
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

    @push('plugins_js')
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    @endpush

    @push('page_js')
        <script>
            $(document).ready(function() {
                if ($.fn.select2) {
                    $('.select2').select2({
                        placeholder: '-- Pilih Jenis Dokumen --',
                        allowClear: true
                    });
                }
            });
        </script>
    @endpush
</x-app-layout>


