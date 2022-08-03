<?php
namespace App\Providers;
use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;
class MerchantUtilityServiceProvider extends ServiceProvider
{
    public function boot()
    {
        //
    }
    public function register()
    {
        App::bind('payment.calc', function () {
            return new \App\Library\Helpers\PaymentCalculator();
        });
        App::bind('event.history', function () {
            return new \App\Library\Helpers\EventHistory();
        });
        App::bind('roles.permissions', function () {
            return new \App\Library\Helpers\Permissions();
        });
        App::bind('field.formatter', function () {
            return new \App\Library\Helpers\FieldFormatter();
        });
        App::bind('generate.payment.helper', function () {
            return new \App\Library\Helpers\GeneratePaymentHelper();
        });
        App::bind('label.helper', function () {
            return new \App\Helpers\LabelHelper(
                App::make(\App\Library\Repository\Interfaces\ILabelRepository::class),
            );
        });
        App::bind('funding.helper', function () {
            return new \App\Helpers\FundingHelper();
        });
        App::bind('market.offer.helper', function () {
            return new \App\Helpers\MarketOfferHelper(
                App::make(\App\Library\Repository\Interfaces\IRoleRepository::class),
                App::make(\App\Library\Repository\Interfaces\ISubStatusRepository::class)
            );
        });
        App::bind('liquidity.log.helper', function () {
            return new \App\Helpers\LiquidityLogHelper(
                App::make(\App\Library\Repository\Interfaces\IRoleRepository::class),
                App::make(\App\Library\Repository\Interfaces\ILabelRepository::class),
            );
        });
        App::bind('investor.helper', function (){
            return new \App\Helpers\InvestorHelper(
                App::make(\App\Library\Repository\Interfaces\IMerchantRepository::class),
            );
        });
        App::bind('merchant.helper', function (){
            return new \App\Helpers\MerchantHelper(
                App::make(\App\Library\Repository\Interfaces\ISubStatusRepository::class),
                App::make(\App\Library\Repository\Interfaces\IRoleRepository::class),
                App::make(\App\Library\Repository\Interfaces\IMerchantRepository::class),
                App::make(\App\Library\Repository\Interfaces\ILabelRepository::class),
                App::make(\App\Library\Repository\Interfaces\IParticipantPaymentRepository::class),
               App::make(\Yajra\DataTables\Html\Builder::class)
            );
        });
        App::bind('generate.payment.helper', function () {
            return new \App\Library\Helpers\GeneratePaymentHelper(
                App::make(\App\Library\Repository\Interfaces\IInvestorTransactionRepository::class),
            );
        });
        App::bind('company.helper', function () {
            return new \App\Helpers\CompanyHelper(App::make(\App\Library\Repository\Interfaces\IRoleRepository::class));
        });
        App::bind('investor.transaction.helper', function () {
            return new \App\Helpers\InvestorTransactionHelper(App::make(\App\Library\Repository\Interfaces\IRoleRepository::class));
        });
        App::bind('dashboard.helper', function () {
            return new \App\Helpers\DashboardHelper(App::make(\App\Library\Repository\Interfaces\IRoleRepository::class));
        });
        App::bind('notes.helper', function () {
            return new \App\Helpers\NotesHelper(
                App::make(\App\Library\Repository\Interfaces\IMNotesRepository::class),
            );
        });
        App::bind('branch.helper', function () {
            return new \App\Helpers\BranchHelper(
                App::make(\App\Library\Repository\Interfaces\IRoleRepository::class),
                App::make(\App\Library\Repository\Interfaces\IUserRepository::class),
                App::make(\Yajra\DataTables\Html\Builder::class)
            );
        });        
        App::bind('faq.helper', function () {
            return new \App\Helpers\FaqHelper();
        });
        App::bind('collectionUser.helper', function () {
            return new \App\Helpers\CollectionUserHelper(
                App::make(\App\Library\Repository\Interfaces\IRoleRepository::class),
                App::make(\App\Library\Repository\Interfaces\IUserRepository::class),
                App::make(\Yajra\DataTables\Html\Builder::class)
            );
        });
        App::bind('merchant.table.builder', function () {
            return new \App\Library\Helpers\MerchantTableBuilder(
                App::make(\App\Library\Repository\Interfaces\IMerchantRepository::class),
                App::make(\App\Library\Repository\Interfaces\IRoleRepository::class),
                App::make(\App\Library\Repository\Interfaces\IParticipantPaymentRepository::class), //,
                //  App::make('App\Library\Repository\Interfaces\IMerchantPaymentRepository')
                App::make(\App\Library\Repository\Interfaces\IUserRepository::class),
                App::make(\App\Library\Repository\Interfaces\ILiquidityLogRepository::class)
            );
        });
        App::bind('merchant.statement.helper', function () {
            return new \App\Helpers\MerchantStatementHelper(
                App::make(\Yajra\DataTables\Html\Builder::class)
            );
        });
       
        App::bind('participant.payment.helper', function () {
            return new \App\Helpers\ParticipantPaymentHelper();
        });
        App::bind('merchant.user.helper', function () {
            return new \App\Helpers\MerchantUserHelper(
                App::make(\App\Library\Repository\Interfaces\ISubStatusRepository::class),
                App::make(\App\Library\Repository\Interfaces\IRoleRepository::class),
                App::make(\App\Library\Repository\Interfaces\IMerchantRepository::class),
                App::make(\App\Library\Repository\Interfaces\ILabelRepository::class),
                App::make(\App\Library\Repository\Interfaces\IParticipantPaymentRepository::class),
            );
        });  
        App::bind('payment.term.helper', function () {
            return new \App\Helpers\PaymentTermHelper(
                App::make(\App\Library\Repository\Interfaces\IMerchantRepository::class),
            );
        });
        App::bind('investor.assign.helper', function () {
            return new \App\Helpers\InvestorAssignHelper(
                App::make(\App\Library\Repository\Interfaces\ISubStatusRepository::class),
                App::make(\App\Library\Repository\Interfaces\IRoleRepository::class),
                App::make(\App\Library\Repository\Interfaces\IMerchantRepository::class),
                App::make(\App\Library\Repository\Interfaces\ILabelRepository::class),
                App::make(\App\Library\Repository\Interfaces\IParticipantPaymentRepository::class),
            );
        });
        App::bind('report.helper', function () {
            return new \App\Library\Helpers\ReportHelper();
        });
        App::bind('setting.helper', function () {
            return new \App\Helpers\SettingHelper();
        });
        App::bind('bank.helper', function () {
            return new \App\Library\Helpers\BankHelper();
        });
        App::bind('investor.transaction', function () {
            return new \App\Library\Helpers\InvestorTransaction();
        });
         App::bind('dynamic.report.helper', function () {
            return new \App\Helpers\DynamicReportHelper(
                 App::make(\Yajra\DataTables\Html\Builder::class)
            );
        });
        App::bind('template', function () {
            return new \App\Library\Helpers\TemplateHelper(
                App::make(\App\Library\Repository\Interfaces\ITemplateRepository::class),
            );
        });  
        
        App::bind('payment.helper', function () {
            return new \App\Helpers\PaymentHelper(
                App::make(\App\Library\Repository\Interfaces\IParticipantPaymentRepository::class),
                App::make(\App\Library\Repository\Interfaces\IRoleRepository::class),
                App::make(\App\Library\Repository\Interfaces\IMerchantRepository::class),
                App::make(\App\Library\Repository\Interfaces\IMNotesRepository::class),
                App::make(\App\Library\Repository\Interfaces\IInvestorRepository::class),
            );
        });
        App::bind('crm.helper', function () {
            return new \App\Helpers\CRMHelper(
                App::make(\App\Library\Repository\Interfaces\IMerchantRepository::class),
                App::make(\App\Library\Repository\Interfaces\IRoleRepository::class),
            );
        });

        App::bind('faqMerchant.helper', function () {
           return new \App\Helpers\FaqMerchantHelper(
           );
       });
        

    }
}
