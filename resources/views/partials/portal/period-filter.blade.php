@props([
    'period' => '30',
    'fromInput' => '',
    'toInput' => '',
    'formId' => 'portal-filter-form',
    'resetUrl' => null,
])

@php
    $currentPeriod = in_array($period, ['7', '30', '90', 'custom'], true) ? $period : '30';
    $showCustom = $currentPeriod === 'custom';
    $resetUrl = $resetUrl ?? route('dashboard');
@endphp

<div class="portal-period-filter">
    <div class="row g-2 align-items-end">
        <div class="col-md-3">
            <label class="form-label font-size-12 text-muted mb-1" for="period-select">Période</label>
            <select name="period" id="period-select" class="form-select form-select-sm">
                <option value="7" @selected($currentPeriod === '7')>7 derniers jours</option>
                <option value="30" @selected($currentPeriod === '30')>30 derniers jours</option>
                <option value="90" @selected($currentPeriod === '90')>90 derniers jours</option>
                <option value="custom" @selected($currentPeriod === 'custom')>Personnalisée</option>
            </select>
        </div>
        <div id="custom-period-dates" class="col-md-6 @if(!$showCustom) d-none @endif">
            <div class="row g-2 portal-date-range">
                <div class="col-6">
                    <label class="form-label font-size-12 text-muted mb-1">Du</label>
                    <div class="portal-date-wrap">
                        <input type="text" name="from" class="form-control form-control-sm portal-date-input portal-date-from" placeholder="JJ/MM/AAAA" inputmode="numeric" maxlength="10" autocomplete="off" value="{{ $fromInput }}" @disabled(!$showCustom)>
                        <i class="bx bx-calendar portal-date-icon" aria-hidden="true"></i>
                    </div>
                </div>
                <div class="col-6">
                    <label class="form-label font-size-12 text-muted mb-1">Au</label>
                    <div class="portal-date-wrap">
                        <input type="text" name="to" class="form-control form-control-sm portal-date-input portal-date-to" placeholder="JJ/MM/AAAA" inputmode="numeric" maxlength="10" autocomplete="off" value="{{ $toInput }}" @disabled(!$showCustom)>
                        <i class="bx bx-calendar portal-date-icon" aria-hidden="true"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 d-flex gap-2">
            <button type="submit" class="btn btn-primary btn-sm flex-grow-1">Appliquer</button>
            <a href="{{ $resetUrl }}" class="btn btn-soft-secondary btn-sm">Réinitialiser</a>
        </div>
    </div>
</div>

@once
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var select = document.getElementById('period-select');
                var customBlock = document.getElementById('custom-period-dates');
                if (!select || !customBlock) {
                    return;
                }

                function toggleCustomPeriod() {
                    var isCustom = select.value === 'custom';
                    customBlock.classList.toggle('d-none', !isCustom);
                    customBlock.querySelectorAll('input').forEach(function (input) {
                        input.disabled = !isCustom;
                    });
                }

                select.addEventListener('change', toggleCustomPeriod);
                toggleCustomPeriod();
            });
        </script>
    @endpush
@endonce
