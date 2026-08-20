<?php

namespace App\Console;

use Illuminate\Database\Console\Migrations\MigrateCommand as MigrateCommandBase;

/**
 * Laravel 6: opsi --path selalu di-prefix basePath(), sehingga path absolut
 * (mis. /home/user/proj/database/migrations/foo.php) menjadi path ganda dan gagal.
 * Path absolut diperlakukan seperti --realpath implisit.
 *
 * Tidak diletakkan di app/Console/Commands/ agar tidak di-auto-load sebagai command terpisah.
 */
class MigrateCommand extends MigrateCommandBase
{
    /**
     * @return array
     */
    protected function getMigrationPaths()
    {
        if ($this->input->hasOption('path') && $this->option('path')) {
            return collect($this->option('path'))->map(function ($path) {
                if ($this->usingRealPath()) {
                    return $path;
                }
                if ($this->isAbsolutePath((string) $path)) {
                    return $path;
                }

                return $this->laravel->basePath().'/'.$path;
            })->all();
        }

        return parent::getMigrationPaths();
    }

    /**
     * @param string $path
     */
    protected function isAbsolutePath($path): bool
    {
        if ($path === '') {
            return false;
        }
        if ($path[0] === '/' || $path[0] === '\\') {
            return true;
        }
        if (strlen($path) > 2 && ctype_alpha($path[0]) && $path[1] === ':'
            && ($path[2] === '\\' || $path[2] === '/')) {
            return true;
        }

        return false;
    }
}
