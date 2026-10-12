@php $theme = asset('theme/assets'); @endphp
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8" />
    <title>{{ $forced ? 'Nouveau mot de passe' : 'Mot de passe' }} — {{ config('aggregpay.brand') }}</title>
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
                    <a href="{{ route('dashboard') }}">
                        <span class="logo-lg">
                            <img src="{{ $theme }}/images/logo-sm.svg" alt="" height="24">
                            <span class="logo-txt">{{ config('aggregpay.brand') }}</span>
                        </span>
                    </a>
                </div>
            </div>
            <div class="row justify-content-center my-auto">
                <div class="col-md-8 col-lg-6 col-xl-5">
                    <div class="card shadow-sm border-0">
                        <div class="card-body p-4">
                            <div class="text-center mb-4">
                                <div class="portal-auth-icon mx-auto mb-3">
                                    <i class="uil uil-shield-check"></i>
                                </div>
                                <h5 class="mb-1">{{ $forced ? 'Définissez votre mot de passe' : 'Modifier le mot de passe' }}</h5>
                                @if($forced)
                                    <p class="text-muted mb-0">Pour des raisons de sécurité, choisissez un mot de passe personnel avant d'accéder au portail.</p>
                                @else
                                    <p class="text-muted mb-0">Choisissez un mot de passe sécurisé.</p>
                                @endif
                            </div>
                            <form method="POST" action="{{ route('password.change.update') }}">
                                @csrf
                                @method('PUT')
                                @if(!$forced)
                                    <div class="form-floating form-floating-custom mb-3">
                                        <input type="password" class="form-control" id="current_password" name="current_password" placeholder="Mot de passe actuel" required>
                                        <label for="current_password">Mot de passe actuel</label>
                                        <div class="form-floating-icon"><i class="uil uil-padlock"></i></div>
                                    </div>
                                @endif
                                <div class="form-floating form-floating-custom mb-3">
                                    <input type="password" class="form-control" id="password" name="password" placeholder="Nouveau mot de passe" required minlength="8">
                                    <label for="password">Nouveau mot de passe</label>
                                    <div class="form-floating-icon"><i class="uil uil-key-skeleton"></i></div>
                                </div>
                                <div class="form-floating form-floating-custom mb-3">
                                    <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" placeholder="Confirmation" required minlength="8">
                                    <label for="password_confirmation">Confirmer le mot de passe</label>
                                    <div class="form-floating-icon"><i class="uil uil-check-circle"></i></div>
                                </div>
                                @if($errors->any())
                                    <div class="alert alert-danger py-2">{{ $errors->first() }}</div>
                                @endif
                                <button type="submit" class="btn btn-primary w-100 mt-2">Enregistrer</button>
                                @if(!$forced)
                                    <a href="{{ route('dashboard') }}" class="btn btn-soft-secondary w-100 mt-2">Annuler</a>
                                @endif
                            </form>
                            @if($forced)
                                <form method="POST" action="{{ route('logout') }}" class="mt-2">
                                    @csrf
                                    <button type="submit" class="btn btn-link text-muted w-100">Se déconnecter</button>
                                </form>
                            @endif
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
