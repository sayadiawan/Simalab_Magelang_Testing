<?php 

namespace Smt\Masterweb;


use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class MasterWebServiceProvider  extends RouteServiceProvider
{
    protected $namespace='Smt\Masterweb\Http\Controllers';
    protected $namespaceapi='Smt\Masterweb\Http\Controllers\Api';
    public function boot()
    {
        parent::boot();
        
        // Load views namespace - must be in boot() method
        $this->loadViewsFrom(__DIR__.'/views', 'masterweb');
        
        // Load migrations from package
        $this->loadMigrationsFrom(__DIR__.'/migrations');
        
        $this->publishes([
            __DIR__.'/public/assets' => public_path('assets'),
        ], 'public');
    }

   

    public function map()
    {
        $this->mapApiRoutes();

        $this->mapWebRoutes();
    }


    protected function mapApiRoutes()
    {
        Route::prefix('api')
            ->namespace($this->namespaceapi)
            ->group(__DIR__ . '/routes/api.php');
    }

    protected function mapWebRoutes()
    {
        Route::middleware('web')
            ->namespace($this->namespace)
            ->group(__DIR__ . '/routes/web.php');
    }



    public function register()
    {
        # code...
    }
}