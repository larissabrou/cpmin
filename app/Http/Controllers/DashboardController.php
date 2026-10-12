<?php

namespace App\Http\Controllers;

use App\Services\AggregPayGatewayClient;
use App\Services\PortalDashboardService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /** @var array<string, int|float> */
    private const EMPTY_OVERVIEW = [
        'payin_volume' => 0,
        'payout_volume' => 0,
        'net_flow' => 0,
        'payin_count' => 0,
        'payin_success' => 0,
        'payin_failed' => 0,
        'payin_pending' => 0,
        'payout_count' => 0,
        'payout_success' => 0,
        'success_rate' => 0,
    ];

    public function index(Request $request, PortalDashboardService $dashboard): View
    {
        $company = $request->attributes->get('company');
        $compareEnabled = $request->boolean('compare');
        $periodA = $dashboard->resolvePeriodA($request);
        $periodB = $compareEnabled ? $dashboard->resolvePeriodB($request, $periodA) : null;

        $stats = ['payins_total' => 0, 'payouts_total' => 0, 'pending' => 0, 'success' => 0, 'failed' => 0, 'cancelled' => 0, 'volume_success' => 0, 'success_rate' => 0];
        $periodStatsA = ['total' => 0, 'success' => 0, 'failed' => 0, 'pending' => 0, 'volume_success' => 0, 'success_rate' => 0];
        $payoutStatsA = ['total' => 0, 'success' => 0, 'failed' => 0, 'pending' => 0, 'volume_success' => 0];
        $overviewA = self::EMPTY_OVERVIEW;
        $operatorsA = [];
        $periodStatsB = null;
        $payoutStatsB = null;
        $overviewB = null;
        $operatorsB = [];
        $comparison = null;
        $chart = ['mode' => 'single', 'single' => ['labels' => [], 'volume' => []]];
        $recent = [];
        $recentPayouts = [];
        $accountStrip = PortalDashboardService::EMPTY_ACCOUNT_STRIP;
        $error = null;

        if ($company->hasGatewayCredentials()) {
            try {
                $client = AggregPayGatewayClient::forCompany($company);
                $accountStrip = $dashboard->buildAccountStrip($client);
                $built = $dashboard->build($client, $periodA, $periodB);
                $stats = $built['stats'] ?? $stats;
                $periodAData = $this->extractPeriodBlock($built, 'period_a', $periodA);
                $periodStatsA = $periodAData['stats'];
                $payoutStatsA = $periodAData['payout_stats'];
                $overviewA = $periodAData['overview'];
                $operatorsA = $periodAData['operators'];
                $chart = $built['chart'] ?? $chart;
                $comparison = $built['comparison'] ?? null;
                $recent = $built['recent'] ?? [];
                $recentPayouts = $built['recent_payouts'] ?? [];
                if (! empty($built['period_b'])) {
                    $periodBData = $this->extractPeriodBlock($built, 'period_b', $periodB ?? $periodA);
                    $periodStatsB = $periodBData['stats'];
                    $payoutStatsB = $periodBData['payout_stats'];
                    $overviewB = $periodBData['overview'];
                    $operatorsB = $periodBData['operators'];
                    $periodB = $periodBData['filter'];
                }
            } catch (\Throwable $e) {
                $error = $e->getMessage();
            }
        }

        return view('portal.dashboard', compact(
            'company',
            'stats',
            'periodA',
            'periodB',
            'periodStatsA',
            'periodStatsB',
            'payoutStatsA',
            'payoutStatsB',
            'overviewA',
            'overviewB',
            'operatorsA',
            'operatorsB',
            'comparison',
            'compareEnabled',
            'chart',
            'recent',
            'recentPayouts',
            'accountStrip',
            'error',
        ));
    }

    /**
     * @param  array<string, mixed>  $built
     * @return array{
     *     filter: array<string, mixed>,
     *     stats: array<string, int|float>,
     *     payout_stats: array<string, int>,
     *     overview: array<string, int|float>,
     *     operators: list<array<string, mixed>>
     * }
     */
    private function extractPeriodBlock(array $built, string $key, array $fallbackFilter): array
    {
        $block = is_array($built[$key] ?? null) ? $built[$key] : [];

        $stats = is_array($block['stats'] ?? null) ? $block['stats'] : [];
        $payoutStats = is_array($block['payout_stats'] ?? null) ? $block['payout_stats'] : [];
        $overview = is_array($block['overview'] ?? null) ? $block['overview'] : [];

        if ($overview === [] && $stats !== []) {
            $overview = $this->buildOverviewFromStats($stats, $payoutStats);
        }

        return [
            'filter' => is_array($block['filter'] ?? null) ? $block['filter'] : $fallbackFilter,
            'stats' => array_merge([
                'total' => 0,
                'success' => 0,
                'failed' => 0,
                'pending' => 0,
                'volume_success' => 0,
                'success_rate' => 0,
            ], $stats),
            'payout_stats' => array_merge([
                'total' => 0,
                'success' => 0,
                'failed' => 0,
                'pending' => 0,
                'volume_success' => 0,
            ], $payoutStats),
            'overview' => array_merge(self::EMPTY_OVERVIEW, $overview),
            'operators' => is_array($block['operators'] ?? null) ? $block['operators'] : [],
        ];
    }

    /**
     * Compatibilité avec une ancienne version du service sans clé overview.
     *
     * @param  array<string, int|float>  $stats
     * @param  array<string, int>  $payoutStats
     * @return array<string, int|float>
     */
    private function buildOverviewFromStats(array $stats, array $payoutStats): array
    {
        $payinVolume = (int) ($stats['volume_success'] ?? 0);
        $payoutVolume = (int) ($payoutStats['volume_success'] ?? 0);

        return [
            'payin_volume' => $payinVolume,
            'payout_volume' => $payoutVolume,
            'net_flow' => $payinVolume - $payoutVolume,
            'payin_count' => (int) ($stats['total'] ?? 0),
            'payin_success' => (int) ($stats['success'] ?? 0),
            'payin_failed' => (int) ($stats['failed'] ?? 0),
            'payin_pending' => (int) ($stats['pending'] ?? 0),
            'payout_count' => (int) ($payoutStats['total'] ?? 0),
            'payout_success' => (int) ($payoutStats['success'] ?? 0),
            'success_rate' => (float) ($stats['success_rate'] ?? 0),
        ];
    }
}
