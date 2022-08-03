<?php

namespace App\Jobs;

use App\Events\DashboardJobSuccessEvent;
use InvestorHelper;
use App\PaymentInvestors;
use App\UserMeta;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class DashboardCalculationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 25;

    public $type;
    public $userId;
    public $authId;
    public $host;
    public $isBulk;
    public $timeout = 24 * 60 * 60;
    public $defaultMerchantIds = [];

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(string $type, $userId, int $authId = 0, array $defaultMerchantIds = [], bool $isBulk = false)
    {
        $this->type = $type;
        $this->userId = $userId;
        $this->authId = $authId;
        $this->defaultMerchantIds = $defaultMerchantIds;
        $this->isBulk = $isBulk;
        $this->host = request()->getHost();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->type == 'investor_principal') {
            InvestorHelper::updateUserPrincipal($this->userId);
        }

        if ($this->type == 'investor_payment') {
            InvestorHelper::updatePaymentValues($this->userId);
        }

        if ($this->type == 'investor_principal_bulk' and is_array($this->userId)) {
            $jobs = 0;
            foreach ($this->userId as $userId) {
                $jobs++;
                set_time_limit(0);
                InvestorHelper::updateUserPrincipal($userId, $this->defaultMerchantIds);
            }

            $dashboard_cost_ctd_jobs = (int) UserMeta::find_it(1, 'dashboard_cost_ctd_jobs', 0);
            UserMeta::update_it(1, 'dashboard_cost_ctd_update', '');
            UserMeta::update_it(1, 'dashboard_cost_ctd_jobs', $dashboard_cost_ctd_jobs - $jobs);
        }

        if ($this->type == 'investor_payment_bulk' and is_array($this->userId)) {
            $jobs = 0;
            foreach ($this->userId as $userId) {
                $jobs++;
                set_time_limit(0);
                InvestorHelper::updatePaymentValues($userId);
            }
            UserMeta::update_it(1, 'dashboard_ctd_fee_update', '');
            // its working but looks like a new feature for them so temporarily commented (5-April-2022)
            // event(new DashboardJobSuccessEvent('Payment Update has been completed everywhere in system.', optional($this)->authId, optional($this)->host));
        }
    }
}
