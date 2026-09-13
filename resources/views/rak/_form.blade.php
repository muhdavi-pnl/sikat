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
            <label for="lemari_id">Lemari <span class="text-danger">*</span></label>
            <select name="lemari_id" id="lemari_id" class="form-control @error('lemari_id') is-invalid @enderror" required>
                <option value="">-- Pilih Lemari --</option>
                @foreach($lemaris as $lemariOption)
                    <option value="{{ $lemariOption->id }}" {{ (string) old('lemari_id', $rak->lemari_id) === (string) $lemariOption->id ? 'selected' : '' }}>{{ $lemariOption->lemari }} - {{ optional($lemariOption->ruang)->kode_ruang }} ({{ optional(optional($lemariOption->ruang)->gedung)->nama_gedung }})</option>
                @endforeach
            </select>
            @error('lemari_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="form-group col-md-6">
            <label for="rak">Nama Rak <span class="text-danger">*</span></label>
            <input type="text" name="rak" id="rak" class="form-control @error('rak') is-invalid @enderror" value="{{ old('rak', $rak->rak) }}" required>
            @error('rak')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>

    <div class="form-group mb-0">
        <label for="keterangan">Keterangan</label>
        <textarea name="keterangan" id="keterangan" rows="2" class="form-control @error('keterangan') is-invalid @enderror">{{ old('keterangan', $rak->keterangan) }}</textarea>
        @error('keterangan')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>
<div class="card-footer text-right">
    <a href="{{ route('rak.index') }}" class="btn btn-outline-secondary mr-2">Batal</a>
    <button type="submit" class="btn btn-primary">{{ $submitLabel }}</button>
</div>

