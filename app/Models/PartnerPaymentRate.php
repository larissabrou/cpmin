<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PartnerPaymentRate extends Model
{
    use HasFactory;

    public const FLOW_PAYIN = 'payin';

    public const FLOW_PAYOUT = 'payout';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'partner_id',
        'operator_id',
        'flow',
        'label',
        'commission_percent',
        'fixed_fee_per_transaction',
        'min_transaction_amount',
        'max_transaction_amount',
        'currency',
        'effective_from',
        'effective_until',
        'status',
        'notes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'commission_percent' => 'decimal:4',
            'fixed_fee_per_transaction' => 'decimal:2',
            'min_transaction_amount' => 'decimal:2',
            'max_transaction_amount' => 'decimal:2',
            'effective_from' => 'datetime',
            'effective_until' => 'datetime',
        ];
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }

    public function operator(): BelongsTo
    {
        return $this->belongsTo(Operator::class);
    }

    public function scopePayin($query)
    {
        return $query->where('flow', self::FLOW_PAYIN);
    }

    public function scopePayout($query)
    {
        return $query->where('flow', self::FLOW_PAYOUT);
    }

    public function scopeForOperator($query, int|Operator $operator)
    {
        $operatorId = $operator instanceof Operator ? $operator->id : $operator;

        return $query->where('operator_id', $operatorId);
    }

    public function isPayin(): bool
    {
        return $this->flow === self::FLOW_PAYIN;
    }

    public function isPayout(): bool
    {
        return $this->flow === self::FLOW_PAYOUT;
    }

    public function isEffectiveAt(?\DateTimeInterface $at = null): bool
    {
        $at ??= now();

        if ($this->status !== 'active') {
            return false;
        }

        if ($this->effective_from > $at) {
            return false;
        }

        return $this->effective_until === null || $this->effective_until > $at;
    }
}
