<?php

namespace Smt\Masterweb;

use Illuminate\Support\ServiceProvider;

class SmtServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        require_once __DIR__.'/Helpers/Smt.php';
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
