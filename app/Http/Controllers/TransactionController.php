<?php

namespace App\Http\Controllers;

use App\Services\AggregPayGatewayClient;
use App\Support\PortalDateFilter;
use App\Support\TransactionListStats;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TransactionController extends Controller
{
    public function payins(Request $request): View
    {
        return $this->list($request, 'payins');
    }

    public function payouts(Request $request): View
    {
        return $this->list($request, 'payouts');
    }

    public function exportPayins(Request $request): StreamedResponse|RedirectResponse
    {
        return $this->export($request, 'payins');
    }

    public function exportPayouts(Request $request): StreamedResponse|RedirectResponse
    {
        return $this->export($request, 'payouts');
    }

    private function list(Request $request, string $type): View
    {
        $company = $request->attributes->get('company');
        $filters = $this->filtersFromRequest($request);
        $filterDisplay = $this->filterDisplayValues($request);
        $items = [];
        $meta = [];
        $stats = TransactionListStats::fromGatewaySummary([], $type);
        $error = null;

        if ($company->hasGatewayCredentials()) {
            try {
                $client = AggregPayGatewayClient::forCompany($company);
                $response = $type === 'payins'
                    ? $client->listPayments($filters)
                    : $client->listPayouts($filters);
                $payload = $response['data'] ?? [];
                $items = $payload['data'] ?? [];
                $meta = [
                    'current_page' => $payload['current_page'] ?? 1,
                    'last_page' => $payload['last_page'] ?? 1,
                    'total' => $payload['total'] ?? count($items),
                ];

                $statsFilters = $this->statsFiltersFromRequest($filters);
                $stats = $this->loadStats($client, $type, $statsFilters, $meta);
            } catch (\Throwable $e) {
                $error = $e->getMessage();
            }
        }

        return view('portal.transactions', compact('company', 'items', 'meta', 'stats', 'error', 'type', 'filterDisplay'));
    }

    /**
     * @return array{date_from: string, date_to: string}
     */
    private function filterDisplayValues(Request $request): array
    {
        return [
            'date_from' => PortalDateFilter::formatInput($request->query('date_from')),
            'date_to' => PortalDateFilter::formatInput($request->query('date_to')),
        ];
    }

    private function export(Request $request, string $type): StreamedResponse|RedirectResponse
    {
        $company = $request->attributes->get('company');

        if (! $company->hasGatewayCredentials()) {
            return redirect()
                ->route($type === 'payins' ? 'transactions.payins' : 'transactions.payouts')
                ->with('error', 'Clé API non configurée.');
        }

        try {
            $client = AggregPayGatewayClient::forCompany($company);
            $path = $type === 'payins' ? 'payments/export' : 'payouts/export';

            return $client->downloadExport($path, $this->filtersFromRequest($request, forExport: true));
        } catch (\Throwable $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function filtersFromRequest(Request $request, bool $forExport = false): array
    {
        $filters = array_filter([
            'status' => $request->query('status'),
            'country' => $request->query('country'),
            'date_from' => PortalDateFilter::normalizeForApi($request->query('date_from')),
            'date_to' => PortalDateFilter::normalizeForApi($request->query('date_to')),
            'q' => $request->query('reference') ?: $request->query('q'),
        ], fn ($value) => $value !== null && $value !== '');

        if (! $forExport) {
            $filters['per_page'] = min(max((int) $request->query('per_page', 20), 1), 100);
            $filters['page'] = max((int) $request->query('page', 1), 1);
        }

        return $filters;
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    private function statsFiltersFromRequest(array $filters): array
    {
        unset($filters['per_page'], $filters['page']);

        return $filters;
    }

    /**
     * @param  list<array<string, mixed>>  $items
     * @param  array{current_page?: int, last_page?: int, total?: int}  $meta
     * @return array<string, mixed>
     */
    private function loadStats(
        AggregPayGatewayClient $client,
        string $type,
        array $statsFilters,
        array $meta,
    ): array {
        try {
            $response = $type === 'payins'
                ? $client->paymentStats($statsFilters)
                : $client->payoutStats($statsFilters);

            return TransactionListStats::fromGatewaySummary($response['data'] ?? [], $type);
        } catch (\Throwable) {
            $allItems = $this->fetchAllFilteredItems($client, $type, $statsFilters, $meta);

            return TransactionListStats::summarize(
                $allItems,
                $type,
                (int) ($meta['total'] ?? count($allItems)),
            );
        }
    }

    /**
     * @param  array<string, mixed>  $statsFilters
     * @param  array{current_page?: int, last_page?: int, total?: int}  $meta
     * @return list<array<string, mixed>>
     */
    private function fetchAllFilteredItems(
        AggregPayGatewayClient $client,
        string $type,
        array $statsFilters,
        array $meta,
    ): array {
        $total = (int) ($meta['total'] ?? 0);

        if ($total === 0) {
            return [];
        }

        $allItems = [];
        $lastPage = (int) ($meta['last_page'] ?? 1);

        for ($page = 1; $page <= $lastPage; $page++) {
            $pageFilters = $statsFilters;
            $pageFilters['per_page'] = 100;
            $pageFilters['page'] = $page;

            $response = $type === 'payins'
                ? $client->listPayments($pageFilters)
                : $client->listPayouts($pageFilters);

            $allItems = array_merge($allItems, $response['data']['data'] ?? []);
            $lastPage = (int) ($response['data']['last_page'] ?? $lastPage);
        }

        return $allItems;
    }
}
