<?php

namespace App\Support;

class TransactionListStats
{
    /**
     * @param  array<string, mixed>  $data
     * @return array{
     *     total: int,
     *     success: int,
     *     failed: int,
     *     pending: int,
     *     cancelled: int,
     *     volume_success: int,
     *     net_success: int,
     *     debited_success: int,
     *     success_rate: float,
     *     partial: bool,
     *     filtered_total: int,
     *     type: string
     * }
     */
    public static function fromGatewaySummary(array $data, string $type): array
    {
        $filteredTotal = (int) ($data['total'] ?? 0);

        return [
            'total' => $filteredTotal,
            'success' => (int) ($data['success'] ?? 0),
            'failed' => (int) ($data['failed'] ?? 0),
            'pending' => (int) ($data['pending'] ?? 0),
            'cancelled' => (int) ($data['cancelled'] ?? 0),
            'volume_success' => (int) ($data['volume_success'] ?? 0),
            'net_success' => (int) ($data['net_success'] ?? 0),
            'debited_success' => (int) ($data['debited_success'] ?? 0),
            'success_rate' => (float) ($data['success_rate'] ?? 0),
            'partial' => false,
            'filtered_total' => $filteredTotal,
            'type' => $type,
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $items
     * @return array{
     *     total: int,
     *     success: int,
     *     failed: int,
     *     pending: int,
     *     cancelled: int,
     *     volume_success: int,
     *     net_success: int,
     *     debited_success: int,
     *     success_rate: float,
     *     partial: bool,
     *     filtered_total: int,
     *     type: string
     * }
     */
    public static function summarize(array $items, string $type, int $filteredTotal): array
    {
        $stats = [
            'total' => count($items),
            'success' => 0,
            'failed' => 0,
            'pending' => 0,
            'cancelled' => 0,
            'volume_success' => 0,
            'net_success' => 0,
            'debited_success' => 0,
            'success_rate' => 0.0,
            'partial' => false,
            'filtered_total' => $filteredTotal,
            'type' => $type,
        ];

        foreach ($items as $row) {
            $status = strtoupper((string) ($row['status'] ?? ''));
            $amount = (int) ($row['amount'] ?? 0);

            match ($status) {
                'SUCCESS' => (function () use (&$stats, $row, $amount, $type): void {
                    $stats['success']++;
                    $stats['volume_success'] += $amount;
                    if ($type === 'payins') {
                        $stats['net_success'] += (int) ($row['net_amount'] ?? $amount);
                    } else {
                        $stats['debited_success'] += (int) ($row['total_debited'] ?? ($amount + (int) ($row['fee_amount'] ?? 0)));
                    }
                })(),
                'FAILED' => $stats['failed']++,
                'PENDING' => $stats['pending']++,
                'CANCELLED' => $stats['cancelled']++,
                default => null,
            };
        }

        $closed = $stats['success'] + $stats['failed'] + $stats['cancelled'];
        $stats['success_rate'] = $closed > 0
            ? round(($stats['success'] / $closed) * 100, 1)
            : 0.0;

        return $stats;
    }
}
