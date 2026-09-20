<div class="card-body">
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible show fade">
            <div class="alert-body">
                <button class="close" data-dismiss="alert"><span>&times;</span></button>
                <div class="alert-title font-weight-bold"><i class="fas fa-exclamation-triangle mr-1"></i> Perhatian</div>
                <ul class="mb-0 pl-3">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <div class="form-group">
        <label for="judul">Judul Pengumuman <span class="text-danger">*</span></label>
        <input type="text" name="judul" id="judul" class="form-control @error('judul') is-invalid @enderror" value="{{ old('judul', $pengumuman->judul) }}" placeholder="Masukkan judul pengumuman" required>
        @error('judul')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="form-group">
        <label class="d-block font-weight-bold">Tipe Pengumuman <span class="text-danger">*</span></label>
        <div class="row">
            @php $currentTipe = old('tipe', $pengumuman->tipe ?? 'teks'); @endphp
            <div class="col-md-4 mb-2">
                <div class="custom-control custom-radio custom-control-inline p-2 border rounded w-100 {{ $currentTipe === 'teks' ? 'bg-light border-primary' : '' }}" id="radio-wrapper-teks">
                    <input type="radio" id="tipe_teks" name="tipe" value="teks" class="custom-control-input js-tipe-radio" {{ $currentTipe === 'teks' ? 'checked' : '' }}>
                    <label class="custom-control-label font-weight-semibold cursor-pointer w-100" for="tipe_teks">
                        <i class="fas fa-align-left text-primary mr-1"></i> Teks Saja
                        <small class="d-block text-muted">Pengumuman berupa pesan/tulisan teks.</small>
                    </label>
                </div>
            </div>
            <div class="col-md-4 mb-2">
                <div class="custom-control custom-radio custom-control-inline p-2 border rounded w-100 {{ $currentTipe === 'gambar' ? 'bg-light border-primary' : '' }}" id="radio-wrapper-gambar">
                    <input type="radio" id="tipe_gambar" name="tipe" value="gambar" class="custom-control-input js-tipe-radio" {{ $currentTipe === 'gambar' ? 'checked' : '' }}>
                    <label class="custom-control-label font-weight-semibold cursor-pointer w-100" for="tipe_gambar">
                        <i class="fas fa-image text-success mr-1"></i> Gambar Saja
                        <small class="d-block text-muted">Pengumuman berupa poster / banner gambar.</small>
                    </label>
                </div>
            </div>
            <div class="col-md-4 mb-2">
                <div class="custom-control custom-radio custom-control-inline p-2 border rounded w-100 {{ $currentTipe === 'keduanya' ? 'bg-light border-primary' : '' }}" id="radio-wrapper-keduanya">
                    <input type="radio" id="tipe_keduanya" name="tipe" value="keduanya" class="custom-control-input js-tipe-radio" {{ $currentTipe === 'keduanya' ? 'checked' : '' }}>
                    <label class="custom-control-label font-weight-semibold cursor-pointer w-100" for="tipe_keduanya">
                        <i class="fas fa-photo-video text-warning mr-1"></i> Teks & Gambar
                        <small class="d-block text-muted">Kombinasi gambar banner dan teks penjelasan.</small>
                    </label>
                </div>
            </div>
        </div>
        @error('tipe')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
    </div>

    <div class="form-group" id="field-isi-container">
        <label for="isi">Isi Pengumuman <span class="text-danger" id="isi-required-mark">*</span></label>
        <textarea name="isi" id="isi" rows="6" class="form-control @error('isi') is-invalid @enderror" placeholder="Tuliskan isi pengumuman secara rinci di sini...">{{ old('isi', $pengumuman->isi) }}</textarea>
        @error('isi')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="form-group" id="field-gambar-container">
        <label for="gambar">File Gambar / Banner <span class="text-danger" id="gambar-required-mark">*</span></label>
        <div class="custom-file">
            <input type="file" name="gambar" id="gambar" class="custom-file-input @error('gambar') is-invalid @enderror" accept="image/jpeg,image/png,image/jpg,image/webp,image/gif" onchange="previewImage(this)">
            <label class="custom-file-label" for="gambar" id="gambar-file-label">Pilih file gambar (JPG, PNG, WebP maks 5MB)</label>
        </div>
        @error('gambar')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
        <small class="form-text text-muted">Disarankan menggunakan gambar rasio lanskap atau poster yang jelas (maksimal 5MB).</small>

        <div id="image-preview-wrapper" class="mt-3 {{ empty($pengumuman->gambar) ? 'd-none' : '' }}">
            <p class="font-weight-bold mb-1 text-muted small">Pratinjau Gambar:</p>
            <div class="border rounded p-2 bg-light d-inline-block position-relative shadow-sm" style="max-width: 100%;">
                <img id="image-preview" src="{{ $pengumuman->gambar ? asset($pengumuman->gambar) : '' }}" alt="Pratinjau" class="img-fluid rounded" style="max-height: 280px; object-fit: contain;">
            </div>
        </div>
    </div>

    <hr class="my-4">

    <div class="row">
        <div class="form-group col-md-4">
            <label for="target_role">Target Penerima</label>
            <select name="target_role" id="target_role" class="form-control @error('target_role') is-invalid @enderror">
                <option value="semua" {{ old('target_role', $pengumuman->target_role ?? 'semua') === 'semua' ? 'selected' : '' }}>Semua Pengguna (Default)</option>
                <option value="pegawai" {{ old('target_role', $pengumuman->target_role) === 'pegawai' ? 'selected' : '' }}>Pegawai (Dosen & Tendik)</option>
                <option value="pimpinan" {{ old('target_role', $pengumuman->target_role) === 'pimpinan' ? 'selected' : '' }}>Pimpinan</option>
                <option value="kepegawaian" {{ old('target_role', $pengumuman->target_role) === 'kepegawaian' ? 'selected' : '' }}>Staf Kepegawaian</option>
                <option value="super-admin" {{ old('target_role', $pengumuman->target_role) === 'super-admin' ? 'selected' : '' }}>Super Admin</option>
            </select>
            @error('target_role')<div class="invalid-feedback">{{ $message }}</div>@enderror
            <small class="form-text text-muted">Pengumuman hanya muncul pada pop-up role terkait.</small>
        </div>

        <div class="form-group col-md-4">
            <label for="tanggal_mulai">Tanggal Mulai Berlaku</label>
            <input type="date" name="tanggal_mulai" id="tanggal_mulai" class="form-control @error('tanggal_mulai') is-invalid @enderror" value="{{ old('tanggal_mulai', $pengumuman->tanggal_mulai ? $pengumuman->tanggal_mulai->format('Y-m-d') : '') }}">
            @error('tanggal_mulai')<div class="invalid-feedback">{{ $message }}</div>@enderror
            <small class="form-text text-muted">Kosongkan jika berlaku langsung.</small>
        </div>

        <div class="form-group col-md-4">
            <label for="tanggal_selesai">Tanggal Selesai Berlaku</label>
            <input type="date" name="tanggal_selesai" id="tanggal_selesai" class="form-control @error('tanggal_selesai') is-invalid @enderror" value="{{ old('tanggal_selesai', $pengumuman->tanggal_selesai ? $pengumuman->tanggal_selesai->format('Y-m-d') : '') }}">
            @error('tanggal_selesai')<div class="invalid-feedback">{{ $message }}</div>@enderror
            <small class="form-text text-muted">Kosongkan jika tidak ada batas waktu.</small>
        </div>
    </div>

    <div class="form-group mb-0">
        <div class="custom-control custom-checkbox">
            <input type="checkbox" name="is_aktif" class="custom-control-input" id="is_aktif" value="1" {{ old('is_aktif', $pengumuman->is_aktif ?? true) ? 'checked' : '' }}>
            <label class="custom-control-label font-weight-bold" for="is_aktif">Aktifkan Pengumuman Ini</label>
            <small class="form-text text-muted">Jika tidak dicentang, pengumuman akan tersimpan sebagai draf dan tidak tampil di pop-up pengguna.</small>
        </div>
    </div>
