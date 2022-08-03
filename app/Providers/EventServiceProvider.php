<?php

namespace App\Providers;

use App\Events\DashboardJobSuccessEvent;
use App\Events\UserHasAssignedInvestor;
use Illuminate\Auth\Events\Login;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        \App\Events\Event::class => [
            \App\Listeners\EventListener::class,
        ],
        Login::class => [
            \App\Listeners\UserLoggedInListener::class,
        ],
        UserHasAssignedInvestor::class => [
            \App\Listeners\UserHasAssignedInvestorListener::class,
        ],
        DashboardJobSuccessEvent::class => [
            \App\Listeners\DashboardJobSuccessListener::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {

        //
    }
}
