<?php

namespace App\Services\IPVueTable;

use Illuminate\Support\ServiceProvider;

class IPVuetableServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->alias('ip-vuetable', IPVuetable::class);
        $this->app->singleton('ip-vuetable', function () {
            return new IPVuetable(app('request'));
        });
    }
}
