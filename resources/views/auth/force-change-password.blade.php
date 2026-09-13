<x-app-layout>
    @section('title', 'Ubah Password')
    <x-slot name="header">
        <h1>Ubah Password</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item">Keamanan Akun</div>
        </div>
    </x-slot>

    <div class="section-body">
        <h2 class="section-title">Ubah Password</h2>
        <p class="section-lead">Perbarui password akun Anda untuk melanjutkan penggunaan aplikasi.</p>

        <div class="row justify-content-center">
            <div class="col-12 col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h4>Password Wajib Diperbarui</h4>
                    </div>
                    <form method="POST" action="{{ route('password.force.update') }}">
                        @csrf
                        @method('PUT')

                        <div class="card-body">
                            @if(session('status'))
                                <div class="alert alert-info">{{ session('status') }}</div>
                            @endif

                            @if($errors->any())
                                <div class="alert alert-danger">
                                    <ul class="mb-0 pl-3">
                                        @foreach($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <p class="text-muted">
                                Demi keamanan akun, silakan ubah password Anda sebelum melanjutkan ke aplikasi.
                            </p>

                            <div class="form-group">
                                <label>Password Saat Ini</label>
                                <input type="password" name="current_password" class="form-control" required>
                            </div>

                            <div class="form-group">
                                <label>Password Baru</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>

                            <div class="form-group">
                                <label>Konfirmasi Password Baru</label>
                                <input type="password" name="password_confirmation" class="form-control" required>
                            </div>
                        </div>

                        <div class="card-footer text-right">
                            <button type="submit" class="btn btn-primary">Simpan Password Baru</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

