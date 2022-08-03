<?php

namespace App\Providers;

use App\CompanyAmount;
use App\Jobs\DashboardCalculationJob;
use App\MarketpalceInvestors;
use App\Merchant;
use App\MerchantUser;
use App\ParticipentPayment;
use App\PaymentInvestors;
use App\User;
use App\UserActivityLog;
use App\UserDetails;
use App\UserMeta;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Role;

class DashboardServiceProvider extends ServiceProvider
{
    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->updatePaymentInvestorAmount();
    }

    private function updatePaymentInvestorAmount()
    {
        PaymentInvestors::created(function ($modelInstance) {
            //$this->processInvestorJob( $modelInstance->user_id );
        });

        PaymentInvestors::updated(function ($modelInstance) {
            $this->processInvestorJob($modelInstance->user_id);
        });

        Merchant::updated(function ($modelInstance) {
            $userIds = MerchantUser::where('merchant_id', $modelInstance->id)->pluck('user_id','user_id')->toArray();
            
            self::addInvestorPrincipalJOb($userIds);
        });

        PaymentInvestors::deleted(function ($modelInstance) {
            $this->processInvestorJob($modelInstance->user_id);
        });
    }

    private function processInvestorJob(int $userId)
    {
        $authId = Auth::user() ? Auth::id() : 0;
        DashboardCalculationJob::dispatch('investor_payment', $userId, $authId);
        DashboardCalculationJob::dispatch('investor_principal', $userId, $authId);
    }

    public static function addInvestorPaymentJob($userIds = [])
    {
        $authId = Auth::user() ? Auth::id() : 0;
        UserMeta::update_it(1, 'dashboard_ctd_fee_update', 'yes');
        UserMeta::update_it(1, 'dashboard_ctd_fee_jobs', count($userIds));

        DashboardCalculationJob::dispatch('investor_payment_bulk', $userIds, $authId, [], true);
        /*foreach ($userIds as $userId) {
            DashboardCalculationJob::dispatch( 'investor_payment', $userId, $authId, [], true );
        }*/

        self::addInvestorPrincipalJOb($userIds);
    }

    public static function addInvestorPrincipalJOb($userIds)
    {
        $authId = Auth::user() ? Auth::id() : 0;
        $defaultMerchantIds = Merchant::whereIn('sub_status_id', [4, 22])->pluck('id')->toArray();

        $dashboard_cost_ctd_jobs = (int) UserMeta::find_it(1, 'dashboard_cost_ctd_jobs', 0);
        $dashboard_cost_ctd_jobs += count($userIds);

        UserMeta::update_it(1, 'dashboard_cost_ctd_jobs', $dashboard_cost_ctd_jobs);
        UserMeta::update_it(1, 'dashboard_cost_ctd_update', 'yes');
        DashboardCalculationJob::dispatch('investor_principal_bulk', $userIds, $authId, $defaultMerchantIds, true);
    }
}
