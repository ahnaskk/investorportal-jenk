<?php

namespace App\Listeners;

use App\Providers\UserActivityLogServiceProvider;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UserHasAssignedInvestorListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        $user = $event->user;
        $roleName = $event->roleName;
        UserActivityLogServiceProvider::makeLogTypeToRoleName($user, $roleName);
    }
}
