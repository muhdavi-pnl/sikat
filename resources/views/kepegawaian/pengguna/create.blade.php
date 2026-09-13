<x-app-layout>
    @push('plugins_css')
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    @endpush

    @push('page_css')
        <style>
            .select2-container {
                width: 100% !important;
            }
        </style>
    @endpush

    @section('title', $title)
    <x-slot name="header">
        <h1>{{ $title }}</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{ route('dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item active"><a href="{{ route('kepegawaian.pengguna') }}">Pengguna</a></div>
            <div class="breadcrumb-item">{{ $title }}</div>
        </div>
    </x-slot>

    <div class="section-body">
        <h2 class="section-title">{{ $title }}</h2>
        <p class="section-lead">Buat akun pengguna baru dan hubungkan dengan data pegawai.</p>

        <div class="card">
            <div class="card-header">
                <h4>Form {{ $title }}</h4>
            </div>
            <form method="POST" action="{{ route('kepegawaian.pengguna.store') }}">
                @csrf
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
                            <label>Nama <span class="text-danger">*</span></label>
                            <select name="pegawai_id" id="pegawai_id" class="form-control" required>
                                <option value="">-- Pilih Nama Pegawai --</option>
                                @if($selectedPegawai)
                                    <option value="{{ $selectedPegawai->id }}" selected>{{ strtoupper($selectedPegawai->nama) }} ({{ $selectedPegawai->nip }})</option>
                                @endif
                            </select>
                            <small class="text-muted">Nama akun akan mengikuti data pegawai yang dipilih.</small>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Password <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Konfirmasi Password <span class="text-danger">*</span></label>
                            <input type="password" name="password_confirmation" class="form-control" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Peran <span class="text-danger">*</span></label>
                            <select name="role" class="form-control" required>
                                <option value="">-- Pilih Peran --</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->name }}" {{ old('role') === $role->name ? 'selected' : '' }}>{{ ucfirst($role->name) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Unit Kerja</label>
                            <select name="unit_kerja_id" class="form-control">
                                <option value="">-- Pilih Unit Kerja --</option>
                                @foreach($unitKerjas as $unitKerja)
                                    <option value="{{ $unitKerja->id }}" {{ (string) old('unit_kerja_id') === (string) $unitKerja->id ? 'selected' : '' }}>{{ $unitKerja->unit_kerja }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-right">
                    <a href="{{ route('kepegawaian.pengguna') }}" class="btn btn-secondary mr-2">Batal</a>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    @push('plugins_js')
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    @endpush

    @push('page_js')
        <script>
            $(function () {
                $('#pegawai_id').select2({
                    width: '100%',
                    placeholder: '-- Pilih Nama Pegawai --',
                    allowClear: true,
                    minimumInputLength: 2,
                    language: {
                        inputTooShort: function () {
                            return 'Ketik minimal 2 karakter';
                        }
                    },
                    ajax: {
                        url: '{{ route('kepegawaian.pengguna.options.pegawais') }}',
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


