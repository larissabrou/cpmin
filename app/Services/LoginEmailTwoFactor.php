<?php

namespace App\Services;

use App\Mail\LoginTwoFactorCodeMail;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class LoginEmailTwoFactor
{
    public function start(User $user, bool $remember): string
    {
        $intentId = (string) Str::uuid();
        $code = (string) random_int(100000, 999999);

        Cache::put($this->cacheKey($intentId), [
            'user_id' => $user->id,
            'code_hash' => Hash::make($code),
            'remember' => $remember,
        ], config('security.email_2fa_ttl_seconds', 900));

        Mail::to($user->email)->send(new LoginTwoFactorCodeMail($code, $user));

        return $intentId;
    }

    /**
     * @return array{user_id: int, remember: bool}|null
     */
    public function verify(string $intentId, string $code): ?array
    {
        $data = Cache::get($this->cacheKey($intentId));

        if (! is_array($data) || ! isset($data['user_id'], $data['code_hash'])) {
            return null;
        }

        $digits = preg_replace('/\D/', '', $code) ?? '';

        if (strlen($digits) !== 6 || ! Hash::check($digits, $data['code_hash'])) {
            return null;
        }

        Cache::forget($this->cacheKey($intentId));

        return [
            'user_id' => (int) $data['user_id'],
            'remember' => (bool) ($data['remember'] ?? false),
        ];
    }

    public function forget(string $intentId): void
    {
        Cache::forget($this->cacheKey($intentId));
    }

    private function cacheKey(string $intentId): string
    {
        return 'portal_login_email_2fa:'.$intentId;
    }
}
