@php $theme = asset('theme/assets'); @endphp

<!doctype html>

<html lang="fr">

<head>

    <meta charset="utf-8" />

    <title>Vérification — {{ config('aggregpay.brand') }}</title>

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

            <div class="row justify-content-center my-auto">

                <div class="col-md-8 col-lg-6 col-xl-5">

                    <div class="card shadow-sm border-0">

                        <div class="card-body p-4">

                            <div class="text-center mb-4">

                                <h5 class="mb-1">Vérification en deux étapes</h5>

                                <p class="text-muted">Un code à 6 chiffres a été envoyé à votre adresse e-mail.</p>

                            </div>

                            <form method="POST" action="{{ route('login.two-factor.verify') }}">

                                @csrf

                                <div class="form-floating form-floating-custom mb-3">

                                    <input type="text" class="form-control text-center font-monospace fs-4 letter-spacing-2" id="code" name="code" inputmode="numeric" pattern="[0-9]*" maxlength="20" placeholder="000000" required autofocus autocomplete="one-time-code">

                                    <label for="code">Code reçu par e-mail</label>

                                    <div class="form-floating-icon"><i class="uil uil-shield-check"></i></div>

                                </div>

                                @if($errors->any())

                                    <div class="alert alert-danger py-2">{{ $errors->first() }}</div>

                                @endif

                                <button type="submit" class="btn btn-primary w-full w-100 mt-2">Valider</button>

                            </form>

                            <p class="text-center mt-3 mb-0">

                                <a href="{{ route('login') }}" class="text-muted">Annuler et revenir</a>

                            </p>

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


