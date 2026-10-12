<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'email',
        'phone',
        'status',
        'settings',
        'gateway_api_key',
        'gateway_webhook_url',
        'gateway_webhook_secret',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'settings' => 'array',
            'gateway_api_key' => 'encrypted',
            'gateway_webhook_secret' => 'encrypted',
        ];
    }

    public function hasGatewayCredentials(): bool
    {
        return filled($this->gateway_api_key);
    }

    public function maskedApiKey(): ?string
    {
        if (! $this->gateway_api_key) {
            return null;
        }

        $key = $this->gateway_api_key;

        return strlen($key) >= 12 ? substr($key, 0, 12).'…' : '***';
    }

    public function maskedWebhookSecret(): ?string
    {
        if (! $this->gateway_webhook_secret) {
            return null;
        }

        $secret = $this->gateway_webhook_secret;

        return strlen($secret) >= 8 ? substr($secret, 0, 6).'••••••' : '••••••';
    }

    /**
     * Users that belong to this company.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot(['role', 'is_owner', 'joined_at'])
            ->withTimestamps();
    }

    /**
     * Metrics displayed in the company dashboard.
     */
    public function dashboardMetrics(): HasMany
    {
        return $this->hasMany(DashboardMetric::class);
    }
}
