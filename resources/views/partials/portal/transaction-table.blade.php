@props(['items' => [], 'showPhone' => false])

@php
    $colspan = ($showPhone ? 9 : 8);
@endphp

<div class="table-responsive">
    <table class="table table-hover table-nowrap align-middle mb-0 table-transactions">
        <thead class="table-light">
        <tr>
            <th>Référence</th>
            <th>Montant</th>
            <th>Taux</th>
            <th>Frais</th>
            <th>Pays</th>
            <th>Opérateur</th>
            @if($showPhone)<th>Téléphone</th>@endif
            <th>Statut</th>
            <th>Date</th>
        </tr>
        </thead>
        <tbody>
        @forelse($items as $row)
            @php
                $feeRate = $row['fee_rate_percent'] ?? null;
                $feeAmount = array_key_exists('fee_amount', $row) ? (int) $row['fee_amount'] : null;
            @endphp
            <tr>
                <td><code>{{ $row['order_id'] ?? '—' }}</code></td>
                <td class="fw-semibold">{{ number_format((int) ($row['amount'] ?? 0), 0, ',', ' ') }} {{ $row['currency'] ?? 'XOF' }}</td>
                <td class="text-muted">
                    @if($feeRate !== null && $feeRate !== '')
                        {{ rtrim(rtrim(number_format((float) $feeRate, 2, ',', ' '), '0'), ',') }} %
                    @else
                        —
                    @endif
                </td>
                <td class="text-muted">
                    @if($feeAmount !== null)
                        {{ number_format($feeAmount, 0, ',', ' ') }} {{ $row['currency'] ?? 'XOF' }}
                    @else
                        —
                    @endif
                </td>
                <td>{{ $row['country'] ?? '—' }}</td>
                <td>{{ $row['operator'] ?? '—' }}</td>
                @if($showPhone)<td>{{ $row['phone_number'] ?? '—' }}</td>@endif
                <td>@include('partials.portal.status-badge', ['status' => $row['status'] ?? 'PENDING'])</td>
                <td class="text-muted">
                    {{ isset($row['created_at']) ? \Carbon\Carbon::parse($row['created_at'])->locale('fr')->isoFormat('D MMM Y HH:mm') : '—' }}
                </td>
            </tr>
        @empty
            <tr><td colspan="{{ $colspan }}" class="text-center text-muted py-4">Aucune transaction.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
