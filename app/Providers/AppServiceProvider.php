<?php

namespace App\Providers;

use Carbon\Carbon;
use Smt\Masterweb\Models\Module;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\View;
use Smt\Masterweb\Models\Privileges;
use Illuminate\Support\ServiceProvider;
use Illuminate\Console\Events\ArtisanStarting;

class AppServiceProvider extends ServiceProvider
{
  /**
   * Register any application services.
   *
   * @return void
   */
  public function register()
  {
    //
  }

  /**
   * Bootstrap any application services.
   *
   * @return void
   */
  public function boot()
  {
    Event::listen(ArtisanStarting::class, function () {
      app()->forgetInstance('command.migrate');
      app()->singleton('command.migrate', function ($app) {
        return new \App\Console\MigrateCommand($app['migrator']);
      });
    });

    config(['app.locale' => 'id']);
    Carbon::setLocale('id');
    date_default_timezone_set('Asia/Jakarta');
  }
}