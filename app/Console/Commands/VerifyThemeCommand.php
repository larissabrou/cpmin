<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class VerifyThemeCommand extends Command
{
    protected $signature = 'theme:verify';

    protected $description = 'Vérifie que les assets statiques du thème sont accessibles';

    /** @var list<string> */
    private array $required = [
        'theme/assets/css/bootstrap.min.css',
        'theme/assets/css/icons.min.css',
        'theme/assets/css/app.min.css',
        'theme/assets/images/logo-sm.svg',
        'theme/assets/images/favicon.ico',
        'theme/assets/libs/bootstrap/js/bootstrap.bundle.min.js',
    ];

    public function handle(): int
    {
        $missing = [];

        foreach ($this->required as $path) {
            if (! is_file(public_path($path))) {
                $missing[] = $path;
            }
        }

        if ($missing !== []) {
            $this->error('Assets manquants dans public/ :');
            foreach ($missing as $path) {
                $this->line("  - {$path}");
            }
            $this->newLine();
            $this->line('Sur le serveur de build : php artisan theme:sync --force');
            $this->line('Puis déployer le dossier public/theme/ sur la production.');

            return self::FAILURE;
        }

        $this->info('Tous les assets requis sont présents.');
        $this->line('Document root attendu : '.public_path());

        return self::SUCCESS;
    }
}
