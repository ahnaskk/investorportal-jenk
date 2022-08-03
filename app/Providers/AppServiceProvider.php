<?php

namespace App\Providers;

use App\Events\DashboardJobSuccessEvent;
use App\UserMeta;
use Illuminate\Database\Schema\Builder;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Routing\UrlGenerator;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\ServiceProvider;
use Laravel\Dusk\DuskServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(UrlGenerator $url)
    {
        Builder::defaultStringLength(191);
        /*if (config('settings.app_env') === 'production') {
            $url->formatScheme('https');
        }*/
        Queue::after(function (JobProcessed $event) {
            $job = @unserialize(optional($event->job->payload())['data']['command'] ?? serialize([]));
            $this->processJobEvent($job);
        });
    }

    public function processJobEvent($job)
    {
        if (optional($job)->type == 'investor_principal' and optional($job)->isBulk) {
            $this->updatePrincipalJOb($job);
        } elseif (optional($job)->type == 'investor_payment' and optional($job)->isBulk) {
            $this->updatePaymentJob($job);
        }
    }

    public function updatePaymentJob($job)
    {
        $dashboard_ctd_fee_jobs = (int) UserMeta::find_it(1, 'dashboard_ctd_fee_jobs', 0);
        if ($dashboard_ctd_fee_jobs < 1) {
            return '';
        }
        $dashboard_ctd_fee_jobs -= 1;
        UserMeta::update_it(1, 'dashboard_ctd_fee_jobs', $dashboard_ctd_fee_jobs);
        if ($dashboard_ctd_fee_jobs < 1) {
            UserMeta::update_it(1, 'dashboard_ctd_fee_update', '');
            // its working but looks like a new feature for them so temporarily commented (5-April-2022)
            // event(new DashboardJobSuccessEvent('Payment Update has been completed everywhere in system.', optional($job)->authId, optional($job)->host));
        }
    }

    public function updatePrincipalJOb($job)
    {
        $dashboard_cost_ctd_jobs = (int) UserMeta::find_it(1, 'dashboard_cost_ctd_jobs', 0);
        if ($dashboard_cost_ctd_jobs < 1) {
            return '';
        }
        $dashboard_cost_ctd_jobs -= 1;
        UserMeta::update_it(1, 'dashboard_cost_ctd_jobs', $dashboard_cost_ctd_jobs);
        if ($dashboard_cost_ctd_jobs < 1) {
            UserMeta::update_it(1, 'dashboard_cost_ctd_update', '');
        }
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if (config('app.env') === 'production') {
            $this->app['request']->server->set('HTTPS', true);
        }

        // if (config('settings.app_env') === 'production') {
        // $this->app['request']->server->set('HTTPS', true);
        //  }

        // if ($this->app->environment('local', 'testing')) {
        //     $this->app->register(DuskServiceProvider::class);
        // }

            // $qq=Input::get('fields');
            // $test=isset($qq)?Input::get('fields'):'';
            // $fire='';

        //     if($test)
        //     {
        //            $fields=Input::get('fields');
        //            $fields =explode(',', $fields);
        //           // $fields = preg_split( "/ (@|vs) /", $fields);

        //            $fire =[ "sClass" => "hidden-column", "aTargets" =>$fields];

        //     }

        // Config::set('DTParametros', [
        //   'columnDefs'       =>   [ "sClass" => "hidden-column", "aTargets" =>$fire],
        // ]);

       // print_r(config('DTParametros'));
    }
}
