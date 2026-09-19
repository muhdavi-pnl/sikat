@props([
    'title',
    'googleLabel',
])

<x-guest-layout>
    @section('title', $title)

    <section class="section">
        <div class="container mt-5">
            <div class="row">
                <div class="col-12 col-sm-8 offset-sm-2 col-md-6 offset-md-3 col-lg-6 offset-lg-3 col-xl-4 offset-xl-4">
                    <div class="login-brand">
                        <a href="{{ route('landing') }}">
                            <img src="{{ asset('img/logo-pnl.png') }}" class="fill-current inline" alt="Logo PNL" width="100" height="100" />
                        </a>
                    </div>

                    <div class="card card-primary">
                        <div class="card-header">
                            <h4>{{ $title }}</h4>
                        </div>

                        <div class="card-body">
                            {{ $status ?? '' }}

                            <x-auth-validation-errors class="mb-4" :errors="$errors" />

                            {{ $slot }}

                            @if (Route::has('auth.google.redirect'))
                                <div class="text-center text-muted mb-3">atau</div>

                                <div class="form-group {{ isset($postActions) ? 'mb-3' : 'mb-0' }}">
                                    <a href="{{ route('landing') }}" class="btn btn-danger btn-lg btn-block">
                                        <i class="fas fa-angle-left mr-2"></i> Kembali
                                    </a>
                                </div>
                            @endif

                            {{ $postActions ?? '' }}
                        </div>
                    </div>
                    <div class="simple-footer">
                        Copyright &copy; SIKAT 2022-{{ date('Y') }}
                    </div>
                </div>
            </div>
        </div>
    </section>
</x-guest-layout>
