<x-auth-page title="Register" google-label="Register dengan Google">
    <form method="POST" action="{{ route('register') }}" class="needs-validation" novalidate="">
        @csrf

        <div class="form-group">
            <label for="name">Nama</label>
            <input id="name" type="text" class="form-control" name="name" value="{{ old('name') }}" tabindex="1" required autofocus autocomplete="name">
            <div class="invalid-feedback">
                Please fill in your name
            </div>
        </div>

        <div class="form-group">
            <label for="email">Email</label>
            <input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}" tabindex="2" required autocomplete="username">
            <div class="invalid-feedback">
                Please fill in your email
            </div>
        </div>

        <div class="form-group">
            <label for="password" class="control-label">Password</label>
            <input id="password" type="password" class="form-control" name="password" tabindex="3" required autocomplete="new-password">
            <div class="invalid-feedback">
                Please fill in your password
            </div>
        </div>

        <div class="form-group">
            <label for="password_confirmation" class="control-label">Konfirmasi Password</label>
            <input id="password_confirmation" type="password" class="form-control" name="password_confirmation" tabindex="4" required autocomplete="new-password">
            <div class="invalid-feedback">
                Please confirm your password
            </div>
        </div>

        <div class="form-group">
            <button type="submit" class="btn btn-primary btn-lg btn-block" tabindex="5">
                Register
            </button>
        </div>
    </form>

    <x-slot name="postActions">
        <div class="text-center text-muted">
            Sudah punya akun?
            <a href="{{ route('login') }}">Login</a>
        </div>
    </x-slot>
</x-auth-page>
