<?php

namespace App\Jobs;

use App\ApiLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AddMerchantNotes implements ShouldQueue
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
        $form_params = $this->details;
        $client = new \GuzzleHttp\Client();

        $response = $client->request('POST', config('app.crm_url').'/api/service', [
                        'form_params' => $form_params,
            ]);

        $json_encode = json_encode($form_params);

        ApiLog::create(['api_name'=>config('app.crm_url').'/api/service', 'ip_address'=>$last_login_ip, 'request'=>$json_encode, 'date'=>date('Y-m-d h:m:i'), 'response'=>$response->getBody()]);
    }
}
