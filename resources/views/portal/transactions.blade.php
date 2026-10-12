@extends('layouts.portal')

@section('title', $type === 'payins' ? 'Payins' : 'Payouts')

@section('kpi_strip')
    @php
        $filteredCount = (int) ($stats['filtered_total'] ?? count($items));
        $filteredVolume = $type === 'payins'
            ? (int) ($stats['net_success'] ?? 0)
            : (int) ($stats['debited_success'] ?? 0);
        $volumeLabel = $type === 'payins' ? 'Volume filtré' : 'Volume filtré';
        $volumeSub = $type === 'payins'
            ? 'XOF · net après frais · réussis'
            : 'XOF · montant + frais · réussis';
    @endphp
    <div class="row portal-page-head">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <h4 class="mb-0 sub-title text-white">{{ $type === 'payins' ? 'Encaissements' : 'Décaissements' }}</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-white-50">Dashboard</a></li>
                        <li class="breadcrumb-item active text-white-50">{{ $type === 'payins' ? 'Payins' : 'Payouts' }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-xl-12">
            <div class="card dash-header-box shadow-none border-0">
                <div class="card-body p-0">
                    <div class="row row-cols-xxl-2 row-cols-md-2 row-cols-1 g-0">
                        <div class="col">
                            <div class="py-3 px-4 mx-2 portal-stat-mini">
                                <div class="stat-icon"><i class="bx bx-wallet"></i></div>
                                <p class="stat-label mb-0">{{ $volumeLabel }}</p>
                                <h3 class="stat-value mb-0">{{ number_format($filteredVolume, 0, ',', ' ') }}</h3>
                                <p class="stat-sub mb-0">{{ $volumeSub }}</p>
                            </div>
                        </div>
                        <div class="col">
                            <div class="py-3 px-4 mx-2 portal-stat-mini">
                                <div class="stat-icon"><i class="bx bx-receipt"></i></div>
                                <p class="stat-label mb-0">Transactions</p>
                                <h3 class="stat-value mb-0">{{ number_format($filteredCount, 0, ',', ' ') }}</h3>
                                <p class="stat-sub mb-0">résultat{{ $filteredCount > 1 ? 's' : '' }} du filtre</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content')
@if(!$company->hasGatewayCredentials())
    <div class="alert alert-warning">
        Configurez votre <a href="{{ route('api.credentials') }}" class="alert-link">clé API</a>.
    </div>
@elseif($error)
    <div class="alert alert-danger">{{ $error }}</div>
@endif

@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

@php
    $exportRoute = $type === 'payins' ? 'transactions.payins.export' : 'transactions.payouts.export';
    $exportQuery = request()->only(['status', 'country', 'date_from', 'date_to', 'reference']);
@endphp

<div class="card">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end mb-4 portal-date-range">
            <div class="col-md-2">
                <label class="form-label">Référence</label>
                <input type="text" name="reference" class="form-control" placeholder="ORD-12345" value="{{ request('reference') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">Du</label>
                <div class="portal-date-wrap">
                    <input type="text" name="date_from" class="form-control portal-date-input portal-date-from" placeholder="JJ/MM/AAAA" inputmode="numeric" maxlength="10" autocomplete="off" value="{{ $filterDisplay['date_from'] ?? '' }}">
                    <i class="bx bx-calendar portal-date-icon" aria-hidden="true"></i>
                </div>
            </div>
            <div class="col-md-2">
                <label class="form-label">Au</label>
                <div class="portal-date-wrap">
                    <input type="text" name="date_to" class="form-control portal-date-input portal-date-to" placeholder="JJ/MM/AAAA" inputmode="numeric" maxlength="10" autocomplete="off" value="{{ $filterDisplay['date_to'] ?? '' }}">
                    <i class="bx bx-calendar portal-date-icon" aria-hidden="true"></i>
                </div>
            </div>
            <div class="col-md-2">
                <label class="form-label">Statut</label>
                <select name="status" class="form-select">
                    <option value="">Tous</option>
                    @foreach(['PENDING','SUCCESS','FAILED','CANCELLED'] as $s)
                        <option value="{{ $s }}" @selected(request('status')===$s)>{{ $s }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-1">
                <label class="form-label">Pays</label>
                <input type="text" name="country" class="form-control" placeholder="CI" value="{{ request('country') }}" maxlength="2">
            </div>
            <div class="col-md-1">
                <label class="form-label">Par page</label>
                <select name="per_page" class="form-select">
                    @foreach([20, 50, 100] as $n)
                        <option value="{{ $n }}" @selected((int) request('per_page', 20) === $n)>{{ $n }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1">Filtrer</button>
                @if($company->hasGatewayCredentials())
                    <a href="{{ route($exportRoute, $exportQuery) }}" class="btn btn-soft-secondary" title="Exporter CSV">
                        <i class="bx bx-download"></i>
                    </a>
                @endif
            </div>
        </form>

        @include('partials.portal.transaction-table', ['items' => $items, 'showPhone' => $type === 'payins'])

        @if(($meta['last_page'] ?? 1) > 1)
            <nav class="d-flex justify-content-between align-items-center mt-4">
                @if(($meta['current_page'] ?? 1) > 1)
                    <a class="btn btn-soft-secondary btn-sm" href="{{ request()->fullUrlWithQuery(['page' => ($meta['current_page'] ?? 1) - 1]) }}">← Précédent</a>
                @else
                    <span></span>
                @endif
                @if(($meta['current_page'] ?? 1) < ($meta['last_page'] ?? 1))
                    <a class="btn btn-soft-secondary btn-sm" href="{{ request()->fullUrlWithQuery(['page' => ($meta['current_page'] ?? 1) + 1]) }}">Suivant →</a>
                @endif
            </nav>
        @endif
    </div>
</div>
@include('partials.portal.date-picker')
@endsection
