<x-app-layout>
    @push('page_css')
        <style>
            .review-card-sticky {
                position: -webkit-sticky;
                position: sticky;
                top: 20px;
            }
            .preview-container {
                background: #f8fafc;
                border: 1px solid #e2e8f0;
                border-radius: 8px;
                overflow: hidden;
            }
            .preview-iframe {
                width: 100%;
                height: 750px;
                border: none;
                display: block;
            }
            .preview-image-wrapper {
                min-height: 480px;
                max-height: 750px;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 1.25rem;
                overflow: auto;
                background-color: #1a202c10;
            }
            .preview-image {
                max-width: 100%;
                max-height: 700px;
                object-fit: contain;
                border-radius: 6px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            }
        </style>
    @endpush

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

    @php
        $fileExt = strtolower(pathinfo($dokumenUpload->file, PATHINFO_EXTENSION));
        $isPdf = $fileExt === 'pdf';
        $isImage = in_array($fileExt, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg']);
        $previewUrl = route('arsip.preview', [$dokumenUpload->file, $pegawaiNipCipher]);
        $downloadUrl = route('arsip.download', [$dokumenUpload->file, $pegawaiNipCipher]);
    @endphp

    <div class="section-body">
        <h2 class="section-title">{{ $title }}</h2>
        <p class="section-lead">Tinjau berkas yang diunggah pegawai dan perbarui status verifikasi / validasi.</p>

        <div class="row">
            {{-- Kolom Kiri: Form Review & Status Validasi --}}
            <div class="col-12 col-lg-5 col-xl-5 mb-4">
                <div class="card shadow-sm border-0 review-card-sticky">
                    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="text-primary mb-0 font-weight-bold">
                                <i class="fas fa-clipboard-check mr-2"></i> Form Review
                            </h4>
                            <small class="text-muted">{{ strtoupper($pegawai->nama) }} (NIP: {{ $pegawai->nip }})</small>
                        </div>
                        <div class="d-flex align-items-center" style="gap: 0.5rem;">
                            <span class="badge {{ \App\Models\DokumenPegawai::statusBadgeClass($dokumenUpload->status) }} px-2 py-1">
                                {{ \App\Models\DokumenPegawai::statusLabel($dokumenUpload->status) }}
                            </span>
                            <a href="{{ route('kepegawaian.dokumen.show', $pegawai) }}" class="btn btn-sm btn-outline-dark" title="Kembali ke Riwayat">
                                <i class="fas fa-arrow-left"></i>
                            </a>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('kepegawaian.dokumen.update', [$pegawai, $dokumen]) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
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

                            <div class="form-group mb-3">
                                <label class="font-weight-bold text-dark mb-1">Jenis Dokumen</label>
                                <div class="p-2 bg-light rounded border">
                                    <span class="font-weight-bold">{{ optional($dokumenUpload->dokumen)->nama_dokumen }}</span>
                                    <span class="badge badge-info ml-1">{{ optional($dokumenUpload->dokumen)->kode_dokumen }}</span>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-6 mb-3">
                                    <label class="font-weight-bold mb-1">Nomor Dokumen</label>
                                    <input type="text" name="nomor" class="form-control" placeholder="Nomor surat/SK..." value="{{ old('nomor', $dokumenUpload->nomor) }}">
                                </div>
                                <div class="form-group col-md-6 mb-3">
                                    <label class="font-weight-bold mb-1">Tanggal Dokumen</label>
                                    <input type="date" name="tanggal" class="form-control" value="{{ old('tanggal', optional($dokumenUpload->tanggal)->format('Y-m-d')) }}">
                                </div>
                            </div>

                            <div class="form-group mb-3">
                                <label class="font-weight-bold text-dark mb-1">
                                    Status Dokumen <span class="text-danger">*</span>
                                </label>
                                <select name="status" id="review-status" class="form-control font-weight-600" required>
                                    <option value="1" {{ (string) old('status', (int) $dokumenUpload->status) === '1' ? 'selected' : '' }}>
                                        &#10004; Valid (Disetujui)
                                    </option>
                                    <option value="0" {{ (string) old('status', (int) $dokumenUpload->status) === '0' ? 'selected' : '' }}>
                                        &#9203; Menunggu Verifikasi
                                    </option>
                                    <option value="2" {{ (string) old('status', (int) $dokumenUpload->status) === '2' ? 'selected' : '' }}>
                                        &#10006; Ditolak
                                    </option>
                                </select>
                            </div>

                            <div class="form-group mb-3" id="alasan-penolakan-container" style="{{ (string) old('status', (int) $dokumenUpload->status) === '2' ? '' : 'display: none;' }}">
                                <label class="font-weight-bold text-danger mb-1">
                                    <i class="fas fa-exclamation-circle mr-1"></i> Alasan Penolakan <span class="text-danger">*</span>
                                </label>
                                <textarea name="alasan_penolakan" id="alasan_penolakan" rows="3" class="form-control border-danger" placeholder="Tuliskan alasan penolakan dokumen agar pegawai dapat mengetahui perbaikan yang dibutuhkan...">{{ old('alasan_penolakan', $dokumenUpload->alasan_penolakan) }}</textarea>
                                <small class="text-muted">Wajib diisi jika status dokumen adalah Ditolak.</small>
                            </div>

                            <div class="form-group mb-3">
                                <label class="font-weight-bold mb-1">Ganti File Baru (Opsional)</label>
                                <input type="file" name="file" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                                <small class="text-muted">Kosongkan jika tidak ingin mengganti file yang telah diunggah.</small>
                            </div>

                            <div class="form-group mb-3">
                                <label class="font-weight-bold mb-1">Keterangan / Catatan Tambahan</label>
                                <textarea name="keterangan" rows="3" class="form-control" placeholder="Catatan internal verifikator...">{{ old('keterangan', $dokumenUpload->keterangan) }}</textarea>
                            </div>

                            <div class="p-2 mb-3 bg-light rounded text-muted small border">
                                <i class="fas fa-user-edit mr-1"></i> Uploader: <strong>{{ optional($dokumenUpload->uploader)->name ?: '-' }}</strong>
                                <span class="mx-1">&bull;</span>
                                <i class="fas fa-clock mr-1"></i> {{ optional($dokumenUpload->updated_at)->format('d M Y H:i') ?: '-' }}
                            </div>
                        </div>

                        <div class="card-footer bg-light border-top py-3 d-flex justify-content-between align-items-center">
                            <a href="{{ $downloadUrl }}" class="btn btn-outline-secondary">
                                <i class="fas fa-download mr-1"></i> Unduh
                            </a>
                            <button type="submit" class="btn btn-primary px-4 font-weight-bold">
                                <i class="fas fa-save mr-1"></i> Simpan Hasil Review
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Kolom Kanan: Pratinjau Dokumen Langsung --}}
            <div class="col-12 col-lg-7 col-xl-7 mb-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center flex-wrap" style="gap: 0.5rem;">
                        <div class="d-flex align-items-center" style="gap: 0.5rem;">
                            <h4 class="text-primary mb-0 font-weight-bold">
                                <i class="fas fa-file-alt mr-1"></i> Pratinjau Dokumen
                            </h4>
                            <span class="badge badge-secondary text-uppercase">{{ $fileExt ?: 'File' }}</span>
                        </div>
                        <div class="card-header-action" style="gap: 0.35rem; display: flex;">
                            <a href="{{ $previewUrl }}" target="_blank" class="btn btn-sm btn-outline-primary" title="Buka di Tab Baru">
                                <i class="fas fa-external-link-alt mr-1"></i> Tab Baru
                            </a>
                            <a href="{{ $downloadUrl }}" class="btn btn-sm btn-outline-secondary" title="Unduh File">
                                <i class="fas fa-download mr-1"></i> Unduh
                            </a>
                        </div>
                    </div>

                    <div class="card-body p-3">
                        <div class="preview-container">
                            @if($isPdf)
                                <iframe src="{{ $previewUrl }}#toolbar=1" class="preview-iframe" title="Pratinjau PDF"></iframe>
                            @elseif($isImage)
                                <div class="preview-image-wrapper">
                                    <img src="{{ $previewUrl }}" alt="{{ optional($dokumenUpload->dokumen)->nama_dokumen }}" class="preview-image">
                                </div>
                            @else
                                <div class="text-center p-5">
                                    <i class="fas fa-file-invoice fa-4x text-muted mb-3 d-block"></i>
                                    <h5 class="text-dark">Format berkas tidak dapat dipratinjau langsung</h5>
                                    <p class="text-muted">Berkas berformat <strong>.{{ $fileExt }}</strong> dapat diunduh untuk diperiksa secara mandiri.</p>
                                    <a href="{{ $downloadUrl }}" class="btn btn-primary mt-2">
                                        <i class="fas fa-download mr-1"></i> Unduh Dokumen ({{ $dokumenUpload->file }})
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('page_js')
    <script>
        $(function () {
            const $statusSelect = $('#review-status');
            const $alasanContainer = $('#alasan-penolakan-container');
            const $alasanTextarea = $('#alasan_penolakan');

            function toggleAlasan() {
                const val = String($statusSelect.val()).trim();
                if (val === '2') {
                    $alasanContainer.slideDown(200);
                    $alasanTextarea.attr('required', 'required');
                } else {
                    $alasanContainer.slideUp(200);
                    $alasanTextarea.removeAttr('required');
                }
            }

            $statusSelect.on('change input select2:select', toggleAlasan);
            toggleAlasan();
        });
    </script>
    @endpush
</x-app-layout>
