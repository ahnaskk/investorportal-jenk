<?php

namespace App\Jobs;

use App\Library\Repository\RoleRepository;
use App\Mail\pushNotifyInvestor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class sendtoInvestors implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    protected $details;

    public function __construct($details)
    {
        $this->details = $details;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $setInvestors = [];
        $role = new RoleRepository();
        $investors = $role->allInvestors();

        foreach ($investors as $key => $investor) {
            $setInvestors[] = $investor->notification_email;
        }

        $setInvestors = array_filter($setInvestors);

        foreach ($setInvestors as $key => $investor) {
            /*     $test = new pushNotifyInvestor($this->details);
                 $when = now()->addSeconds(3000);
                 Mail::to($investor)->queue($test);*/
        }
    }
}