</div>

<div class="card-footer text-right bg-whitesmoke">
    <a href="{{ route('pengumuman.index') }}" class="btn btn-secondary mr-2"><i class="fas fa-arrow-left mr-1"></i> Batal</a>
    <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> {{ $submitLabel }}</button>
</div>

@push('page_js')
<script>
    function updateFormVisibility() {
        const tipe = $('input[name="tipe"]:checked').val() || 'teks';
        const isUpdate = {{ $pengumuman->exists ? 'true' : 'false' }};
        const hasExistingImage = {{ $pengumuman->gambar ? 'true' : 'false' }};

        $('.custom-control-inline').removeClass('bg-light border-primary');
        $('#radio-wrapper-' + tipe).addClass('bg-light border-primary');

        if (tipe === 'teks') {
            $('#field-isi-container').slideDown(200);
            $('#isi-required-mark').show();
            $('#field-gambar-container').slideUp(200);
            $('#gambar-required-mark').hide();
        } else if (tipe === 'gambar') {
            $('#field-isi-container').slideUp(200);
            $('#isi-required-mark').hide();
            $('#field-gambar-container').slideDown(200);
            if (isUpdate && hasExistingImage) {
                $('#gambar-required-mark').hide();
            } else {
                $('#gambar-required-mark').show();
            }
        } else if (tipe === 'keduanya') {
            $('#field-isi-container').slideDown(200);
            $('#isi-required-mark').show();
            $('#field-gambar-container').slideDown(200);
            $('#gambar-required-mark').hide();
        }
    }

    function previewImage(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            const fileName = input.files[0].name;
            $('#gambar-file-label').text(fileName);

            reader.onload = function(e) {
                $('#image-preview').attr('src', e.target.result);
                $('#image-preview-wrapper').removeClass('d-none');
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    $(document).ready(function() {
        updateFormVisibility();
        $('input[name="tipe"]').on('change', function() {
            updateFormVisibility();
        });
    });
</script>
@endpush
