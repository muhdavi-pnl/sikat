<x-app-layout>
    @section('title', $title)

    @push('plugins_css')
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
        <style>
            .select2-container {
                width: 100% !important;
            }
            .select2-container--default .select2-selection--single {
                height: 42px !important;
                border-color: #e4e6fc !important;
                padding: 6px 12px;
                border-radius: 4px;
            }
            .select2-container--default .select2-selection--single .select2-selection__rendered {
                line-height: 28px !important;
                color: #495057;
            }
            .select2-container--default .select2-selection--single .select2-selection__arrow {
                height: 40px !important;
            }
            .select2-container--default .select2-selection--single .select2-selection__placeholder {
                color: #a0aec0;
            }
        </style>
    @endpush

    <x-slot name="header">
        <h1>{{ $title }}</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{ route('dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item"><a href="{{ route('peta-jabatan.index') }}">Peta Jabatan</a></div>
            <div class="breadcrumb-item"><a href="{{ route('peta-jabatan.manage.index', ['slug' => 'jabatan']) }}">Kelola Jabatan</a></div>
            <div class="breadcrumb-item">{{ $isEdit ? 'Edit' : 'Tambah' }}</div>
        </div>
    </x-slot>

    <div class="section-body">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
            <div>
                <h2 class="section-title mt-0">{{ $title }}</h2>
                <p class="section-lead mb-0">Lengkapi formulir di bawah ini untuk {{ $isEdit ? 'memperbarui' : 'menambahkan' }} data jabatan.</p>
            </div>
            <div class="mt-2 mt-md-0 text-right">
                <a href="{{ route('peta-jabatan.manage.index', ['slug' => 'jabatan']) }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali ke Daftar
                </a>
            </div>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible show fade">
                <div class="alert-body">
                    <button class="close" data-dismiss="alert"><span>&times;</span></button>
                    <div class="alert-title font-weight-bold"><i class="fas fa-exclamation-triangle mr-1"></i> Terjadi Kesalahan Input</div>
                    <ul class="mb-0 pl-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <form action="{{ $action }}" method="POST">
            @csrf
            @if ($method === 'PUT')
                @method('PUT')
            @endif

            <div class="row">
                {{-- Bagian Kiri: Informasi Utama & Hierarki --}}
                <div class="col-lg-6 col-12">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h4><i class="fas fa-info-circle text-primary mr-2"></i> 1. Informasi Utama Jabatan</h4>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label for="jabatan">Nama Jabatan <span class="text-danger">*</span></label>
                                <input type="text" name="jabatan" id="jabatan" class="form-control @error('jabatan') is-invalid @enderror" value="{{ old('jabatan', $jabatan->jabatan) }}" placeholder="Contoh: Ketua Jurusan TIK / Dosen / Staf Kepegawaian" required>
                                @error('jabatan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="unit_kerja_id">Unit Kerja <span class="text-danger">*</span></label>
                                        <select name="unit_kerja_id" id="unit_kerja_id" class="form-control select2 @error('unit_kerja_id') is-invalid @enderror" data-placeholder="-- Pilih Unit Kerja --" required>
                                            <option value="">-- Pilih Unit Kerja --</option>
                                            @foreach ($unitKerjas as $uk)
                                                <option value="{{ $uk->id }}" data-kode="{{ $uk->kode }}" @selected(old('unit_kerja_id', $jabatan->unit_kerja_id) == $uk->id)>{{ $uk->unit_kerja }}</option>
                                            @endforeach
                                        </select>
                                        @error('unit_kerja_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="jenis_jabatan_id">Jenis Jabatan <span class="text-danger">*</span></label>
                                        <select name="jenis_jabatan_id" id="jenis_jabatan_id" class="form-control select2 @error('jenis_jabatan_id') is-invalid @enderror" data-placeholder="-- Pilih Jenis Jabatan --" required>
                                            <option value="">-- Pilih Jenis Jabatan --</option>
                                            @foreach ($jenisJabatans as $jj)
                                                <option value="{{ $jj->id }}" data-kode="{{ $jj->kode }}" @selected(old('jenis_jabatan_id', $jabatan->jenis_jabatan_id) == $jj->id)>{{ $jj->jenis_jabatan }}</option>
                                            @endforeach
                                        </select>
                                        @error('jenis_jabatan_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <label for="kode_jabatan" class="mb-0">Kode Jabatan <span class="text-danger">*</span></label>
                                            <button type="button" class="btn btn-xs btn-outline-primary" id="btn-auto-generate-kode" title="Generate otomatis kode berdasarkan unit kerja dan jenis jabatan">
                                                <i class="fas fa-magic mr-1"></i> Auto-generate
                                            </button>
                                        </div>
                                        <input type="text" name="kode_jabatan" id="kode_jabatan" class="form-control @error('kode_jabatan') is-invalid @enderror" value="{{ old('kode_jabatan', $jabatan->kode_jabatan) }}" placeholder="Contoh: JTIK-JS01 / JTIK-JP01 / JTIK-JF01" required>
                                        <small class="form-text text-muted">Format: [Kode Unit]-[Kode Jenis][Nomor Urut] (Contoh: JTIK-JS01, JTIK-JP01, JTIK-JF01)</small>
                                        @error('kode_jabatan')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="status_jabatan">Status Jabatan</label>
                                        <select name="status_jabatan" id="status_jabatan" class="form-control select2 @error('status_jabatan') is-invalid @enderror" data-placeholder="-- Pilih Status Jabatan --">
                                            <option value="Aktif" @selected(old('status_jabatan', $jabatan->status_jabatan ?? 'Aktif') === 'Aktif')>Aktif</option>
                                            <option value="Definitif" @selected(old('status_jabatan', $jabatan->status_jabatan) === 'Definitif')>Definitif</option>
                                            <option value="PLT" @selected(old('status_jabatan', $jabatan->status_jabatan) === 'PLT')>PLT</option>
                                            <option value="PLH" @selected(old('status_jabatan', $jabatan->status_jabatan) === 'PLH')>PLH</option>
                                            <option value="Lowong" @selected(old('status_jabatan', $jabatan->status_jabatan) === 'Lowong')>Lowong</option>
                                        </select>
                                        @error('status_jabatan')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            @php
                                $selectedJenjang = old('jenjang_jabatan', $jabatan->jenjang_jabatan);
                                $standardJenjangs = [
                                    'Jabatan Fungsional Keahlian' => [
                                        'Ahli Pertama',
                                        'Ahli Muda',
                                        'Ahli Madya',
                                        'Ahli Utama',
                                    ],
                                    'Jabatan Fungsional Keterampilan' => [
                                        'Pemula',
                                        'Terampil',
                                        'Mahir',
                                        'Penyelia',
                                    ],
                                    'Jabatan Struktural / Manajerial' => [
                                        'Pimpinan Tinggi Utama',
                                        'Pimpinan Tinggi Madya',
                                        'Pimpinan Tinggi Pratama',
                                        'Administrator',
                                        'Pengawas',
                                    ],
                                    'Jabatan Pelaksana' => [
                                        'Pelaksana'
                                    ],
                                ];
                                $allFlattened = collect($standardJenjangs)->flatten()->all();
                                $hasCustom = $selectedJenjang && !in_array($selectedJenjang, $allFlattened);
                            @endphp

                            <div class="form-group">
                                <label for="jenjang_jabatan">Jenjang Jabatan</label>
                                <select name="jenjang_jabatan" id="jenjang_jabatan" class="form-control select2 @error('jenjang_jabatan') is-invalid @enderror" data-placeholder="-- Pilih / Ketik Jenjang Jabatan --" data-tags="true">
                                    <option value="">-- Pilih Jenjang Jabatan --</option>
                                    @if ($hasCustom)
                                        <option value="{{ $selectedJenjang }}" selected>{{ $selectedJenjang }}</option>
                                    @endif
                                    @foreach ($standardJenjangs as $group => $items)
                                        <optgroup label="{{ $group }}">
                                            @foreach ($items as $item)
                                                <option value="{{ $item }}" @selected($selectedJenjang === $item)>{{ $item }}</option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                                @error('jenjang_jabatan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="card card-info">
                        <div class="card-header">
                            <h4><i class="fas fa-sitemap text-info mr-2"></i> 2. Hierarki &amp; Formasi</h4>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label for="atasan_langsung_id">Atasan Langsung (Hierarki Organisasi)</label>
                                <select name="atasan_langsung_id" id="atasan_langsung_id" class="form-control select2 @error('atasan_langsung_id') is-invalid @enderror" data-placeholder="-- Pilih Jabatan Struktural (Atasan) --">
                                    <option value="">-- Tanpa Atasan Langsung (Top Level) --</option>
                                    @foreach ($atasanOptions as $opt)
                                        <option value="{{ $opt->id }}" @selected(old('atasan_langsung_id', $jabatan->atasan_langsung_id) == $opt->id)>
                                            {{ $opt->jabatan }} {{ $opt->unit_kerja ? '(' . $opt->unit_kerja->unit_kerja . ')' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="form-text text-muted"><i class="fas fa-info-circle mr-1"></i>Pilih jabatan struktural yang berwenang sebagai atasan langsung.</small>
                                @error('atasan_langsung_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="kebutuhan_pegawai">Formasi / Kebutuhan Pegawai</label>
                                        <input type="number" name="kebutuhan_pegawai" id="kebutuhan_pegawai" min="0" class="form-control @error('kebutuhan_pegawai') is-invalid @enderror" value="{{ old('kebutuhan_pegawai', $jabatan->kebutuhan_pegawai ?? 1) }}" placeholder="0">
                                        <small class="form-text text-muted">Jumlah formasi pegawai yang dibutuhkan.</small>
                                        @error('kebutuhan_pegawai')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="kelas_jabatan">Kelas Jabatan</label>
                                        <input type="number" name="kelas_jabatan" id="kelas_jabatan" min="1" max="20" class="form-control @error('kelas_jabatan') is-invalid @enderror" value="{{ old('kelas_jabatan', $jabatan->kelas_jabatan) }}" placeholder="Contoh: 9, 12, 14">
                                        @error('kelas_jabatan')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="pangkat_minimal">Pangkat Minimal</label>
                                        <select name="pangkat_minimal" id="pangkat_minimal" class="form-control select2 @error('pangkat_minimal') is-invalid @enderror" data-placeholder="-- Pilih Pangkat Minimal --">
                                            <option value="">-- Pilih Pangkat Minimal --</option>
                                            @foreach ($pangkats ?? [] as $pkt)
                                                <option value="{{ $pkt->id }}" @selected(old('pangkat_minimal', $jabatan->pangkat_minimal) == $pkt->id)>
                                                    {{ $pkt->pangkat }} ({{ $pkt->golongan_ruang }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('pangkat_minimal')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="beban_kerja">Beban Kerja</label>
                                        <input type="text" name="beban_kerja" id="beban_kerja" class="form-control @error('beban_kerja') is-invalid @enderror" value="{{ old('beban_kerja', $jabatan->beban_kerja) }}" placeholder="Contoh: 1250 Jam/Tahun">
                                        @error('beban_kerja')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Bagian Kanan: Kualifikasi & Tugas --}}
                <div class="col-lg-6 col-12">
                    <div class="card card-warning">
                        <div class="card-header">
                            <h4><i class="fas fa-graduation-cap text-warning mr-2"></i> 3. Kualifikasi &amp; Persyaratan</h4>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label for="pendidikan_minimal">Pendidikan Minimal</label>
                                <input type="text" name="pendidikan_minimal" id="pendidikan_minimal" class="form-control @error('pendidikan_minimal') is-invalid @enderror" value="{{ old('pendidikan_minimal', $jabatan->pendidikan_minimal) }}" placeholder="Contoh: S-1 Komputer / S-2 Teknik Informatika / D-3">
                                @error('pendidikan_minimal')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="kompetensi">Kompetensi yang Dibutuhkan</label>
                                <textarea name="kompetensi" id="kompetensi" rows="3" class="form-control @error('kompetensi') is-invalid @enderror" placeholder="Sebutkan kompetensi teknis atau manajerial yang diperlukan">{{ old('kompetensi', $jabatan->kompetensi) }}</textarea>
                                @error('kompetensi')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="persyaratan_jabatan">Persyaratan Khusus Jabatan</label>
                                <textarea name="persyaratan_jabatan" id="persyaratan_jabatan" rows="3" class="form-control @error('persyaratan_jabatan') is-invalid @enderror" placeholder="Persyaratan sertifikasi, diklat, atau pengalaman kerja">{{ old('persyaratan_jabatan', $jabatan->persyaratan_jabatan) }}</textarea>
                                @error('persyaratan_jabatan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="card card-success">
                        <div class="card-header">
                            <h4><i class="fas fa-tasks text-success mr-2"></i> 4. Uraian Tugas &amp; Tanggung Jawab</h4>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label for="ikhtisar_jabatan">Ikhtisar Jabatan</label>
                                <textarea name="ikhtisar_jabatan" id="ikhtisar_jabatan" rows="2" class="form-control @error('ikhtisar_jabatan') is-invalid @enderror" placeholder="Ringkasan ruang lingkup tugas pokok jabatan">{{ old('ikhtisar_jabatan', $jabatan->ikhtisar_jabatan) }}</textarea>
                                @error('ikhtisar_jabatan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="uraian_tugas">Uraian Tugas</label>
                                <textarea name="uraian_tugas" id="uraian_tugas" rows="3" class="form-control @error('uraian_tugas') is-invalid @enderror" placeholder="Rincian butir kegiatan dan pekerjaan harian/periodik">{{ old('uraian_tugas', $jabatan->uraian_tugas) }}</textarea>
                                @error('uraian_tugas')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="tanggung_jawab">Tanggung Jawab</label>
                                        <textarea name="tanggung_jawab" id="tanggung_jawab" rows="2" class="form-control @error('tanggung_jawab') is-invalid @enderror" placeholder="Tanggung jawab atas hasil kerja">{{ old('tanggung_jawab', $jabatan->tanggung_jawab) }}</textarea>
                                        @error('tanggung_jawab')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="wewenang">Wewenang</label>
                                        <textarea name="wewenang" id="wewenang" rows="2" class="form-control @error('wewenang') is-invalid @enderror" placeholder="Hak dan wewenang pengambilan keputusan">{{ old('wewenang', $jabatan->wewenang) }}</textarea>
                                        @error('wewenang')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-2">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <a href="{{ route('peta-jabatan.manage.index', ['slug' => 'jabatan']) }}" class="btn btn-secondary">
                        <i class="fas fa-times mr-1"></i> Batal
                    </a>
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-save mr-1"></i> {{ $isEdit ? 'Update Data' : 'Simpan Data' }}
                    </button>
                </div>
            </div>
        </form>
    </div>

    @push('page_js')
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <script>
            $(document).ready(function () {
                $('.select2').each(function () {
                    var placeholder = $(this).data('placeholder') || '-- Pilih Opsi --';
                    var tags = $(this).data('tags') === true || $(this).data('tags') === 'true';
                    $(this).select2({
                        width: '100%',
                        placeholder: placeholder,
                        allowClear: true,
                        tags: tags
                    });
                });

                var isEditMode = {{ $isEdit ? 'true' : 'false' }};
                var initialKode = $('#kode_jabatan').val();
                var generateUrl = "{{ route('peta-jabatan.manage.generate-kode') }}";
                var excludeId = {{ $jabatan->id ?? 'null' }};

                function autoGenerateKode(force = false) {
                    var unitKerjaId = $('#unit_kerja_id').val();
                    var jenisJabatanId = $('#jenis_jabatan_id').val();
                    var currentKode = $('#kode_jabatan').val();

                    if (!jenisJabatanId) {
                        return;
                    }

                    // On edit mode without force, preserve existing custom code
                    if (!force && currentKode && isEditMode && currentKode === initialKode) {
                        return;
                    }

                    $.ajax({
                        url: generateUrl,
                        type: 'GET',
                        data: {
                            unit_kerja_id: unitKerjaId,
                            jenis_jabatan_id: jenisJabatanId,
                            exclude_id: excludeId
                        },
                        success: function (res) {
                            if (res && res.kode) {
                                $('#kode_jabatan').val(res.kode);
                            }
                        },
                        error: function () {
                            var unitOption = $('#unit_kerja_id option:selected');
                            var jenisOption = $('#jenis_jabatan_id option:selected');
                            var unitKode = unitOption.data('kode') || 'UMUM';
                            var jenisKode = jenisOption.data('kode') || 'JF';
                            $('#kode_jabatan').val(unitKode + '-' + jenisKode + '01');
                        }
                    });
                }

                $('#unit_kerja_id, #jenis_jabatan_id').on('change', function () {
                    var currentVal = $('#kode_jabatan').val();
                    if (!isEditMode || !currentVal) {
                        autoGenerateKode(false);
                    }
                });

                $('#btn-auto-generate-kode').on('click', function (e) {
                    e.preventDefault();
                    autoGenerateKode(true);
                });
            });
        </script>
    @endpush
</x-app-layout>
