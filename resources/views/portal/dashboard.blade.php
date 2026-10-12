@extends('layouts.portal')

@section('title', 'Tableau de bord')

@section('kpi_strip')
    <div class="row portal-page-head">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div>
                    <h4 class="mb-1 sub-title text-white">{{ $company->name }}</h4>
                    <span class="portal-period-tag"><i class="bx bx-pulse"></i> Compte · temps réel</span>
                </div>
                <ol class="breadcrumb m-0"><li class="breadcrumb-item active text-white-50">Tableau de bord</li></ol>
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
                                <p class="stat-label mb-0">Solde réel</p>
                                <h3 class="stat-value mb-0">{{ number_format($accountStrip['balance_real'], 0, ',', ' ') }}</h3>
                                <p class="stat-sub mb-0">{{ $accountStrip['currency'] }} · total crédité</p>
                            </div>
                        </div>
                        <div class="col">
                            <div class="py-3 px-4 mx-2 portal-stat-mini">
                                <div class="stat-icon"><i class="bx bx-lock-open"></i></div>
                                <p class="stat-label mb-0">Solde disponible</p>
                                <h3 class="stat-value mb-0">{{ number_format($accountStrip['balance_available'], 0, ',', ' ') }}</h3>
                                <p class="stat-sub mb-0">{{ $accountStrip['currency'] }} · utilisable en payout</p>
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
    <div class="alert alert-warning">Clé API non configurée. <a href="{{ route('api.credentials') }}">Configurer</a></div>
@elseif($error)
    <div class="alert alert-danger">{{ $error }}</div>
@endif

