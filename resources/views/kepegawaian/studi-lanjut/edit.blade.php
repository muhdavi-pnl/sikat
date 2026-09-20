<x-app-layout>
    @push('plugins_css')
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    @endpush

    @push('page_css')
        <style>
            .select2-container {
                width: 100% !important;
            }
            .form-section-title {
                font-size: 1rem;
                font-weight: 700;
                color: #34395e;
                border-bottom: 2px solid #6777ef;
                padding-bottom: 6px;
                margin-bottom: 18px;
            }
        </style>
    @endpush

    @section('title', $title)
    <x-slot name="header">
        <h1>{{ $title }}</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{ route('dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item active"><a href="{{ route('kepegawaian.studi-lanjut.index') }}">Studi Lanjut</a></div>
            <div class="breadcrumb-item">{{ $title }}</div>
        </div>
    </x-slot>

    <div class="section-body">
        <h2 class="section-title">{{ $title }}</h2>
        <p class="section-lead">Perbarui data pemantauan studi lanjut, perkembangan progres, masa studi, atau berkas legalitas pegawai.</p>

        <div class="card card-warning shadow-sm">
            <div class="card-header">
                <h4><i class="fas fa-edit mr-2 text-warning"></i>Edit Data Studi Lanjut: {{ $studiLanjut->pegawai?->nama_lengkap }}</h4>
            </div>
            <form method="POST" action="{{ route('kepegawaian.studi-lanjut.update', $studiLanjut) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible show fade">
                            <div class="alert-body">
                                <button class="close" data-dismiss="alert"><span>&times;</span></button>
                                <div class="alert-title font-weight-bold"><i class="fas fa-exclamation-triangle mr-1"></i> Terdapat Kesalahan Pengisian:</div>
                                <ul class="mb-0 pl-3">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif

                    {{-- Section 1: Data Pegawai --}}
                    <div class="form-section-title">
                        <i class="fas fa-user mr-1 text-primary"></i> 1. Identitas Pegawai
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-12">
                            <label class="font-weight-bold">Pegawai <span class="text-danger">*</span></label>
                            <select name="pegawai_id" id="pegawai_id" class="form-control" required>
                                <option value="{{ $studiLanjut->pegawai_id }}" selected>
                                    {{ $studiLanjut->pegawai?->nip ?? '-' }} - {{ $studiLanjut->pegawai?->nama_lengkap }} ({{ $studiLanjut->pegawai?->program_studi?->program_studi ?? $studiLanjut->pegawai?->unit_kerja?->unit_kerja ?? '-' }})
                                </option>
                            </select>
                            <small class="text-muted">Untuk mengganti pegawai, ketik minimal 2 karakter pada kolom di atas.</small>
                        </div>
                    </div>

                    {{-- Section 2: Program Studi & Akademik --}}
                    <div class="form-section-title mt-3">
                        <i class="fas fa-graduation-cap mr-1 text-primary"></i> 2. Informasi Program Studi & Institusi
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-3">
                            <label class="font-weight-bold">Jenjang Pendidikan</label>
                            <select name="jenjang" class="form-control">
                                <option value="">-- Pilih Jenjang --</option>
                                @foreach($jenjangOptions as $jVal => $jLbl)
                                    <option value="{{ $jVal }}" {{ old('jenjang', $studiLanjut->jenjang) === $jVal ? 'selected' : '' }}>{{ $jLbl }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-5">
                            <label class="font-weight-bold">Program Studi Tujuan <span class="text-danger">*</span></label>
                            <input type="text" name="program_studi" class="form-control" value="{{ old('program_studi', $studiLanjut->program_studi) }}" required>
                        </div>
                        <div class="form-group col-md-4">
                            <label class="font-weight-bold">Bidang Ilmu <span class="text-danger">*</span></label>
                            <select name="bidang_ilmu" class="form-control font-weight-bold" required>
                                @foreach($bidangIlmuOptions as $bVal => $bLbl)
                                    <option value="{{ $bVal }}" {{ old('bidang_ilmu', $studiLanjut->bidang_ilmu) === $bVal ? 'selected' : '' }}>{{ $bLbl }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-8">
                            <label class="font-weight-bold">Perguruan Tinggi / Institusi Tujuan <span class="text-danger">*</span></label>
                            <input type="text" name="nama_institusi" class="form-control" value="{{ old('nama_institusi', $studiLanjut->nama_institusi) }}" required>
                        </div>
                        <div class="form-group col-md-4">
                            <label class="font-weight-bold">Negara</label>
                            <input type="text" name="negara" class="form-control" value="{{ old('negara', $studiLanjut->negara) }}">
                        </div>
                    </div>

                    {{-- Section 3: Pembiayaan & Penugasan --}}
                    <div class="form-section-title mt-3">
                        <i class="fas fa-hand-holding-usd mr-1 text-primary"></i> 3. Pembiayaan & Penugasan
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label class="font-weight-bold">Jenis Pembiayaan <span class="text-danger">*</span></label>
                            <select name="jenis_pembiayaan" id="jenis_pembiayaan" class="form-control" required onchange="toggleBeasiswaField()">
                                @foreach($jenisPembiayaanOptions as $pVal => $pLbl)
                                    <option value="{{ $pVal }}" {{ old('jenis_pembiayaan', $studiLanjut->jenis_pembiayaan) === $pVal ? 'selected' : '' }}>{{ $pLbl }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-4" id="nama_beasiswa_group">
                            <label class="font-weight-bold">Nama Beasiswa / Sumber Pendanaan</label>
                            <input type="text" name="nama_beasiswa" class="form-control" placeholder="Contoh: LPDP, BPI Kemendikbud, Dikti, BU, dll." value="{{ old('nama_beasiswa', $studiLanjut->nama_beasiswa) }}">
                        </div>
                        <div class="form-group col-md-4">
                            <label class="font-weight-bold">Jenis Tugas Belajar <span class="text-danger">*</span></label>
                            <select name="jenis_tugas" class="form-control" required>
                                @foreach($jenisTugasOptions as $tVal => $tLbl)
                                    <option value="{{ $tVal }}" {{ old('jenis_tugas', $studiLanjut->jenis_tugas) === $tVal ? 'selected' : '' }}>{{ $tLbl }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Section 4: Progres & Periode --}}
                    <div class="form-section-title mt-3">
                        <i class="fas fa-tasks mr-1 text-primary"></i> 4. Progres Studi & Periode Waktu
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-3">
                            <label class="font-weight-bold">Progres Studi <span class="text-danger">*</span></label>
                            <select name="progres" class="form-control font-weight-bold" required>
                                @foreach($progresOptions as $prVal => $prLbl)
                                    <option value="{{ $prVal }}" {{ old('progres', $studiLanjut->progres) === $prVal ? 'selected' : '' }}>{{ $prLbl }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-3">
                            <label class="font-weight-bold">Tanggal Mulai Studi</label>
                            <input type="date" name="tanggal_mulai" class="form-control" value="{{ old('tanggal_mulai', $studiLanjut->tanggal_mulai ? $studiLanjut->tanggal_mulai->format('Y-m-d') : '') }}">
                        </div>
                        <div class="form-group col-md-3">
                            <label class="font-weight-bold">Target Tanggal Selesai</label>
                            <input type="date" name="target_selesai" class="form-control" value="{{ old('target_selesai', $studiLanjut->target_selesai ? $studiLanjut->target_selesai->format('Y-m-d') : '') }}">
                        </div>
                        <div class="form-group col-md-3">
                            <label class="font-weight-bold">Tanggal Selesai Riil (Jika Lulus)</label>
                            <input type="date" name="tanggal_selesai" class="form-control" value="{{ old('tanggal_selesai', $studiLanjut->tanggal_selesai ? $studiLanjut->tanggal_selesai->format('Y-m-d') : '') }}">
                        </div>
                    </div>

                    {{-- Section 5: Dokumen SK & Keterangan --}}
                    <div class="form-section-title mt-3">
                        <i class="fas fa-file-contract mr-1 text-primary"></i> 5. Dokumen Legalitas & Catatan
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label class="font-weight-bold">Nomor SK</label>
                            <input type="text" name="nomor_sk" class="form-control" value="{{ old('nomor_sk', $studiLanjut->nomor_sk) }}">
                        </div>
                        <div class="form-group col-md-4">
                            <label class="font-weight-bold">Tanggal SK</label>
                            <input type="date" name="tanggal_sk" class="form-control" value="{{ old('tanggal_sk', $studiLanjut->tanggal_sk ? $studiLanjut->tanggal_sk->format('Y-m-d') : '') }}">
                        </div>
                        <div class="form-group col-md-4">
                            <label class="font-weight-bold">Unggah Dokumen SK Baru (Opsional)</label>
                            <input type="file" name="dokumen_sk" class="form-control-file border p-1 rounded" accept=".pdf,.jpg,.jpeg,.png">
                            @if($studiLanjut->dokumen_sk)
                                <div class="mt-2 small">
                                    <a href="{{ asset($studiLanjut->dokumen_sk) }}" target="_blank" class="text-primary font-weight-bold">
                                        <i class="fas fa-paperclip mr-1"></i> Lihat Dokumen SK Saat Ini
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-12">
                            <label class="font-weight-bold">Keterangan / Catatan Perkembangan</label>
                            <textarea name="keterangan" class="form-control" rows="3">{{ old('keterangan', $studiLanjut->keterangan) }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="card-footer bg-whitesmoke text-right">
                    <a href="{{ route('kepegawaian.studi-lanjut.show', $studiLanjut) }}" class="btn btn-secondary mr-2"><i class="fas fa-arrow-left mr-1"></i> Batal</a>
                    <button type="submit" class="btn btn-warning"><i class="fas fa-save mr-1"></i> Perbarui Data Studi Lanjut</button>
                </div>
            </form>
        </div>
    </div>

    @push('plugins_js')
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    @endpush

    @push('page_js')
        <script>
            function toggleBeasiswaField() {
                var val = $('#jenis_pembiayaan').val();
                if (val === 'beasiswa') {
                    $('#nama_beasiswa_group').show();
                } else {
                    $('#nama_beasiswa_group').hide();
                }
            }

            $(function () {
                toggleBeasiswaField();

                $('#pegawai_id').select2({
                    width: '100%',
                    placeholder: '-- Ketik NIP atau Nama Pegawai --',
                    allowClear: true,
                    minimumInputLength: 2,
                    language: {
                        inputTooShort: function () {
                            return 'Ketik minimal 2 karakter untuk mencari';
                        }
                    },
                    ajax: {
                        url: '{{ route('kepegawaian.studi-lanjut.options.pegawais') }}',
                        dataType: 'json',
                        delay: 250,
                        data: function (params) {
                            return {
                                q: params.term || '',
                                page: params.page || 1,
                            };
                        },
                        processResults: function (data) {
                            return data;
                        }
                    }
                });
            });
        </script>
    @endpush
</x-app-layout>
