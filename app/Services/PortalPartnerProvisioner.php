<?php

namespace App\Services;

use App\Mail\PartnerPortalAccessMail;
use App\Models\Company;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Throwable;

class PortalPartnerProvisioner
{
    /**
     * @param  array{slug: string, name: string, contact_email: string, api_key: string, webhook_url?: ?string, webhook_secret?: ?string}  $data
     * @return array{company_id: int, user_id: int, user_created: bool, email_sent: bool}
     */
    public function provision(array $data): array
    {
        Log::info('Portal partner provisioning started', $this->logContext($data));

        return DB::transaction(function () use ($data) {
            $company = Company::updateOrCreate(
                [
                    'slug' => $data['slug'],
                    'email' => $data['contact_email'],
                ],
                [
                    'name' => $data['name'],
                    'status' => 'active',
                    'gateway_api_key' => $data['api_key'],
                    'gateway_webhook_url' => $data['webhook_url'] ?? null,
                    'gateway_webhook_secret' => $data['webhook_secret'] ?? null,
                ],
            );

            Log::info('Portal company ready', [
                'company_id' => $company->id,
                'slug' => $company->slug,
                'email' => $company->email,
            ]);

            $user = User::where('email', $data['contact_email'])->first();
            $isNewUser = $user === null;
            $plainPassword = Str::password(12);

            if ($isNewUser) {
                $user = User::create([
                    'name' => $this->managerName($data),
                    'email' => $data['contact_email'],
                    'password' => Hash::make($plainPassword),
                    'must_change_password' => true,
                    'current_company_id' => $company->id,
                ]);

                Log::info('Portal user created', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                ]);
            } else {
                $user->update([
                    'password' => Hash::make($plainPassword),
                    'must_change_password' => true,
                    'current_company_id' => $user->current_company_id ?: $company->id,
                ]);

                Log::info('Portal user updated with new temporary password', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                ]);
            }

            $company->users()->syncWithoutDetaching([
                $user->id => ['role' => 'owner', 'is_owner' => true, 'joined_at' => now()],
            ]);

            $emailSent = $this->sendAccessEmail($company, $user, $plainPassword, $data);

            return [
                'company_id' => $company->id,
                'user_id' => $user->id,
                'user_created' => $isNewUser,
                'email_sent' => $emailSent,
            ];
        });
    }

    /**
     * @param  array{slug: string, name: string, contact_email: string}  $data
     */
    private function sendAccessEmail(Company $company, User $user, string $plainPassword, array $data): bool
    {
        $loginUrl = route('login');
        $mailer = (string) config('mail.default');
        $fromAddress = (string) config('mail.from.address');
        $fromName = (string) config('mail.from.name');

        Log::info('Portal access email sending', [
            ...$this->logContext($data),
            'company_id' => $company->id,
            'user_id' => $user->id,
            'recipient' => $user->email,
            'mail_mailer' => $mailer,
            'mail_from' => $fromAddress,
            'mail_from_name' => $fromName,
            'login_url' => $loginUrl,
            'password_included' => true,
        ]);

        if ($mailer === 'log') {
            Log::warning('Portal access email uses MAIL_MAILER=log — no real e-mail will be delivered', [
                'recipient' => $user->email,
                'slug' => $data['slug'],
            ]);
        }

        try {
            Mail::to($user)->send(new PartnerPortalAccessMail(
                company: $company,
                user: $user,
                plainPassword: $plainPassword,
                loginUrl: $loginUrl,
            ));

            Log::info('Portal access email sent successfully', [
                'recipient' => $user->email,
                'slug' => $data['slug'],
                'company_id' => $company->id,
                'user_id' => $user->id,
                'mail_mailer' => $mailer,
            ]);

            return true;
        } catch (Throwable $e) {
            Log::error('Portal access email failed', [
                'recipient' => $user->email,
                'slug' => $data['slug'],
                'company_id' => $company->id,
                'user_id' => $user->id,
                'mail_mailer' => $mailer,
                'mail_from' => $fromAddress,
                'exception' => $e::class,
                'message' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * @param  array{slug: string, name: string, contact_email: string}  $data
     * @return array{slug: string, contact_email: string, project_name: string}
     */
    private function logContext(array $data): array
    {
        return [
            'slug' => $data['slug'],
            'contact_email' => $data['contact_email'],
            'project_name' => $data['name'],
        ];
    }

    /**
     * @param  array{name: string, contact_email: string}  $data
     */
    private function managerName(array $data): string
    {
        $localPart = Str::before($data['contact_email'], '@');

        return Str::headline(str_replace(['.', '_', '-'], ' ', $localPart))
            ?: 'Gestionnaire '.$data['name'];
    }
}
