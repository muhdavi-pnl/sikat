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
        <p class="section-lead">Perbarui data akun pengguna dan pengaturan aksesnya.</p>

        <div class="card">
            <div class="card-header">
                <h4>Form {{ $title }}</h4>
            </div>
            <form method="POST" action="{{ route('kepegawaian.pengguna.update', $user->id) }}">
                @csrf
                @method('PUT')
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
                            <label>Pegawai <span class="text-danger">*</span></label>
                            <select name="pegawai_id" id="pegawai_id" class="form-control" required>
                                <option value="">-- Cari Nama / NIP Pegawai --</option>
                                @if($selectedPegawai)
                                    <option value="{{ $selectedPegawai->id }}" selected>{{ strtoupper($selectedPegawai->nama) }} ({{ $selectedPegawai->nip }})</option>
                                @endif
                            </select>
                            <small class="text-muted">Nama akun akan mengikuti data pegawai yang dipilih.</small>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Peran <span class="text-danger">*</span></label>
                            <select name="role" class="form-control" required>
                                <option value="">-- Pilih Peran --</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->name }}" {{ old('role', $user->roles->pluck('name')->first()) === $role->name ? 'selected' : '' }}>{{ ucfirst($role->name) }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">Pilih hak akses peran untuk pengguna.</small>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-12">
                            <label>Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" id="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Password Baru</label>
                            <input type="password" name="password" class="form-control">
                            <small class="text-muted">Kosongkan jika tidak ingin mengubah password.</small>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Konfirmasi Password Baru</label>
                            <input type="password" name="password_confirmation" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="card-footer text-right">
                    <a href="{{ route('kepegawaian.pengguna') }}" class="btn btn-secondary mr-2">Batal</a>
                    <button type="submit" class="btn btn-primary">Perbarui</button>
                </div>
            </form>
        </div>

        <div class="card">
            <div class="card-header">
                <h4>Reset Password Cepat</h4>
            </div>
            <div class="card-body d-flex justify-content-between align-items-center">
                <span>Password akan direset ke default: <strong>Sikat2019</strong></span>
                <form method="POST" action="{{ route('kepegawaian.pengguna.reset-password', $user->id) }}" class="js-confirm-submit" data-confirm-variant="reset" data-confirm-title="Yakin ingin mereset password pengguna ini?" data-confirm-text="Password pengguna ini akan direset ke default Sikat2019." data-confirm-item-label="Email Pengguna" data-confirm-item-name="{{ $user->email }}" data-confirm-button="Ya, reset">
                    @csrf
                    <button type="submit" class="btn btn-warning">Reset Password</button>
                </form>
            </div>
        </div>
    </div>

    @push('plugins_js')
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    @endpush

    @push('page_js')
        <script>
            $(function () {
                var $pegawaiSelect = $('#pegawai_id');
                var $emailInput = $('#email');

                $pegawaiSelect.select2({
                    width: '100%',
                    placeholder: '-- Cari Nama / NIP Pegawai --',
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
                                current_user_id: '{{ $user->id }}'
                            };
                        },
                        processResults: function (data) {
                            return data;
                        }
                    }
                });

                $pegawaiSelect.on('select2:select', function (e) {
                    var data = e.params.data;
                    if (data && data.email && !$emailInput.val()) {
                        $emailInput.val(data.email);
                    }
                });
            });
        </script>
    @endpush
</x-app-layout>
