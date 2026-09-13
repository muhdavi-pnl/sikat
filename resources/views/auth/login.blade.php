<x-auth-page title="Login" google-label="Login dengan Google">
    <x-slot name="status">
        <x-auth-session-status class="mb-4" :status="session('status')" />
    </x-slot>

    <form method="POST" action="{{ route('login') }}" class="needs-validation" novalidate="">
        @csrf
        <div class="form-group">
            <label for="email">Email</label>
            <input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}" tabindex="1" required autofocus autocomplete="username">
            <div class="invalid-feedback">
                Please fill in your email
            </div>
        </div>

        <div class="form-group">
            <div class="d-block">
                <label for="password" class="control-label">Password</label>
                <div class="float-right">
                    <a href="#" class="text-small">
                        Forgot Password?
                    </a>
                </div>
            </div>
            <input id="password" type="password" class="form-control" name="password" tabindex="2" required autocomplete="current-password">
            <div class="invalid-feedback">
                please fill in your password
            </div>
        </div>

        <div class="form-group">
            <label for="captcha">Captcha</label>
            <div class="d-flex align-items-center">
                <span class="badge badge-light p-2 mr-3" style="font-size: 1rem;">{{ session('login_captcha.question', 'Captcha') }}</span>
                <input id="captcha" type="number" class="form-control" name="captcha" value="{{ old('captcha') }}" tabindex="3" required autocomplete="off" min="0">
            </div>
            <div class="invalid-feedback">
                Please solve the captcha
            </div>
        </div>

        <div class="form-group">
            <div class="custom-control custom-checkbox">
                <input type="checkbox" name="remember_me" class="custom-control-input" tabindex="4" id="remember-me">
                <label class="custom-control-label" for="remember_me">Remember Me</label>
            </div>
        </div>

        <div class="form-group">
            <button type="submit" class="btn btn-primary btn-lg btn-block" tabindex="5">
                Login
            </button>
        </div>
    </form>
</x-auth-page>
