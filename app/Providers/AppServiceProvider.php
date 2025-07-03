<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Pagination\Paginator;

use Laravel\Cashier\Cashier;
use App\Models\Member;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {   
        // Para a paginação funcionar com o Bootstrap(otherwise cria botões gigantes etc.)
        Paginator::useBootstrap();

        Cashier::useCustomerModel(Member::class);

        if(env('FORCE_HTTPS',false)) {
            error_log('configuring https');

            $app_url = config("app.url");
            URL::forceRootUrl($app_url);
            $schema = explode(':', $app_url)[0];
            URL::forceScheme($schema);
        }
    }
}
