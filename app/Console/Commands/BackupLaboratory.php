<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class BackupLaboratory extends Command
{
    protected $signature = 'qualityshop:backup';

    protected $description = 'Preserva uma copia consistente do SQLite local antes da configuracao ou restauracao';

    public function handle(): int
    {
        $expected = realpath(database_path('database.sqlite'));
        $configured = realpath((string) config('database.connections.sqlite.database'));
        if (config('database.default') !== 'sqlite' || ! $expected || $configured !== $expected || config('database.connections.sqlite.url')) {
            $this->error('Operacao restrita ao arquivo database/database.sqlite deste projeto. Confira a configuracao antes de continuar.');

            return self::FAILURE;
        }
        if (filesize($expected) === 0) {
            $this->info('Banco novo e vazio: nenhuma copia necessaria.');

            return self::SUCCESS;
        }
        $directory = storage_path('app/private/backups');
        File::ensureDirectoryExists($directory);
        $destination = $directory.DIRECTORY_SEPARATOR.'qualityshop-'.now()->format('Ymd-His').'-'.Str::lower(Str::random(6)).'.sqlite';
        DB::statement('VACUUM INTO '.DB::connection()->getPdo()->quote($destination));
        $this->info('Copia de seguranca salva em: '.$destination);

        return self::SUCCESS;
    }
}
