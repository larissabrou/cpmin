<?php

namespace App\Services;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AggregPayGatewayClient
{
    public function __construct(
        private readonly string $baseUrl,
        private readonly string $apiKey,
    ) {}

    public static function forCompany(\App\Models\Company $company): self
    {
        $key = $company->gateway_api_key;

        if (! $key) {
            throw new \RuntimeException('Clé API AggregPay non configurée pour cette entreprise.');
        }

        return new self(
            rtrim((string) config('aggregpay.gateway_url'), '/'),
            $key,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function get(string $path, array $query = []): array
    {
        return $this->request('GET', $path, query: $query);
    }

    /**
     * @return array<string, mixed>
     */
    private function request(string $method, string $path, array $query = [], ?array $body = null): array
    {
        $url = $this->baseUrl.'/api/v1/'.ltrim($path, '/');

        $pending = Http::timeout(20)
            ->withHeaders([
                'Authorization' => 'Bearer '.$this->apiKey,
                'Accept' => 'application/json',
            ]);

        try {
            $response = match ($method) {
                'GET' => $pending->get($url, $query),
                'POST' => $pending->post($url, $body ?? []),
                default => throw new \InvalidArgumentException("Méthode {$method} non supportée."),
            };
        } catch (RequestException $e) {
            throw new \RuntimeException('AggregPay API : '.$e->response?->body(), $e->response?->status() ?? 502);
        }

        if (! $response->successful()) {
            throw new \RuntimeException('AggregPay API ['.$response->status().'] : '.$response->body(), $response->status());
        }

        return $response->json() ?? [];
    }

    public function account(): array
    {
        return $this->get('account');
    }

    public function listPayments(array $query = []): array
    {
        return $this->get('payments', $query);
    }

    public function listPayouts(array $query = []): array
    {
        return $this->get('payouts', $query);
    }

    public function paymentStats(array $query = []): array
    {
        return $this->get('payments/stats', $query);
    }

    public function payoutStats(array $query = []): array
    {
        return $this->get('payouts/stats', $query);
    }

    public function downloadExport(string $path, array $query = []): StreamedResponse
    {
        $url = $this->baseUrl.'/api/v1/'.ltrim($path, '/');
        $filename = str_contains($path, 'payout') ? 'payouts.csv' : 'payins.csv';

        return response()->streamDownload(function () use ($url, $query) {
            $response = \Illuminate\Support\Facades\Http::timeout(120)
                ->withHeaders([
                    'Authorization' => 'Bearer '.$this->apiKey,
                    'Accept' => 'text/csv',
                ])
                ->get($url, $query);

            if (! $response->successful()) {
                throw new \RuntimeException('Export AggregPay ['.$response->status().'] : '.$response->body(), $response->status());
            }

            echo $response->body();
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function rates(string $country = 'CI'): array
    {
        return $this->get('rates', ['country' => $country]);
    }

    public function routing(): array
    {
        return $this->get('routing');
    }
}
