<?php

namespace App\Console\Commands;

use App\Services\PortalPartnerProvisioner;
use Illuminate\Console\Command;

class PortalProvisionPartnerCommand extends Command
{
    protected $signature = 'portal:provision-partner
                            {slug : Slug du projet}
                            {name : Nom du projet}
                            {email : E-mail du gestionnaire}
                            {api_key : Clé API gateway}
                            {--webhook-url= : URL webhook}
                            {--webhook-secret= : Secret webhook}';

    protected $description = 'Crée le compte portail et envoie les accès par e-mail';

    public function handle(PortalPartnerProvisioner $provisioner): int
    {
        $result = $provisioner->provision([
            'slug' => $this->argument('slug'),
            'name' => $this->argument('name'),
            'contact_email' => $this->argument('email'),
            'api_key' => $this->argument('api_key'),
            'webhook_url' => $this->option('webhook-url'),
            'webhook_secret' => $this->option('webhook-secret'),
        ]);

        $this->info('Portail provisionné (company #'.$result['company_id'].', user #'.$result['user_id'].').');
        $this->line('E-mail envoyé à '.$this->argument('email').'.');

        return self::SUCCESS;
    }
}
