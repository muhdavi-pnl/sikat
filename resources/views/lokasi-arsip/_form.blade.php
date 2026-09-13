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

    <div class="form-row">
        <div class="form-group col-md-6">
            <label for="pegawai_id">Pegawai <span class="text-danger">*</span></label>
            <select name="pegawai_id" id="pegawai_id" class="form-control @error('pegawai_id') is-invalid @enderror" required>
                <option value="">-- Pilih Pegawai --</option>
                @foreach($pegawais as $pegawai)
                    <option value="{{ $pegawai->id }}" {{ (string) old('pegawai_id', $lokasiArsip->pegawai_id) === (string) $pegawai->id ? 'selected' : '' }}>{{ strtoupper($pegawai->nama) }} ({{ $pegawai->nip }})</option>
                @endforeach
            </select>
            @error('pegawai_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="form-group col-md-6">
            <label for="rak_id">Rak <span class="text-danger">*</span></label>
            <select name="rak_id" id="rak_id" class="form-control @error('rak_id') is-invalid @enderror" required>
                <option value="">-- Pilih Rak --</option>
                @foreach($raks as $rak)
                    <option value="{{ $rak->id }}" {{ (string) old('rak_id', $lokasiArsip->rak_id) === (string) $rak->id ? 'selected' : '' }}>{{ $rak->rak }} - {{ optional($rak->lemari)->lemari }} / {{ optional(optional($rak->lemari)->ruang)->kode_ruang }} / {{ optional(optional(optional($rak->lemari)->ruang)->gedung)->nama_gedung }}</option>
                @endforeach
            </select>
            @error('rak_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>

    <div class="form-group mb-0">
        <label for="keterangan">Keterangan</label>
        <textarea name="keterangan" id="keterangan" rows="2" class="form-control @error('keterangan') is-invalid @enderror">{{ old('keterangan', $lokasiArsip->keterangan) }}</textarea>
        @error('keterangan')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>
<div class="card-footer text-right">
    <a href="{{ route('lokasi-arsip.index') }}" class="btn btn-outline-secondary mr-2">Batal</a>
    <button type="submit" class="btn btn-primary">{{ $submitLabel }}</button>
</div>