<div class="card mb-3">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('dashboard') }}" id="portal-filter-form">
            <label class="form-label fw-medium mb-2">Période à afficher</label>
            @include('partials.portal.period-filter', [
                'period' => $periodA['period'] ?? '30',
                'fromInput' => $periodA['from_input'] ?? '',
                'toInput' => $periodA['to_input'] ?? '',
            ])

            <div class="row g-3 align-items-end mt-2">
                <div class="col-lg-8">
                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" name="compare" value="1" id="compare-toggle" @checked($compareEnabled)>
                        <label class="form-check-label fw-medium" for="compare-toggle">Comparer deux périodes</label>
                    </div>
                    <p class="text-muted font-size-12 mb-0 mt-1">Activé : deux courbes + détails comparatifs.</p>
                </div>
            </div>

            <div id="compare-options" class="mt-3 pt-3 border-top @if(!$compareEnabled) d-none @endif">
                <label class="form-label fw-medium text-success mb-2">Période de comparaison</label>
                <div class="row g-2 align-items-end">
                    <div class="col-md-4">
                        <select name="period2_mode" class="form-select form-select-sm" id="period2-mode">
                            <option value="previous" @selected(request('period2_mode', 'previous') === 'previous')>Même durée, juste avant</option>
                            <option value="custom" @selected(request('period2_mode') === 'custom')>Autres dates</option>
                        </select>
                    </div>
                    <div id="period2-dates" class="col-md-8 @if(request('period2_mode') !== 'custom') d-none @endif">
                        <div class="row g-2 portal-date-range">
                            <div class="col-6">
                                <label class="form-label font-size-12 text-muted mb-1">Du</label>
                                <div class="portal-date-wrap">
                                    <input type="text" name="from2" class="form-control form-control-sm portal-date-input portal-date-from" placeholder="JJ/MM/AAAA" inputmode="numeric" maxlength="10" autocomplete="off" value="{{ $periodB['from_input'] ?? '' }}">
                                    <i class="bx bx-calendar portal-date-icon" aria-hidden="true"></i>
                                </div>
                            </div>
                            <div class="col-6">
                                <label class="form-label font-size-12 text-muted mb-1">Au</label>
                                <div class="portal-date-wrap">
                                    <input type="text" name="to2" class="form-control form-control-sm portal-date-input portal-date-to" placeholder="JJ/MM/AAAA" inputmode="numeric" maxlength="10" autocomplete="off" value="{{ $periodB['to_input'] ?? '' }}">
                                    <i class="bx bx-calendar portal-date-icon" aria-hidden="true"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    @if($compareEnabled && $periodB)
                        <div class="col-12"><span class="text-muted font-size-12"><i class="bx bx-info-circle"></i> {{ $periodB['label'] }}</span></div>
                    @endif
                </div>
            </div>
        </form>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="{{ $compareEnabled ? 'col-lg-8' : 'col-lg-8' }}">
        <div class="card h-100">
            <div class="card-body">
                @if($compareEnabled)
                    <h5 class="card-title mb-1">Comparaison des périodes</h5>
                    <p class="text-muted font-size-12 mb-3">Bleu = période analysée · Orange = période comparée</p>
                @else
                    <h5 class="card-title mb-1">Évolution quotidienne</h5>
                    <p class="text-muted font-size-12 mb-3">{{ $periodA['label'] }} — net encaissé et nombre de payins réussis</p>
                @endif
                <div id="portal-main-chart" style="min-height:320px"></div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        @if(!$compareEnabled)
            <div class="card h-100 mb-0">
                <div class="card-body portal-status-breakdown">
                    <h5 class="card-title mb-3">Répartition payins</h5>
                    @php
                        $totalStatus = max(1, $overviewA['payin_success'] + $overviewA['payin_failed'] + $overviewA['payin_pending']);
                        $pctSuccess = round(($overviewA['payin_success'] / $totalStatus) * 100);
                        $pctFailed = round(($overviewA['payin_failed'] / $totalStatus) * 100);
                        $pctPending = 100 - $pctSuccess - $pctFailed;
                    @endphp
                    <div class="progress mb-3">
                        @if($pctSuccess > 0)<div class="progress-bar progress-bar-success" style="width: {{ $pctSuccess }}%"></div>@endif
                        @if($pctPending > 0)<div class="progress-bar progress-bar-warning" style="width: {{ $pctPending }}%"></div>@endif
                        @if($pctFailed > 0)<div class="progress-bar progress-bar-danger" style="width: {{ $pctFailed }}%"></div>@endif
                    </div>
                    <ul class="list-unstyled mb-4 portal-period-stats">
                        <li class="d-flex justify-content-between py-2 border-bottom">
                            <span><i class="bx bx-check-circle text-success me-1"></i> Réussis</span>
                            <strong>{{ $overviewA['payin_success'] }}</strong>
                        </li>
                        <li class="d-flex justify-content-between py-2 border-bottom">
                            <span><i class="bx bx-time-five text-warning me-1"></i> En attente</span>
                            <strong>{{ $overviewA['payin_pending'] }}</strong>
                        </li>
                        <li class="d-flex justify-content-between py-2">
                            <span><i class="bx bx-x-circle text-danger me-1"></i> Échoués</span>
                            <strong>{{ $overviewA['payin_failed'] }}</strong>
                        </li>
                    </ul>

                    @if(count($operatorsA) > 0)
                        <h6 class="text-muted text-uppercase font-size-11 mb-2">Top opérateurs</h6>
                        @foreach($operatorsA as $i => $op)
                            <div class="portal-operator-row">
                                <span>
                                    <span class="portal-operator-rank">{{ $i + 1 }}</span>
                                    {{ $op['operator'] }}
                                </span>
                                <span class="text-end">
                                    <strong>{{ number_format($op['volume'], 0, ',', ' ') }}</strong>
                                    <span class="text-muted font-size-12 d-block">{{ $op['count'] }} tx</span>
                                </span>
                            </div>
                        @endforeach
                    @else
                        <p class="text-muted font-size-12 mb-0">Aucun payin réussi sur cette période.</p>
                    @endif
                </div>
            </div>
        @else
            @php $w = $comparison['winner'] ?? null; @endphp
            @if($w && $w['side'] !== 'tie')
                <div class="portal-compare-alert rounded p-3 mb-3 font-size-13">
                    <div class="d-flex align-items-start gap-2">
                        <i class="mdi mdi-trophy-outline font-size-18 mt-1"></i>
                        <div>
                            <strong>{{ $w['label'] }}</strong> performe mieux sur le net encaissé
                            <span class="d-block small mt-1 opacity-75">{{ $w['reason'] }}</span>
                        </div>
                    </div>
                </div>
            @endif
            @if(!empty($comparison['volume_lines']))
                <div class="portal-compare-detail rounded p-3 mb-3 font-size-12">
                    <p class="mb-2 fw-medium">Détail du net encaissé</p>
                    <ul class="list-unstyled mb-0 portal-compare-detail-lines">
                        @foreach($comparison['volume_lines'] as $line)
                            <li>{{ $line }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <div class="d-flex flex-column gap-3">
                @include('partials.portal.period-summary', [
                    'title' => 'Période analysée',
                    'label' => $periodA['label'],
                    'stats' => $periodStatsA,
                    'color' => 'primary',
                    'highlight' => ($w['side'] ?? '') === 'a',
                ])
                @if($periodStatsB)
                    @include('partials.portal.period-summary', [
                        'title' => 'Période comparée',
                        'label' => $periodB['label'] ?? '',
                        'stats' => $periodStatsB,
                        'color' => 'portal-secondary',
                        'highlight' => ($w['side'] ?? '') === 'b',
                    ])
                @endif
            </div>
        @endif
    </div>
</div>

<div class="row g-3">
    <div class="col-xl-8">
        <div class="card">
            <div class="card-header border-0 d-flex align-items-center">
                <h5 class="card-title mb-0 flex-grow-1">Derniers payins</h5>
                <a href="{{ route('transactions.payins') }}" class="btn btn-soft-primary btn-sm">Voir tout</a>
            </div>
            <div class="card-body pt-0">@include('partials.portal.transaction-table', ['items' => $recent, 'showPhone' => true])</div>
        </div>
    </div>
    <div class="col-xl-4">
        <div class="card">
            <div class="card-header border-0 d-flex align-items-center">
                <h5 class="card-title mb-0 flex-grow-1">Derniers payouts</h5>
                <a href="{{ route('transactions.payouts') }}" class="btn btn-soft-primary btn-sm">Voir tout</a>
            </div>
            <div class="card-body pt-0">@include('partials.portal.transaction-table', ['items' => $recentPayouts])</div>
        </div>
    </div>
</div>
@include('partials.portal.date-picker')
@endsection

@push('scripts')
<script src="{{ asset('theme/assets/libs/apexcharts/apexcharts.min.js') }}"></script>
<script>window.portalChart = @json($chart);</script>
<script src="{{ asset('theme/portal-dashboard.js') }}?v=3"></script>
<script>
(function () {
    var compareToggle = document.getElementById('compare-toggle');
    var compareOptions = document.getElementById('compare-options');
    var period2Mode = document.getElementById('period2-mode');
    var period2Dates = document.getElementById('period2-dates');

    compareToggle?.addEventListener('change', function () {
        compareOptions?.classList.toggle('d-none', !this.checked);
    });

    period2Mode?.addEventListener('change', function () {
        period2Dates?.classList.toggle('d-none', this.value !== 'custom');
    });
})();
</script>
@endpush
