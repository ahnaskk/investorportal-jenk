<?php

namespace App\Providers;

use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;

class RepoServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(
            \App\Library\Repository\Interfaces\IRoleRepository::class,
            \App\Library\Repository\RoleRepository::class
        );
        $this->app->bind(
            \App\Library\Repository\Interfaces\ITransactionsRepository::class,
            \App\Library\Repository\TransactionsRepository::class
        );
        $this->app->bind(
            \App\Library\Repository\Interfaces\IMarketOfferRepository::class,
            \App\Library\Repository\MarketOfferRepository::class
        );
        $this->app->bind(
            \App\Library\Repository\Interfaces\IUserRepository::class,
            \App\Library\Repository\UserRepository::class
        );
         $this->app->bind(
            \App\Library\Repository\Interfaces\IVisitorRepository::class,
            \App\Library\Repository\VisitorRepository::class
        );
        $this->app->bind(
            \App\Library\Repository\Interfaces\ILogRepository::class,
            \App\Library\Repository\LogRepository::class
        );
        $this->app->bind(
            \App\Library\Repository\Interfaces\ISubStatusRepository::class,
            \App\Library\Repository\SubStatusRepository::class
        );
        $this->app->bind(
            \App\Library\Repository\Interfaces\IMessageRepository::class,
            \App\Library\Repository\MessageRepository::class
        );
        $this->app->bind(
            \App\Library\Repository\Interfaces\IPennyAdjustmentRepository::class,
            \App\Library\Repository\PennyAdjustmentRepository::class
        );
        $this->app->bind(
            \App\Library\Repository\Interfaces\IInvestorRepository::class,
            \App\Library\Repository\InvestorRepository::class
        );

        $this->app->bind(
            \App\Library\Repository\Interfaces\IMNotesRepository::class,
            \App\Library\Repository\MNotesRepository::class
        );
        $this->app->bind(
            \App\Library\Repository\Interfaces\IMerchantRepository::class,
            \App\Library\Repository\MerchantRepository::class
        );

        $this->app->bind(
            \App\Library\Repository\Interfaces\IParticipantPaymentRepository::class,
            \App\Library\Repository\ParticipantPaymentRepository::class
        );
        $this->app->bind(
            \App\Library\Repository\Interfaces\IMerchantBatchRepository::class,
            \App\Library\Repository\MerchantBatchRepository::class
        );
        $this->app->bind(
            \App\Library\Repository\Interfaces\IMarketPlaceRepository::class,
            \App\Library\Repository\MarketPlaceRepository::class
        );
        $this->app->bind(
            \App\Library\Repository\Interfaces\IInvestorTransactionRepository::class,
            \App\Library\Repository\InvestorTransactionRepository::class
        );

        $this->app->bind(
            \App\Library\Repository\Interfaces\ILiquidityLogRepository::class,
            \App\Library\Repository\LiquidityLogRepository::class
        );

        $this->app->bind(
            \App\Library\Repository\Interfaces\ITemplateRepository::class,
            \App\Library\Repository\TemplateRepository::class
        );

        $this->app->bind(
            \App\Library\Repository\Interfaces\ILabelRepository::class,
            \App\Library\Repository\LabelRepository::class
        );

        $this->app->bind(
            \App\Library\Repository\Interfaces\ISubStatusFlagRepository::class,
            \App\Library\Repository\SubStatusFlagRepository::class
        );

        $this->app->bind(
            \App\Library\Repository\Interfaces\IUserActivityLogRepository::class,
            \App\Library\Repository\UserActivityLogRepository::class
        );
        $this->app->bind(
            \App\Library\Repository\Interfaces\ISubAdminRepository::class,
            \App\Library\Repository\SubAdminRepository::class
        );
        $this->app->bind(
            \App\Library\Repository\Interfaces\IPermissionLogRepository::class,
            \App\Library\Repository\PermissionLogRepository::class
        );
        $this->app->bind(
            \App\Library\Repository\Interfaces\IAdminUserRepository::class,
            \App\Library\Repository\AdminUserRepository::class
        );
    }
}
