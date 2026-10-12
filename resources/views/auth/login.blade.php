@php $theme = asset('theme/assets'); @endphp
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8" />
    <title>Connexion — {{ config('aggregpay.brand') }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="{{ $theme }}/images/favicon.ico">
    <link href="{{ $theme }}/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    @include('partials.portal.icon-fonts')
    <link href="{{ $theme }}/css/app.min.css" rel="stylesheet" type="text/css" />
    <link href="{{ asset('theme/portal.css') }}?v=4" rel="stylesheet" type="text/css" />
    @include('partials.portal.brand-styles')
</head>
<body>
<div class="auth-bg-basic d-flex align-items-center min-vh-100">
    <div class="bg-overlay bg-light"></div>
    <div class="container">
        <div class="d-flex flex-column min-vh-100 py-5 px-3">
            <div class="row justify-content-center">
                <div class="col-xl-5 text-center text-muted mb-2">
                    <a href="{{ route('login') }}">
                        <span class="logo-lg">
                            <img src="{{ $theme }}/images/logo-sm.svg" alt="" height="24">
                            <span class="logo-txt">{{ config('aggregpay.brand') }}</span>
                        </span>
                    </a>
                    <p class="text-muted font-size-15 w-75 mx-auto mt-3 mb-0">Portail partenaire</p>
                </div>
            </div>
            <div class="row justify-content-center my-auto">
                <div class="col-md-8 col-lg-6 col-xl-5">
                    <div class="card shadow-sm border-0">
                        <div class="card-body p-4">
                            <div class="text-center mb-4">
                                <h5 class="mb-1">Bienvenue</h5>
                                <p class="text-muted">Connectez-vous à votre espace</p>
                            </div>
                            <form method="POST" action="{{ route('login') }}">
                                @csrf
                                <div class="form-floating form-floating-custom mb-3">
                                    <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" placeholder="Email" required>
                                    <label for="email">Email</label>
                                    <div class="form-floating-icon"><i class="uil uil-envelope"></i></div>
                                </div>
                                <div class="form-floating form-floating-custom mb-3">
                                    <input type="password" class="form-control" id="password" name="password" placeholder="Mot de passe" required>
                                    <label for="password">Mot de passe</label>
                                    <div class="form-floating-icon"><i class="uil uil-padlock"></i></div>
                                </div>
                                @if($errors->any())
                                    <div class="alert alert-danger py-2">{{ $errors->first() }}</div>
                                @endif
                                <button type="submit" class="btn btn-primary w-100 mt-2">Se connecter</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="{{ $theme }}/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
