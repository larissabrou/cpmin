@php
    $theme = asset('theme/assets');
    $brand = config('aggregpay.brand');
    $user = auth()->user();
    $company = request()->attributes->get('company');
    $userCompanies = $user?->companies()->orderBy('name')->get() ?? collect();
@endphp
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8" />
    <title>@yield('title', 'Portail') — {{ $brand }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="{{ $theme }}/images/favicon.ico">
    <link href="{{ $theme }}/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    @include('partials.portal.icon-fonts')
    <link href="{{ $theme }}/css/app.min.css" rel="stylesheet" type="text/css" />
    <link href="{{ asset('theme/portal.css') }}?v=6" rel="stylesheet" type="text/css" />
    @include('partials.portal.brand-styles')
    @stack('styles')
</head>
<body data-layout="horizontal" data-topbar="dark">

<div id="layout-wrapper">

    <header id="page-topbar">
        <div class="navbar-header">
            <div class="d-flex">
                <div class="navbar-brand-box">
                    <a href="{{ route('dashboard') }}" class="logo logo-dark">
                        <span class="logo-sm"><img src="{{ $theme }}/images/logo-sm.svg" alt="" height="26"></span>
                        <span class="logo-lg"><img src="{{ $theme }}/images/logo-sm.svg" alt="" height="26"> <span class="logo-txt">{{ $brand }}</span></span>
                    </a>
                    <a href="{{ route('dashboard') }}" class="logo logo-light">
                        <span class="logo-sm"><img src="{{ $theme }}/images/logo-sm.svg" alt="" height="26"></span>
                        <span class="logo-lg"><img src="{{ $theme }}/images/logo-sm.svg" alt="" height="26"> <span class="logo-txt">{{ $brand }}</span></span>
                    </a>
                </div>

                <button type="button" class="btn btn-sm px-3 font-size-16 d-lg-none header-item" data-bs-toggle="collapse" id="horimenu-btn" data-bs-target="#topnav-menu-content">
                    <i class="fa fa-fw fa-bars"></i>
                </button>

                <div class="topnav">
                    <nav class="navbar navbar-light navbar-expand-lg topnav-menu">
                        <div class="collapse navbar-collapse" id="topnav-menu-content">
                            <ul class="navbar-nav">
                                <li class="nav-item">
                                    <a class="nav-link arrow-none {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                                        <i class="bx bx-home-circle icon"></i>
                                        <span>Tableau de bord</span>
                                    </a>
                                </li>
                                <li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle arrow-none {{ request()->routeIs('transactions.*') ? 'active' : '' }}" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="bx bx-transfer-alt icon"></i>
                                        <span>Transactions</span>
                                        <div class="arrow-down"></div>
                                    </a>
                                    <div class="dropdown-menu">
                                        <a href="{{ route('transactions.payins') }}" class="dropdown-item {{ request()->routeIs('transactions.payins') ? 'active' : '' }}">Payins</a>
                                        <a href="{{ route('transactions.payouts') }}" class="dropdown-item {{ request()->routeIs('transactions.payouts') ? 'active' : '' }}">Payouts</a>
                                    </div>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link arrow-none {{ request()->routeIs('api.credentials*') ? 'active' : '' }}" href="{{ route('api.credentials') }}">
                                        <i class="bx bx-key icon"></i>
                                        <span>Clé API</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link arrow-none {{ request()->routeIs('docs') ? 'active' : '' }}" href="{{ route('docs') }}">
                                        <i class="bx bx-book-open icon"></i>
                                        <span>Documentation</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </nav>
                </div>
            </div>

            <div class="d-flex align-items-center">
                @if($userCompanies->count() > 1)
                <div class="dropdown d-inline-block me-1">
                    <button type="button" class="btn header-item portal-project-switch text-start d-flex align-items-center" data-bs-toggle="dropdown" aria-expanded="false" title="Changer de projet">
                        <i class="bx bx-buildings font-size-18 me-1"></i>
                        <span class="d-none d-lg-inline-block text-truncate portal-project-switch__name">{{ $company?->name ?? 'Projet' }}</span>
                        <i class="mdi mdi-chevron-down d-none d-lg-inline-block ms-1"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end portal-project-switch__menu">
                        <h6 class="dropdown-header">Changer de projet</h6>
                        @foreach($userCompanies as $userCompany)
                            @if($userCompany->id === $company?->id)
                                <span class="dropdown-item active d-flex align-items-center gap-2">
                                    <i class="bx bx-check-circle text-success"></i>
                                    <span>
                                        <span class="d-block">{{ $userCompany->name }}</span>
                                        <small class="text-muted">{{ $userCompany->slug }}</small>
                                    </span>
                                </span>
                            @else
                                <form method="POST" action="{{ route('company.switch') }}" class="portal-project-switch__form">
                                    @csrf
                                    <input type="hidden" name="company_id" value="{{ $userCompany->id }}">
                                    <button type="submit" class="dropdown-item d-flex align-items-center gap-2 w-100">
                                        <i class="bx bx-buildings text-muted"></i>
                                        <span>
                                            <span class="d-block">{{ $userCompany->name }}</span>
                                            <small class="text-muted">{{ $userCompany->slug }}</small>
                                        </span>
                                    </button>
                                </form>
                            @endif
                        @endforeach
                    </div>
                </div>
                @endif

                <div class="dropdown d-inline-block">
                    <button type="button" class="btn header-item user text-start d-flex align-items-center" data-bs-toggle="dropdown">
                        <span class="avatar-sm me-2">
                            <span class="avatar-title rounded-circle bg-primary-subtle text-primary font-size-16">
                                {{ strtoupper(substr($user->name ?? $user->email, 0, 1)) }}
                            </span>
                        </span>
                        <span class="ms-2 d-none d-xl-inline-block user-item-desc">
                            <span class="user-name">{{ $user->name ?? $user->email }} <i class="mdi mdi-chevron-down"></i></span>
                        </span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end pt-0">
                        <h6 class="dropdown-header">{{ $company?->name ?? 'Compte' }}</h6>
                        <a class="dropdown-item" href="{{ route('password.change') }}"><i class="mdi mdi-lock-reset text-muted font-size-16 align-middle me-1"></i> Mot de passe</a>
                        <div class="dropdown-divider"></div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item"><i class="mdi mdi-logout text-muted font-size-16 align-middle me-1"></i> Déconnexion</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        @hasSection('kpi_strip')
            <div class="collapse dash-content" id="dashtoggle">
                <div class="container-fluid">
                    @yield('kpi_strip')
                </div>
            </div>
            <div>
                <a class="dash-troggle-icon collapsed" id="dash-troggle-icon" data-bs-toggle="collapse" href="#dashtoggle" aria-expanded="false" aria-controls="dashtoggle">
                    <i class="bx bx-up-arrow-alt"></i>
                </a>
            </div>
        @endif
    </header>

    <div class="hori-overlay"></div>

    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                @yield('content')
            </div>
        </div>
        <footer class="footer">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-sm-6">{{ date('Y') }} © {{ $brand }}</div>
                    <div class="col-sm-6">
                        <div class="text-sm-end d-none d-sm-block">Portail partenaire</div>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</div>

<script src="{{ $theme }}/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="{{ $theme }}/libs/metismenujs/metismenujs.min.js"></script>
<script src="{{ $theme }}/libs/simplebar/simplebar.min.js"></script>
<script src="{{ $theme }}/libs/feather-icons/feather.min.js"></script>
<script src="{{ asset('theme/portal-app.js') }}?v=2"></script>
@stack('scripts')
</body>
</html>
