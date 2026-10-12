@props([
    'title',
    'label',
    'stats',
    'color' => 'primary',
    'highlight' => false,
])

@php
    $isPortalSecondary = $color === 'portal-secondary';
    $badgeBg = $isPortalSecondary ? 'bg-portal-secondary' : 'bg-'.$color;
    $borderClass = $isPortalSecondary ? 'border-portal-secondary' : 'border-'.$color;
    $highlightBadge = $isPortalSecondary ? 'bg-portal-secondary text-white' : 'bg-white text-primary';
@endphp

<div class="portal-period-panel border rounded p-3 h-100 {{ $highlight ? $borderClass.' border-2 portal-period-panel--winner' : '' }}">
    <div class="d-flex align-items-center gap-2 mb-2">
        <span class="badge {{ $badgeBg }}">{{ $title }}</span>
        @if($highlight)
            <span class="badge {{ $highlightBadge }}"><i class="mdi mdi-trophy-outline"></i> Meilleure</span>
        @endif
    </div>
    <p class="portal-period-panel-dates font-size-12 mb-3">{{ $label }}</p>
    <ul class="list-unstyled mb-0 portal-period-stats">
        <li class="d-flex justify-content-between py-2 border-bottom">
            <span>Net encaissé</span>
            <strong>{{ number_format($stats['volume_success'], 0, ',', ' ') }} XOF</strong>
        </li>
        <li class="d-flex justify-content-between py-2 border-bottom">
            <span>Taux de succès</span>
            <strong class="portal-stat-success">{{ $stats['success_rate'] }} %</strong>
        </li>
        <li class="d-flex justify-content-between py-2 border-bottom">
            <span>Réussies</span>
            <strong>{{ $stats['success'] }}</strong>
        </li>
        <li class="d-flex justify-content-between py-2">
            <span>Transactions</span>
            <strong>{{ $stats['total'] }}</strong>
        </li>
    </ul>
</div>
