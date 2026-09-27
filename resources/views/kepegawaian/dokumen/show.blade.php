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
            .modal-preview-body {
                height: 75vh;
                overflow: auto;
                background: #f8fafc;
            }
        </style>
    @endpush

    @section('title', $title)
    <x-slot name="header">
        <h1>{{ $title }}</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{ route('dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item active"><a href="{{ route('kepegawaian.dokumen.index') }}">Review Dokumen</a></div>
            <div class="breadcrumb-item">{{ strtoupper($pegawai->nama) }}</div>
        </div>
    </x-slot>

    <div class="section-body">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap" style="gap: 0.5rem;">
            <div>
                <h2 class="section-title my-0">{{ $title }}</h2>
                <p class="section-lead mb-0">Kelola unggah, validasi, dan riwayat arsip dokumen untuk pegawai <strong>{{ strtoupper($pegawai->nama) }}</strong>.</p>
            </div>
            <div>
                <a href="{{ route('kepegawaian.dokumen.index') }}" class="btn btn-outline-dark">
                    <i class="fas fa-arrow-left mr-1"></i> Kembali ke Daftar Pegawai
                </a>
            </div>
        </div>

        {{-- Card 1: Skor Kelengkapan Data --}}
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

        {{-- Card 2: Form Unggah Dokumen Pegawai --}}
        <div class="card document-upload-card shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <h4 class="text-primary mb-0 font-weight-bold">
                    <i class="fas fa-cloud-upload-alt mr-2"></i> Unggah Dokumen Pegawai ({{ strtoupper($pegawai->nama) }})
                </h4>
            </div>
            <form action="{{ route('kepegawaian.dokumen.store', $pegawai) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger mb-3">
                            <ul class="mb-0 pl-3">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if($dokumenOptions->isEmpty())
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-circle mr-1"></i> Master jenis dokumen belum tersedia. Tambahkan data dokumen terlebih dahulu.
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
                        <div class="form-group col-md-4">
                            <label class="font-weight-bold"><i class="fas fa-calendar-alt text-warning mr-1"></i> Tanggal Dokumen</label>
                            <input type="date" name="tanggal" class="form-control" value="{{ old('tanggal') }}">
                        </div>

                        <div class="form-group col-md-4">
                            <label class="font-weight-bold"><i class="fas fa-shield-alt text-success mr-1"></i> Status Dokumen <span class="text-danger">*</span></label>
                            <select name="status" id="show-upload-status" class="form-control font-weight-600" required>
                                <option value="1" {{ old('status', '1') === '1' ? 'selected' : '' }}>&#10004; Valid</option>
                                <option value="0" {{ old('status') === '0' ? 'selected' : '' }}>&#9203; Menunggu Verifikasi</option>
                                <option value="2" {{ old('status') === '2' ? 'selected' : '' }}>&#10006; Ditolak</option>
                            </select>
                        </div>

                        <div class="form-group col-md-4">
                            <label class="font-weight-bold"><i class="fas fa-paperclip text-info mr-1"></i> File Dokumen <span class="text-danger">*</span></label>
                            <input type="file" name="file" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required {{ $dokumenOptions->isEmpty() ? 'disabled' : '' }}>
                            <small class="text-muted d-block mt-1">Format: PDF/JPG/PNG (Maks 2MB).</small>
                        </div>
                    </div>

                    <div class="form-group" id="show-alasan-penolakan-container" style="{{ old('status') === '2' ? '' : 'display: none;' }}">
                        <label class="font-weight-bold text-danger"><i class="fas fa-exclamation-circle mr-1"></i> Alasan Penolakan <span class="text-danger">*</span></label>
                        <textarea name="alasan_penolakan" id="show-alasan-penolakan" rows="2" class="form-control border-danger" placeholder="Tuliskan alasan penolakan dokumen...">{{ old('alasan_penolakan') }}</textarea>
                        <small class="text-muted">Wajib diisi jika status dokumen adalah Ditolak.</small>
                    </div>

                    <div class="form-group mb-0">
                        <label class="font-weight-bold"><i class="fas fa-comment-dots text-secondary mr-1"></i> Keterangan / Catatan Tambahan</label>
                        <textarea name="keterangan" rows="2" class="form-control" placeholder="Tuliskan catatan internal jika ada...">{{ old('keterangan') }}</textarea>
                    </div>
                </div>
                <div class="card-footer bg-light text-right py-3">
                    <button type="submit" class="btn btn-primary px-4 font-weight-bold" {{ $dokumenOptions->isEmpty() ? 'disabled' : '' }}>
                        <i class="fas fa-save mr-1"></i> Simpan Dokumen
                    </button>
                </div>
            </form>
        </div>

        {{-- Card 3: Riwayat Dokumen Pegawai --}}
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
                                <th class="text-center" style="width: 170px;">Aksi</th>
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
                                        <div class="font-weight-bold text-dark">{{ optional($upload->dokumen)->nama_dokumen ?: '-' }}</div>
                                        <div class="text-muted small"><code>{{ optional($upload->dokumen)->kode_dokumen ?: '-' }}</code></div>
                                    </td>
                                    <td class="align-middle">
                                        <div class="font-weight-600">{{ $upload->nomor ?: '-' }}</div>
                                        <div class="text-muted small">
                                            <i class="far fa-calendar-alt mr-1"></i>{{ optional($upload->tanggal)->format('d-m-Y') ?: '-' }}
                                        </div>
                                    </td>
                                    <td class="align-middle">
                                        <div class="font-weight-600">{{ optional($uploader)->name ?: '-' }}</div>
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
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-sm btn-outline-info px-2 btn-preview-modal"
                                                    data-url="{{ route('arsip.preview', [$upload->file, $pegawaiNipCipher]) }}"
                                                    data-download="{{ route('arsip.download', [$upload->file, $pegawaiNipCipher]) }}"
                                                    data-title="{{ optional($upload->dokumen)->nama_dokumen }}"
                                                    data-ext="{{ strtolower(pathinfo($upload->file, PATHINFO_EXTENSION)) }}"
                                                    title="Pratinjau Dokumen">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <a href="{{ route('arsip.download', [$upload->file, $pegawaiNipCipher]) }}" class="btn btn-sm btn-outline-secondary px-2" title="Unduh Berkas">
                                                <i class="fas fa-download"></i>
                                            </a>
                                            <a href="{{ route('kepegawaian.dokumen.edit', [$pegawai, $upload->dokumen_id]) }}" class="btn btn-sm btn-outline-primary px-2 font-weight-bold" title="Review / Verifikasi">
                                                <i class="fas fa-file-signature"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-5">
                                        <i class="fas fa-folder-open fa-3x text-muted mb-2 d-block" style="opacity: 0.5;"></i>
                                        Belum ada dokumen untuk pegawai ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Pratinjau Dokumen (Clean Header & Body without Overlap) --}}
    <div class="modal fade" id="modal-preview-doc" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered" role="document" style="max-width: 92vw; margin: 1.5rem auto;">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="modal-title font-weight-bold text-primary mb-0" id="modal-doc-title">Pratinjau Dokumen</h5>
                        <small class="text-muted">{{ strtoupper($pegawai->nama) }} (NIP: {{ $pegawai->nip }})</small>
                    </div>
                    <div class="d-flex align-items-center" style="gap: 0.5rem;">
                        <a id="modal-doc-newtab" href="#" target="_blank" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-external-link-alt mr-1"></i> Tab Baru
                        </a>
                        <a id="modal-doc-download" href="#" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-download mr-1"></i> Unduh
                        </a>
                        <button type="button" class="btn btn-sm btn-light border ml-1 text-dark" data-dismiss="modal" aria-label="Close" style="padding: 0.35rem 0.65rem;">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                <div class="modal-body p-0 modal-preview-body" id="modal-doc-body">
                    {{-- Konten preview diisi via JS --}}
                </div>
                <div class="modal-footer py-2 px-4 bg-light border-top d-flex justify-content-end">
                    <button type="button" class="btn btn-secondary px-3" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    @push('plugins_js')
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    @endpush

    @push('page_js')
    <script>
        $(function () {
            if ($.fn.select2) {
                $('.select2').select2({
                    placeholder: '-- Pilih Jenis Dokumen --',
                    allowClear: true
                });
            }

            const $statusSelect = $('#show-upload-status');
            const $alasanContainer = $('#show-alasan-penolakan-container');
            const $alasanTextarea = $('#show-alasan-penolakan');

            function toggleShowAlasan() {
                const val = String($statusSelect.val()).trim();
                if (val === '2') {
                    $alasanContainer.slideDown(200);
                    $alasanTextarea.attr('required', 'required');
                } else {
                    $alasanContainer.slideUp(200);
                    $alasanTextarea.removeAttr('required');
                }
            }

            $statusSelect.on('change input select2:select', toggleShowAlasan);
            toggleShowAlasan();

            // Handle preview modal
            $('.btn-preview-modal').on('click', function() {
                const url = $(this).data('url');
                const download = $(this).data('download');
                const title = $(this).data('title');
                const ext = String($(this).data('ext')).toLowerCase();

                $('#modal-doc-title').text(title || 'Pratinjau Dokumen');
                $('#modal-doc-newtab').attr('href', url);
                $('#modal-doc-download').attr('href', download);

                let content = '';
                if (ext === 'pdf') {
                    content = `<iframe src="${url}#toolbar=1" style="width: 100%; height: 100%; border: none;"></iframe>`;
                } else if (['jpg', 'jpeg', 'png', 'webp', 'gif'].includes(ext)) {
                    content = `<div class="d-flex align-items-center justify-content-center p-3" style="min-height: 100%;"><img src="${url}" class="img-fluid rounded shadow-sm" style="max-height: 70vh; object-fit: contain;"></div>`;
                } else {
                    content = `<div class="text-center p-5"><i class="fas fa-file-alt fa-3x text-muted mb-3 d-block"></i><p>Format file .${ext} tidak dapat dipratinjau langsung.</p><a href="${download}" class="btn btn-primary"><i class="fas fa-download mr-1"></i> Unduh File</a></div>`;
                }

                $('#modal-doc-body').html(content);
                $('#modal-preview-doc').modal('show');
            });
        });
    </script>
    @endpush
</x-app-layout>
