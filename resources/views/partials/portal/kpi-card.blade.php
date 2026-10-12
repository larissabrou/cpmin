@props([
    'label',
    'value',
    'sub' => null,
    'icon' => 'bx-bar-chart',
    'accent' => 'primary',
    'badge' => null,
    'badgeClass' => 'bg-primary-subtle text-primary',
])

<div class="portal-kpi-card portal-kpi-{{ $accent }}">
    <div class="portal-kpi-icon">
        <i class="bx {{ $icon }}"></i>
    </div>
    <div class="portal-kpi-body">
        <p class="portal-kpi-label">{{ $label }}</p>
        <h3 class="portal-kpi-value">{{ $value }}</h3>
        @if($sub)
            <p class="portal-kpi-sub">{{ $sub }}</p>
        @endif
        @if($badge)
            <span class="badge {{ $badgeClass }} portal-kpi-badge">{{ $badge }}</span>
        @endif
    </div>
</div>
