<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DashboardMetric extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'company_id',
        'metric_key',
        'metric_value',
        'captured_at',
        'meta',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'captured_at' => 'datetime',
            'meta' => 'array',
            'metric_value' => 'decimal:2',
        ];
    }

    /**
     * Company owning this metric entry.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
