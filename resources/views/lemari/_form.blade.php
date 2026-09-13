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
            <label for="ruang_id">Ruang <span class="text-danger">*</span></label>
            <select name="ruang_id" id="ruang_id" class="form-control @error('ruang_id') is-invalid @enderror" required>
                <option value="">-- Pilih Ruang --</option>
                @foreach($ruangs as $ruang)
                    <option value="{{ $ruang->id }}" {{ (string) old('ruang_id', $lemari->ruang_id) === (string) $ruang->id ? 'selected' : '' }}>{{ $ruang->kode_ruang }} - {{ $ruang->nama_ruang }} ({{ optional($ruang->gedung)->nama_gedung }})</option>
                @endforeach
            </select>
            @error('ruang_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="form-group col-md-6">
            <label for="lemari">Nama Lemari <span class="text-danger">*</span></label>
            <input type="text" name="lemari" id="lemari" class="form-control @error('lemari') is-invalid @enderror" value="{{ old('lemari', $lemari->lemari) }}" required>
            @error('lemari')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>

    <div class="form-group mb-0">
        <label for="keterangan">Keterangan</label>
        <textarea name="keterangan" id="keterangan" rows="2" class="form-control @error('keterangan') is-invalid @enderror">{{ old('keterangan', $lemari->keterangan) }}</textarea>
        @error('keterangan')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>
<div class="card-footer text-right">
    <a href="{{ route('lemari.index') }}" class="btn btn-outline-secondary mr-2">Batal</a>
    <button type="submit" class="btn btn-primary">{{ $submitLabel }}</button>
</div>

