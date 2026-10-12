<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Partner extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'code',
        'name',
        'email',
        'phone',
        'status',
        'api_access_granted_at',
        'metadata',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'api_access_granted_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    /**
     * All rate schedules negotiated for this partner (history included).
     */
    public function paymentRates(): HasMany
    {
        return $this->hasMany(PartnerPaymentRate::class);
    }

    /**
     * Active payin rate for a given operator.
     */
    public function activePayinRateFor(int|Operator $operator): HasOne
    {
        return $this->activeRateFor($operator, PartnerPaymentRate::FLOW_PAYIN);
    }

    /**
     * Active payout rate for a given operator.
     */
    public function activePayoutRateFor(int|Operator $operator): HasOne
    {
        return $this->activeRateFor($operator, PartnerPaymentRate::FLOW_PAYOUT);
    }

    /**
     * Active rate for partner + operator + flow (payin or payout).
     */
    public function activeRateFor(int|Operator $operator, string $flow): HasOne
    {
        $operatorId = $operator instanceof Operator ? $operator->id : $operator;

        return $this->hasOne(PartnerPaymentRate::class)
            ->where('operator_id', $operatorId)
            ->where('flow', $flow)
            ->where('status', 'active')
            ->where('effective_from', '<=', now())
            ->where(function ($query) {
                $query->whereNull('effective_until')
                    ->orWhere('effective_until', '>', now());
            })
            ->latest('effective_from');
    }
}
