<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class SyncThemeCommand extends Command
{
    protected $signature = 'theme:sync {--force : Écraser sans confirmation}';

    protected $description = 'Copie le thème Vuesy (dist/assets) vers public/theme/assets';

    public function handle(): int
    {
        $source = base_path('../dist/assets');
        $target = public_path('theme/assets');

        if (! is_dir($source)) {
            $this->error("Source introuvable : {$source}");
            $this->line('Le dossier dist/assets doit exister à la racine du monorepo.');

            return self::FAILURE;
        }

        if (is_dir($target) && ! $this->option('force') && ! $this->confirm('Écraser public/theme/assets ?', true)) {
            return self::SUCCESS;
        }

        if (is_dir($target)) {
            File::deleteDirectory($target);
        }

        File::ensureDirectoryExists(dirname($target));
        File::copyDirectory($source, $target);

        $this->info("Thème synchronisé : {$source} → {$target}");

        return self::SUCCESS;
    }
}
