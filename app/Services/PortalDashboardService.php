<?php

namespace App\Services;

use App\Support\PortalDateFilter;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PortalDashboardService
{
    /** @var array<string, int|string> */
    public const EMPTY_ACCOUNT_STRIP = [
        'balance_real' => 0,
        'balance_available' => 0,
        'currency' => 'XOF',
        'payin_success' => 0,
        'payin_failed' => 0,
        'payin_volume_net' => 0,
        'payout_volume' => 0,
    ];

    public function resolvePeriodA(Request $request): array
    {
        $period = (string) $request->query('period', '30');
        $from = $request->query('from');
        $to = $request->query('to');

        if ($period === 'custom' && $from && $to) {
            return $this->resolvePeriodFromQuery('custom', $from, $to, 'Période analysée');
        }

        if (in_array($period, ['7', '30', '90'], true)) {
            return $this->resolvePeriodFromQuery($period, null, null, 'Période analysée');
        }

        return $this->resolvePeriodFromQuery('30', null, null, 'Période analysée');
    }

    public function resolvePeriodB(Request $request, array $periodA): ?array
    {
        if (! $request->boolean('compare')) {
            return null;
        }

        $mode = (string) $request->query('period2_mode', 'previous');

        if ($mode === 'custom' && $request->filled('from2') && $request->filled('to2')) {
            return $this->resolvePeriodFromQuery(
                'custom',
                $request->query('from2'),
                $request->query('to2'),
                'Période comparée',
            );
        }

        $days = max(1, (int) $periodA['days']);
        $to = $periodA['from']->copy()->subDay()->endOfDay();
        $from = $to->copy()->subDays($days - 1)->startOfDay();

        return $this->finalizePeriod(
            $from,
            $to,
            'previous',
            'Période précédente ('.$days.' j)',
            'Période comparée',
        );
    }

    /**
     * Bandeau KPI header : soldes et totaux globaux (hors filtre de période).
     *
     * @return array{
     *     balance_real: int,
     *     balance_available: int,
     *     currency: string,
     *     payin_success: int,
     *     payin_failed: int,
     *     payin_volume_net: int,
     *     payout_volume: int
     * }
     */
    public function buildAccountStrip(AggregPayGatewayClient $client): array
    {
        $account = $client->account()['data'] ?? [];
        $payinStats = $client->paymentStats([])['data'] ?? [];
        $payoutStats = $client->payoutStats([])['data'] ?? [];

        return [
            'balance_real' => (int) ($account['balance_real'] ?? 0),
            'balance_available' => (int) ($account['balance_available'] ?? 0),
            'currency' => (string) ($account['currency'] ?? 'XOF'),
            'payin_success' => (int) ($payinStats['success'] ?? 0),
            'payin_failed' => (int) ($payinStats['failed'] ?? 0),
            'payin_volume_net' => (int) ($payinStats['net_success'] ?? 0),
            'payout_volume' => (int) ($payoutStats['debited_success'] ?? $payoutStats['volume_success'] ?? 0),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function build(AggregPayGatewayClient $client, array $periodA, ?array $periodB = null): array
    {
        $payinResp = $client->listPayments(['per_page' => 100]);
        $payins = $this->extractItems($payinResp);
        $payinMeta = $this->extractMeta($payinResp, $payins);

        $payoutResp = $client->listPayouts(['per_page' => 100]);
        $payouts = $this->extractItems($payoutResp);
        $payoutMeta = $this->extractMeta($payoutResp, $payouts);

        $payinsA = $this->filterByPeriod($payins, $periodA['from'], $periodA['to']);
        $payoutsA = $this->filterByPeriod($payouts, $periodA['from'], $periodA['to']);
        $statsA = $this->computePeriodStats($payinsA);
        $payoutStatsA = $this->computePayoutPeriodStats($payoutsA);

        $result = [
            'stats' => $this->computeStats($payins, $payouts, $payinMeta, $payoutMeta),
            'period_a' => [
                'filter' => $periodA,
                'stats' => $statsA,
                'payout_stats' => $payoutStatsA,
                'overview' => $this->buildPeriodOverview($statsA, $payoutStatsA),
                'operators' => $this->operatorRanking($payinsA, 5),
            ],
            'chart' => [
                'mode' => 'single',
                'single' => $this->chartDailyVolume($payinsA, $periodA),
            ],
            'comparison' => null,
            'period_b' => null,
            'recent' => array_slice($payins, 0, 10),
            'recent_payouts' => array_slice($payouts, 0, 5),
        ];

        if ($periodB === null) {
            return $result;
        }

        $payinsB = $this->filterByPeriod($payins, $periodB['from'], $periodB['to']);
        $payoutsB = $this->filterByPeriod($payouts, $periodB['from'], $periodB['to']);
        $statsB = $this->computePeriodStats($payinsB);
        $payoutStatsB = $this->computePayoutPeriodStats($payoutsB);

        $result['period_b'] = [
            'filter' => $periodB,
            'stats' => $statsB,
            'payout_stats' => $payoutStatsB,
            'overview' => $this->buildPeriodOverview($statsB, $payoutStatsB),
            'operators' => $this->operatorRanking($payinsB, 5),
        ];
        $result['comparison'] = $this->buildComparison($periodA, $statsA, $periodB, $statsB);
        $result['chart'] = [
            'mode' => 'compare',
            'compare' => $this->chartCompareDaily($payinsA, $periodA, $payinsB, $periodB),
        ];

        return $result;
    }

    /**
     * Courbe unique : volume encaissé jour par jour.
     *
     * @param  list<array<string, mixed>>  $payins
     * @return array{labels: list<string>, volume: list<int>, success_count: list<int>, title: string}
     */
    private function chartDailyVolume(array $payins, array $period): array
    {
        $series = $this->dailyVolumeSeries($payins, $period['from'], $period['to']);

        return [
            'title' => 'Net encaissé par jour',
            'labels' => $series['labels'],
            'volume' => $series['volumes'],
            'success_count' => $series['counts'],
        ];
    }

    /**
     * Deux courbes alignées sur J1…Jn (même échelle de temps).
     *
     * @param  list<array<string, mixed>>  $payinsA
     * @param  list<array<string, mixed>>  $payinsB
     * @return array{labels: list<string>, series: list<array{name: string, data: list<int>}>}
     */
    private function chartCompareDaily(array $payinsA, array $periodA, array $payinsB, array $periodB): array
    {
        $dataA = $this->dailyVolumeSeries($payinsA, $periodA['from'], $periodA['to'])['volumes'];
        $dataB = $this->dailyVolumeSeries($payinsB, $periodB['from'], $periodB['to'])['volumes'];

        $length = max(count($dataA), count($dataB));
        $labels = [];
        for ($i = 1; $i <= $length; $i++) {
            $labels[] = 'J'.$i;
        }

        return [
            'title' => 'Comparaison du net encaissé',
            'subtitle' => 'J1 = premier jour de chaque période',
            'labels' => $labels,
            'series' => [
                ['name' => $periodA['label'], 'data' => $this->padSeries($dataA, $length)],
                ['name' => $periodB['label'], 'data' => $this->padSeries($dataB, $length)],
            ],
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $payins
     * @return array{labels: list<string>, volumes: list<int>, counts: list<int>}
     */
    private function dailyVolumeSeries(array $payins, Carbon $from, Carbon $to): array
    {
        $buckets = $this->buildDayBuckets($from, $to);
        $volumes = [];
        $counts = [];
        foreach ($buckets as $bucket) {
            $volumes[$bucket['key']] = 0;
            $counts[$bucket['key']] = 0;
        }

        foreach ($payins as $row) {
            if (empty($row['created_at']) || strtoupper((string) ($row['status'] ?? '')) !== 'SUCCESS') {
                continue;
            }
            $key = Carbon::parse($row['created_at'])->format('Y-m-d');
            if (isset($volumes[$key])) {
                $volumes[$key] += $this->payinNetAmount($row);
                $counts[$key]++;
            }
        }

        return [
            'labels' => array_column($buckets, 'label'),
            'volumes' => array_values($volumes),
            'counts' => array_values($counts),
        ];
    }

    /**
     * @return list<array{key: string, label: string, volume: int}>
     */
    private function buildDayBuckets(Carbon $from, Carbon $to): array
    {
        $buckets = [];
        $cursor = $from->copy()->startOfDay();
        $end = $to->copy()->startOfDay();

        while ($cursor->lte($end)) {
            $key = $cursor->format('Y-m-d');
            $buckets[$key] = [
                'key' => $key,
                'label' => $cursor->locale('fr')->isoFormat('D/M'),
                'volume' => 0,
            ];
            $cursor->addDay();
        }

        return array_values($buckets);
    }

    /**
     * @param  list<int>  $data
     * @return list<int>
     */
    private function padSeries(array $data, int $length): array
    {
        $out = array_values($data);
        while (count($out) < $length) {
            $out[] = 0;
        }

        return array_slice($out, 0, $length);
    }

    /**
     * @param  array{label: string}  $periodA
     * @param  array<string, int|float>  $statsA
     * @param  array{label: string}  $periodB
     * @param  array<string, int|float>  $statsB
     * @return array<string, mixed>
     */
    private function buildComparison(array $periodA, array $statsA, array $periodB, array $statsB): array
    {
        $volA = (int) $statsA['volume_success'];
        $volB = (int) $statsB['volume_success'];
        $winner = $this->resolveWinner($statsA, $statsB);
        $volume = $this->describeVolumeComparison($volA, $volB);

        return [
            'winner' => $winner,
            'volume_delta' => $volume['delta'],
            'volume_lines' => $volume['lines'],
        ];
    }

    /**
     * @return array{delta: ?float, lines: list<string>}
     */
    private function describeVolumeComparison(int $volA, int $volB): array
    {
        $fmt = static fn (int $amount): string => number_format($amount, 0, ',', ' ');

        if ($volA === $volB) {
            if ($volA === 0) {
                return [
                    'delta' => null,
                    'lines' => ['Aucun encaissement sur les deux périodes (0 XOF).'],
                ];
            }

            return [
                'delta' => 0.0,
                'lines' => ["Même net encaissé : {$fmt($volA)} XOF sur chaque période."],
            ];
        }

        if ($volB === 0 && $volA > 0) {
            return [
                'delta' => 100.0,
                'lines' => [
                    "Période analysée (net) : {$fmt($volA)} XOF",
                    'Période comparée (net) : 0 XOF (aucun payin réussi)',
                    'La période comparée n’avait aucun encaissement — pas de comparaison en % possible.',
                ],
            ];
        }

        if ($volA === 0 && $volB > 0) {
            return [
                'delta' => -100.0,
                'lines' => [
                    'Période analysée (net) : 0 XOF (aucun payin réussi)',
                    "Période comparée (net) : {$fmt($volB)} XOF",
                    'La période analysée n’avait aucun encaissement — pas de comparaison en % possible.',
                ],
            ];
        }

        $delta = $this->percentDelta($volA, $volB);
        $sign = $delta >= 0 ? '+' : '';

        return [
            'delta' => $delta,
            'lines' => [
                "Période analysée (net) : {$fmt($volA)} XOF",
                "Période comparée (net) : {$fmt($volB)} XOF",
                "Écart : {$sign}{$delta} % par rapport à la période comparée",
            ],
        ];
    }

    /**
     * @param  array<string, int|float>  $statsA
     * @param  array<string, int|float>  $statsB
     * @return array{side: string, label: string, reason: string}
     */
    private function resolveWinner(array $statsA, array $statsB): array
    {
        $volA = (int) $statsA['volume_success'];
        $volB = (int) $statsB['volume_success'];
        $fmt = static fn (int $amount): string => number_format($amount, 0, ',', ' ');

        if ($volA > $volB) {
            return [
                'side' => 'a',
                'label' => 'Période analysée',
                'reason' => "{$fmt($volA)} XOF net encaissés contre {$fmt($volB)} XOF sur la période comparée",
            ];
        }
        if ($volB > $volA) {
            return [
                'side' => 'b',
                'label' => 'Période comparée',
                'reason' => "{$fmt($volB)} XOF net encaissés contre {$fmt($volA)} XOF sur la période analysée",
            ];
        }

        $rateA = (float) $statsA['success_rate'];
        $rateB = (float) $statsB['success_rate'];

        if ($rateA > $rateB) {
            return ['side' => 'a', 'label' => 'Période analysée', 'reason' => 'meilleur taux de succès'];
        }
        if ($rateB > $rateA) {
            return ['side' => 'b', 'label' => 'Période comparée', 'reason' => 'meilleur taux de succès'];
        }

        return ['side' => 'tie', 'label' => 'Égalité', 'reason' => 'même performance'];
    }

    private function percentDelta(float|int $current, float|int $baseline): ?float
    {
        if ($baseline == 0) {
            return $current > 0 ? 100.0 : null;
        }

        return round((($current - $baseline) / $baseline) * 100, 1);
    }

    private function resolvePeriodFromQuery(string $period, mixed $fromInput, mixed $toInput, string $shortPrefix): array
    {
        if ($period === 'custom' && $fromInput && $toInput) {
            $from = PortalDateFilter::parse($fromInput);
            $to = PortalDateFilter::parse($toInput, endOfDay: true);

            if ($from === null || $to === null) {
                return $this->resolvePeriodFromQuery('30', null, null, $shortPrefix);
            }

            if ($from->gt($to)) {
                [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
            }
            $label = $from->locale('fr')->isoFormat('D MMM').' — '.$to->locale('fr')->isoFormat('D MMM Y');

            return $this->finalizePeriod($from, $to, 'custom', $label, $shortPrefix);
        }

        $days = match ($period) {
            '7' => 7,
            '90' => 90,
            default => 30,
        };
        $period = match ($period) {
            '7', '90' => $period,
            default => '30',
        };
        $from = Carbon::today()->subDays($days - 1)->startOfDay();
        $to = Carbon::today()->endOfDay();
        $label = match ($period) {
            '7' => '7 derniers jours',
            '90' => '90 derniers jours',
            default => '30 derniers jours',
        };

        return $this->finalizePeriod($from, $to, $period, $label, $shortPrefix);
    }

    /**
     * @return array{from: Carbon, to: Carbon, period: string, from_input: string, to_input: string, label: string, short_label: string, days: int}
     */
    private function finalizePeriod(Carbon $from, Carbon $to, string $period, string $label, string $shortLabel): array
    {
        if ($from->diffInDays($to) > 366) {
            $from = $to->copy()->subDays(366)->startOfDay();
        }

        $days = (int) $from->copy()->startOfDay()->diffInDays($to->copy()->startOfDay()) + 1;

        return [
            'from' => $from,
            'to' => $to,
            'period' => $period,
            'from_input' => $from->format('d/m/Y'),
            'to_input' => $to->format('d/m/Y'),
            'label' => $label,
            'short_label' => $shortLabel,
            'days' => $days,
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function extractItems(array $response): array
    {
        $data = $response['data'] ?? [];

        if (isset($data['data']) && is_array($data['data'])) {
            return $data['data'];
        }

        return is_array($data) ? array_values(array_filter($data, 'is_array')) : [];
    }

    private function extractMeta(array $response, array $items): array
    {
        $data = $response['data'] ?? [];

        return [
            'total' => (int) ($data['total'] ?? count($items)),
            'current_page' => (int) ($data['current_page'] ?? 1),
            'last_page' => (int) ($data['last_page'] ?? 1),
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $items
     * @return list<array<string, mixed>>
     */
    private function filterByPeriod(array $items, Carbon $from, Carbon $to): array
    {
        return array_values(array_filter($items, function (array $row) use ($from, $to) {
            if (empty($row['created_at'])) {
                return false;
            }

            return Carbon::parse($row['created_at'])->between($from, $to);
        }));
    }

    /**
     * @return array<string, int|float>
     */
    private function computeStats(array $payins, array $payouts, array $payinMeta, array $payoutMeta): array
    {
        $stats = [
            'payins_total' => $payinMeta['total'],
            'payouts_total' => $payoutMeta['total'],
            'pending' => 0,
            'success' => 0,
            'failed' => 0,
            'cancelled' => 0,
            'volume_success' => 0,
            'success_rate' => 0,
        ];

        foreach ($payins as $row) {
            $st = strtoupper((string) ($row['status'] ?? ''));
            if ($st === 'PENDING') {
                $stats['pending']++;
            } elseif ($st === 'SUCCESS') {
                $stats['success']++;
            } elseif ($st === 'FAILED') {
                $stats['failed']++;
            } elseif ($st === 'CANCELLED') {
                $stats['cancelled']++;
            }
            if ($st === 'SUCCESS') {
                $stats['volume_success'] += $this->payinNetAmount($row);
            }
        }

        $closed = $stats['success'] + $stats['failed'];
        $stats['success_rate'] = $closed > 0
            ? round(($stats['success'] / $closed) * 100, 1)
            : 0;

        return $stats;
    }

    /**
     * @param  list<array<string, mixed>>  $payins
     * @return array<string, int|float>
     */
    private function computePeriodStats(array $payins): array
    {
        $stats = [
            'total' => count($payins),
            'success' => 0,
            'failed' => 0,
            'pending' => 0,
            'volume_success' => 0,
            'success_rate' => 0,
        ];

        foreach ($payins as $row) {
            $st = strtoupper((string) ($row['status'] ?? ''));
            if ($st === 'SUCCESS') {
                $stats['success']++;
                $stats['volume_success'] += $this->payinNetAmount($row);
            } elseif ($st === 'FAILED') {
                $stats['failed']++;
            } elseif ($st === 'PENDING') {
                $stats['pending']++;
            }
        }

        $closed = $stats['success'] + $stats['failed'];
        $stats['success_rate'] = $closed > 0
            ? round(($stats['success'] / $closed) * 100, 1)
            : 0;

        return $stats;
    }

    /**
     * @param  list<array<string, mixed>>  $payouts
     * @return array<string, int|float>
     */
    private function computePayoutPeriodStats(array $payouts): array
    {
        $stats = [
            'total' => count($payouts),
            'success' => 0,
            'failed' => 0,
            'pending' => 0,
            'volume_success' => 0,
        ];

        foreach ($payouts as $row) {
            $st = strtoupper((string) ($row['status'] ?? ''));
            if ($st === 'SUCCESS') {
                $stats['success']++;
                $stats['volume_success'] += $this->payoutDebitedAmount($row);
            } elseif ($st === 'FAILED') {
                $stats['failed']++;
            } elseif ($st === 'PENDING') {
                $stats['pending']++;
            }
        }

        return $stats;
    }

    /**
     * @param  array<string, int|float>  $payinStats
     * @param  array<string, int|float>  $payoutStats
     * @return array<string, int|float>
     */
    private function buildPeriodOverview(array $payinStats, array $payoutStats): array
    {
        $payinVolume = (int) $payinStats['volume_success'];
        $payoutVolume = (int) $payoutStats['volume_success'];

        return [
            'payin_volume' => $payinVolume,
            'payout_volume' => $payoutVolume,
            'net_flow' => $payinVolume - $payoutVolume,
            'payin_count' => (int) $payinStats['total'],
            'payin_success' => (int) $payinStats['success'],
            'payin_failed' => (int) $payinStats['failed'],
            'payin_pending' => (int) $payinStats['pending'],
            'payout_count' => (int) $payoutStats['total'],
            'payout_success' => (int) $payoutStats['success'],
            'success_rate' => (float) $payinStats['success_rate'],
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $payins
     * @return list<array{operator: string, count: int, volume: int}>
     */
    private function operatorRanking(array $payins, int $limit = 5): array
    {
        $ranked = [];

        foreach ($payins as $row) {
            if (strtoupper((string) ($row['status'] ?? '')) !== 'SUCCESS') {
                continue;
            }
            $operator = (string) ($row['operator'] ?? 'Inconnu');
            if (! isset($ranked[$operator])) {
                $ranked[$operator] = ['operator' => $operator, 'count' => 0, 'volume' => 0];
            }
            $ranked[$operator]['count']++;
            $ranked[$operator]['volume'] += $this->payinNetAmount($row);
        }

        usort($ranked, fn ($a, $b) => $b['volume'] <=> $a['volume']);

        return array_slice(array_values($ranked), 0, $limit);
    }

    /** Montant net crédité au partenaire après frais opérateur. */
    private function payinNetAmount(array $row): int
    {
        return (int) ($row['net_amount'] ?? $row['amount'] ?? 0);
    }

    /** Montant total débité du compte partenaire (payout + frais). */
    private function payoutDebitedAmount(array $row): int
    {
        if (isset($row['total_debited'])) {
            return (int) $row['total_debited'];
        }

        $amount = (int) ($row['amount'] ?? 0);

        return $amount + (int) ($row['fee_amount'] ?? 0);
    }
}
