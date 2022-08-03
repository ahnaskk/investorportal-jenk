<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\User;
use DB;
use App\Merchant;
use App\Module;
use App\Template;
use App\MerchantUser;
use App\MarketOffers;
use App\Models\Views\InvestorAchTransactionView;
use Spatie\Permission\Models\Role;
class ExampleTest extends DuskTestCase
{
    /**
     * A Dusk test testReport.
     *
     * @return void
     *
     * @author Priya
     */
    protected $browser;

    public function testExample()
    {
        $this->browse(function (Browser $browser) {
            $this->browser = $browser;
            $this->Login();
            $this->Accounts();          
            $this->transactions();
            $this->Companies();
            $this->Lender();
            $this->Roles_Permissions();
            $this->Merchants();
            $this->market_offers();
            $this->Reconcilation();
            $this->Payments();
            $this->Investor_ACH();
            $this->Report();
            $this->Logs();
            $this->template_management();
            $this->Settings();
            $this->Logout();
        });
    }

    public function Login()
    {
        $this->browser->visit('/login')
                    ->type('email', '1email.33433@iocod.com')
                    ->type('password', 'admin987987')
                    ->press('Login')
                    ->visit('/admin/dashboard')
                    ->assertPathIs('/admin/dashboard');
    }

    public function Accounts()
    {
        $this->browser->visit('/admin/dashboard');
      //     // ->clickLink('Accounts')
      //     // ->clickLink('All Accounts')
         $this->browser->visit('/admin/investors');
      //    $this->browser->press('download');
      $firstInvestor = (new Role())->whereName('investor')->first()->users->where('company_status',1)->first();
      if($firstInvestor){
          $invsetor_id=$firstInvestor->id;
          $this->browser->visit("/admin/investors/portfolio/$invsetor_id");
          $this->browser->visit("/admin/investors/achRequest/$invsetor_id");
          $this->browser->clicklink("Bank +");
          $this->browser->visit("/admin/investors/bank_details/$invsetor_id");
          $this->browser->clicklink("Create Bank Account");
          $this->browser->visit("admin/investors/bankCreate/$invsetor_id");
          $this->browser->press("Create");
          $this->browser->clickLink('Back to list');
          $this->browser->visit("/admin/investors/portfolio/$invsetor_id");
          $this->browser->visit("Transfer To bank");
          $this->browser->visit("/admin/investors/achRequest/Credit/$invsetor_id");
          $this->browser->visit("/admin/investors/edit/$invsetor_id");
          $this->browser->visit("/admin/investors/transactions/$invsetor_id");
          $this->browser->visit("/admin/merchant_investor/documents_upload/$invsetor_id");
          $this->browser->visit("/admin/investors/investor-reserve-liquidity/$invsetor_id");
          $this->browser->visit("/admin/investors/bank_details/$invsetor_id");
          $this->browser->visit("/admin/pdf_for_investors?id=$invsetor_id");
          $this->browser->visit("/admin/reports/AdvancePlusInvestments/$invsetor_id");
          $this->browser->visit("/admin/investors/bankCreate/$invsetor_id");

      }
      $this->browser->visit('/admin/dashboard');
      $this->browser->clicklink("Accounts");
      $this->browser->clicklink("Create Account");
      $this->browser->press("Create");
      $this->browser->clicklink("View Accounts");
      $this->browser->visit("/admin/investors/create");
      $this->browser->clicklink("Generate Statement");
      $this->browser->press("Generate Statement");
      $this->browser->clicklink("Generated PDF/CSV");
      $this->browser->visit("/admin/generatedPdfCsv");
      $this->browser->clicklink("FAQ");
      $this->browser->visit("/admin/investors/faq");
      $this->browser->clicklink("Create New");
      $this->browser->visit("/admin/investors/faq/create"); 
    //   $this->browser->asserPathIs("/admin/investors/faq/create");    
      $this->browser->clicklink("Cancel");
    }


    public function transactions()
    {
        $this->browser->clickLink('Transactions');
        $this->browser->visit('/admin/investors/transaction-report');
        $this->browser->press('Download report');
        $transaction=InvestorAchTransactionView::latest()->first();
        if($transaction){

            $this->browser->visit("/admin/investors/transactions/$transaction->investor_id/$transaction->id/edit");
            $this->browser->clicklink("View Lists");
            $this->browser->visit("/admin/investors/transactions/$transaction->investor_id/$transaction->id/edit");
            $this->browser->clicklink("Edit");
            $this->browser->press("Update");
            $this->browser->visit("/admin/investors/transactions/$transaction->investor_id");

        }
        $this->browser->visit("/admin/investors/transactions/$transaction->investor_id");
        $this->browser->clickLink("Transactions");
        $this->browser->clickLink("Add Transactions");
        $this->browser->visit("/admin/merchants/investor-transactions");
        $this->browser->press("View Investors");
        $this->browser->clickLink('Reset');
        $this->browser->visit('/admin/merchants/investor-transactions'); 
    }
  
    public function Companies()
    {
        $company =  (new Role())->whereName('company')->first()->users->first();
            $this->browser->clickLink('Companies');
            $this->browser->clickLink('All Companies');
            $this->browser->visit('/admin/sub_admins');
            $this->browser->clickLink('Create Companies');
            $this->browser->visit('/admin/sub_admins/create');
            // $this->browser->press('Create');
            $this->browser->visit('/admin/sub_admins');
            // $this->browser->clickLink('Edit');
            if($company){
                $company_id=$company->id;
                $this->browser->visit('/admin/sub_admins');
                $this->browser->visit("/admin/sub_admins/edit/$company_id");
                $this->browser->assertPathIs("/admin/sub_admins/edit/$company_id");
                $this->browser->visit("/admin/sub_admins/edit/$company_id");
                $this->browser->visit('/admin/sub_admins');

            }
            $this->browser->clickLink('Companies');
            $this->browser->clickLink('Create Companies');
            $this->browser->visit('/admin/sub_admins/create');
    
            $this->browser->visit('/admin/sub_admins');
          
    }


    public function Lender()
    {
        $firstLender = (new Role())->whereName('lender')->first()->users->where('active_status', 1)->first();
        if($firstLender){
            $lender_id=$firstLender->id;
            $this->browser->visit("/admin/lender");
            $this->browser->visit("/admin/lender/create");
            $this->browser->press("Create");
            $this->browser->Clicklink("View Lenders");
            $this->browser->visit("/admin/lender");
            $this->browser->visit("/admin/lender/view/$lender_id");
            $this->browser->Clicklink("View Lenders");
            $this->browser->visit("/admin/lender/edit/$lender_id");
            $this->browser->Clicklink("View Lenders");
            $this->browser->visit("/admin/lender");
            $this->browser->visit("/admin/lender/edit/$lender_id");
            $this->browser->press("Update");

        }
    }


    public function Roles_Permissions()
    {
        $this->browser->clickLink('Roles and Permissions');
        $this->browser->clickLink('Users and Roles');
        $this->browser->visit('/admin/role/show-user-role');
        $this->browser->clickLink('Create User');
        $this->browser->visit('/admin/role/create-user');
        $this->browser->press('Create');
        $this->browser->clickLink('View Users');
    //   $this->browser->clickLink('Edit');
        $user_has_roles = User::select('users.creator_id', 'users.created_at', 'users.updated_at', 'users.name', 'users.email', 'users.id')->join('user_has_roles', 'user_has_roles.model_id', 'users.id')->join('roles', 'roles.id', 'user_has_roles.role_id')->where('company_status',1)->first();
        if($user_has_roles){
            $user_has_role_id=$user_has_roles->id;
            $this->browser->visit("/admin/role/user-role/edit/$user_has_role_id");
            $this->browser->press("Update");
            $this->browser->visit('/admin/role/show-user-role');
            $this->browser->visit("/admin/role/user-user-permissions/edit/$user_has_role_id");
            $this->browser->visit('/admin/role/show-user-role');
            $this->browser->visit("/admin/firewall/$user_has_role_id");
            $this->browser->visit('/admin/role/show-user-role');
        }

        $this->browser->clicklink('Roles and Permissions');
        $this->browser->visit("/admin/role");
        $this->browser->clicklink("Create");
        $this->browser->visit("/admin/role/create-role");
        $this->browser->press("Create");
        $this->browser->assertPathIs("/admin/role/create-role");
        $this->browser->clicklink("View Roles");
        $this->browser->assertPathIs("/admin/role");
      $this->browser->clickLink('Permissions');
        $roles = DB::table('roles')->first();
        if($roles){
            $role_id = $roles->id;
            $this->browser->visit("/admin/role/edit/$role_id");
            $this->browser->pause(2000);
        }
      $this->browser->clickLink('View Roles');
      $this->browser->visit('/admin/role');
      $this->browser->clickLink('Modules');
      $this->browser->visit("/admin/role/show-modules");
      $this->browser->clickLink('Create Module');
      $this->browser->assertPathIs("/admin/role/create-module");
      $this->browser->press("Create");
      $this->browser->assertPathIs("/admin/role/create-module");
      $this->browser->clicklink("View Modules");
      $this->browser->assertPathIs("/admin/role/show-modules");
      $Module = Module::first();
        if($Module){
            $module_id = $Module->id;
            $this->browser->visit("/admin/role/edit-module/$module_id");
           $this->browser->press("Update");
           $this->browser->assertPathIs("/admin/role/show-modules");
           $this->browser->visit("/admin/role/edit-module/$module_id");
           $this->browser->clicklink("View Modules");
           $this->browser->assertPathIs("/admin/role/show-modules");

        }
        $firewalls = User::where('company_status',1)->withCount('firewalls')->first();
        if($firewalls){
            $firewall_id = $firewalls->id;

            
        $this->browser->clickLink('User Firewall');
        $this->browser->assertPathIs("/admin/firewall");
        // $this->browser->clickLink('Edit');
        $this->browser->visit("/admin/firewall/$firewall_id");
        $this->browser->press('Add IP');
        $this->browser->visit("/admin/firewall/$firewall_id");
        $this->browser->visit('/admin/firewall');

   }
    }
    public function Merchants()
    {
        $this->browser->clickLink('Merchants');
        $this->browser->clickLink('All Merchants');
        $this->browser->visit('/admin/merchants');
        $Merchant = Merchant::first();
        if($Merchant){
            $merchant_id = $Merchant->id;

           
            $this->browser->visit("/admin/merchants/requests/view/$merchant_id");
            $this->browser->visit("/admin/merchants/edit/$merchant_id");
            $this->browser->visit("/admin/merchants/view/$merchant_id");
             $this->browser->visit("/admin/notes/$merchant_id/update");
            // $this->browser->press("Create");
            // $this->browser->pause("3000");
            // $this->browser->view("/admin/notes/$merchant_id/update");
            // $this->browser->pause("3000");
            // $this->browser->clicklink("View Merchant");
            // $this->browser->pause("3000");
            $this->browser->visit("/admin/merchants/view/$merchant_id");
            $this->browser->clicklink("Credit Card");
            $this->browser->assertPathIs("/admin/merchants/creditcard-payment/$merchant_id");
            $this->browser->visit("/admin/merchants/view/$merchant_id"); 
            $this->browser->clicklink("Bank"); 
            $this->browser->assertPathIs("/admin/merchants/$merchant_id/bank_accounts");
            $this->browser->visit("/admin/merchants/$merchant_id/bank_accounts");
            // $this->browser->clicklink("ACH Terms");
            $this->browser->visit("/admin/merchants/$merchant_id/terms");
            $this->browser->assertPathIs("/admin/merchants/$merchant_id/terms");
           
            $this->browser->clicklink("View");
            $this->browser->assertPathIs("/admin/merchants/view/$merchant_id");
            $this->browser->visit("/admin/merchants/$merchant_id/terms/create");
            $this->browser->press("Submit");
            $this->browser->visit("/admin/merchants/view/$merchant_id");
            $this->browser->clicklink("Add Payment");
            $this->browser->assertPathIs("/admin/payment/create/$merchant_id");
            $this->browser->visit("/admin/merchants/view/$merchant_id");
            $this->browser->clicklink("Log");
            $this->browser->visit("/admin/merchants/activity-logs/$merchant_id");
            $this->browser->visit("/admin/merchants/view/$merchant_id");
            $this->browser->clicklink("PayOff Letter");
            $this->browser->visit("/admin/merchants/payoffLetterForMerchants/$merchant_id");
            $this->browser->visit("/admin/merchants/view/$merchant_id");
            $this->browser->clicklink("Upload Docs");
            $this->browser->assertPathIs("/admin/merchants/$merchant_id/documents");
            $this->browser->visit("/admin/merchants/view/$merchant_id");
            $MerchantUser = MerchantUser::where('merchant_id',$merchant_id)->first();
            if($MerchantUser){
                $user_id          = $MerchantUser->user_id;
                $merchant_user_id = $MerchantUser->id;
                $this->browser->visit("/admin/merchant_investor/$merchant_id/documents/$user_id");
                $this->browser->visit("/admin/merchant_investor/edit/$merchant_user_id");
            }
        }
       $this->browser->clickLink('Merchants');
        $this->browser->clickLink('Create Merchants');
        $this->browser->visit('/admin/merchants/create');
        $this->browser->clickLink('List Merchant');
        $this->browser->visit('/admin/merchants');
        $this->browser->clickLink('Graph');
        $this->browser->visit('/admin/percentage_deal');
        $this->browser->clickLink('Clear Filter');
        $this->browser->visit('/admin/percentage_deal');
        $this->browser->clickLink('Update Graph');
        $this->browser->visit('/admin/percentage_deal');
        $this->browser->clickLink('Change to Default');
        $this->browser->visit('/admin/change_merchant_status');
        $this->browser->visit('/admin/percentage_deal');
        $this->browser->visit('/admin/merchants');
        $this->browser->clickLink('Change to Advanced Status');
        $this->browser->visit('/admin/change_advanced_status');
        $this->browser->clickLink('Generate Statement');
        $this->browser->visit('/admin/pdf_for_merchants');
        $this->browser->clickLink('Generated Statement');
        $this->browser->visit('/admin/generated_pdf_merchants');
    }

    public function market_offers()
    {
       
        
            $this->browser->clickLink('Marketing Offers');
            $this->browser->clickLink('Create Merchant Offers');
            $this->browser->visit("admin/addEditMerchantsOffers");
            $this->browser->press("Create");
            $this->browser->visit("admin/addEditMerchantsOffers");
            $this->browser->clicklink("View Offers");
            $this->browser->visit('/admin/addEditMerchantsOffers');
            // $this->browser->press('Create');
            $this->browser->clickLink('View Offers');
            $this->browser->visit('/admin/merchantMarketOfferList');
            $MarketOffers=MarketOffers::first();
            if($MarketOffers){
                $marketOffers_id = $MarketOffers->id;
                $this->browser->visit("admin/addEditMerchantsOffers?id=$marketOffers_id");
                $this->browser->press("Update");
                $this->browser->visit("admin/addEditMerchantsOffers?id=$marketOffers_id");
                // $this->browser->asserPathIs("")
            }
        
            $this->browser->clickLink('View Offers');
            
            $this->browser->clickLink('Create Offers');
            $this->browser->visit('/admin/addEditMerchantsOffers');
            // $this->browser->press('Create');
            $this->browser->clickLink('View Offers');
            $this->browser->visit('/admin/merchantMarketOfferList');
            $this->browser->clickLink('Merchant Offers List');
            $this->browser->visit('/admin/merchantMarketOfferList');
            $this->browser->clickLink('Create Offers');
            
            // $this->browser->clickLink('Edit');
            $this->browser->visit('/admin/addEditMerchantsOffers');
            // $this->browser->press('Update');
            $this->browser->clickLink('View Offers');
            $this->browser->visit('/admin/merchantMarketOfferList');
            // $this->browser->press('Update');
            $this->browser->clickLink('Investors Offers List');
            $this->browser->visit('/admin/investorMarketOfferList');
            $this->browser->clickLink('Create Offers');
            $this->browser->visit('/admin/addEditInvestorsOffers');
            // $this->browser->press('Create');
            $this->browser->clickLink('View Offers');
            $this->browser->visit('/admin/investorMarketOfferList');
            // $this->browser->clickLink('Edit');
            $this->browser->visit('/admin/addEditInvestorsOffers');
            // $this->browser->press('Update');
            $this->browser->clickLink('View Offers');
            $this->browser->visit('/admin/investorMarketOfferList');
    }

    public function Reconcilation()
    {
        $this->browser

             ->clickLink('Reconciliation Request')
             ->visit('/admin/merchants/reconcilation-request');
    }

    


    public function Payments()
    {
        $this->browser
             ->clickLink('Payments')
            // ->clickLink('Open Items')
            // ->visit('/admin/payment/open-items')
            ->clickLink('Generate Payment For Lenders')
            ->visit('/admin/payment/lender_payment_generation')
            ->press('View')
            ->assertPathIs('/admin/payment/lender_payment_generation')
            ->clicklink('Pending Transactions')
         
            ->visit('admin/payment/PendingTransactions')
            ->press('Apply')
            ->clickLink('Send Merchant ACH')
         ->visit('/admin/payment/ach-payment')  
         ->assertPathIs('/admin/payment/ach-payment')
             ->clickLink('Merchant ACH Status Check')  
           ->visit('/admin/payment/ach-requests')
            ->press('Download')
           ->clickLink('Merchant ACH Fees')
          
           ->visit('/admin/payment/ach-fees')
           ->press('Download');
    }

    public function Investor_ACH()
    {
        $this->browser

          ->clickLink('Investor ACH')
            ->clickLink('Status Check')
            ->visit('/admin/payment/investor/ach-requests')
            ->clickLink('Syndication Payments')
            ->visit('/admin/investors/SyndicationPayments');
    }

    public function Marketplace()
    {
        $this->browser
            ->clickLink('Marketplace')
            ->visit('/admin/marketplace');
    }

    public function Report()
    {
        $this->browser
             ->clickLink('Reports')
            ->clickLink('Default Rate')
              ->pause('6000')
            ->visit('/admin/reports/defaultRateReport')
            ->clicklink('Default Rate (Merchants)')
            ->visit('admin/reports/defaultRateMerchantReport')
            // ->clickLink('Delinquent')
            //  ->pause('3000')
            // ->visit('/admin/reports/delinquent')
            // ->clickLink('Payment Left Report')
            // ->visit('/admin/reports/paymentLeftReport')
            // ->clickLink('Lender Delinquent')
            // ->pause('6000')
            // ->visit('/admin/reports/lenderReport')
            ->clickLink('Profitability(65/20/15)')
            ->visit('/admin/reports/profitability2')
            ->clickLink('Profitability(50/30/20)')
            ->visit('/admin/reports/profitability3')
            ->clickLink('Profitability(50/30/20) - 2021')
            ->visit('/admin/reports/profitability21')
             ->clickLink('Profitability(50/50)')
            ->visit('/admin/reports/profitability4')
            ->clickLink('Investment')
            ->visit('/admin/reports/investor')
            ->clicklink('Upsell Commission')
            ->visit('/admin/reports/upsell-commission')
            ->clickLink('Investor Assignment')
            ->visit('/admin/reports/investorAssignment')
            ->clickLink('Investor Reassignment')
            ->visit('/admin/reports/reassignReport')
            // ->clickLink('Liquidity')
            // ->visit('/admin/reports/liquidityReport')
            ->clickLink('Payments')
            ->visit('/admin/reports/payments')
            // ->clickLink('Revenue Recognition')
            // ->visit('/admin/merchants/export2')
            // ->clickLink('Transactions')
            // ->visit('/admin/investors/transaction-report')
            // ->clickLink('Accrued Pref Return')
            // ->visit('/admin/reports/InvestorAccruedPrefReturn')
            // ->clickLink('Debt Investor')
            // ->pause('5000')
            // ->visit('/admin/reports/investorProfitReport')
            ->clickLink('Equity Investor')
            ->pause('5000')
            ->visit('/admin/reports/equityInvestorReport')
            ->visit('/admin/reports/totalPortfolioEarnings')
            ->pause('5000')
            ->clickLink('OverPayment Report')
            ->visit('/admin/reports/overpayment-report')
            // ->clicklink('Merchants Per Diff')
            // ->visit('/admin/reports/merchant_per_diff')
            ->clickLink('Velocity Profitability')
              ->pause('3000')
            ->visit('/admin/reports/velocity-profitability')
           //   ->clickLink('Anticipated Payment')
           // ->visit('/admin/reports/anticipated-payment')
            // ->clickLink('Investor Liquidity Log')
            // ->visit('/admin/reports/InvestorLiquidityLog')
            // ->clickLink('Investor RTR Balance Log')
            // ->visit('/admin/reports/InvestorRTRBalanceLog')

            ->clicklink('Agent Fee Report')
            ->visit('/admin/reports/agent-fee-report');
    }

    public function Logs()
    {
        $this->browser
             ->clickLink('Logs')
            ->clickLink('Liquidity Log')
            ->visit('/admin/reports/liquidityLog')
            ->clickLink('Merchant Liquidity Log')
            ->visit('/admin/reports/MerchantliquidityLog')
            ->clickLink('Merchant Status Log')
            ->visit('/admin/merchant_status_log')
            // ->clickLink('Activity Log')
            // ->visit('/admin/activity_log')
             ->clickLink('User Activity Log')
            ->visit('/admin/activity-log')
              ->clickLink('Investor Transaction Log')
            ->visit('/admin/investor-transaction-log')
            ->clickLink('Messages Log')
            ->visit('/admin/messages')
             ->clickLink('Mail Log')
             ->visit('/admin/merchants/mail-log');
    }

    public function Bank_details()
    {
        $this->browser
           ->clickLink('Bank Details')
            ->clickLink('Create Account')
            ->visit('/admin/bank')
            ->press('Create')
            ->clickLink('View Accounts')
            ->visit('/admin/viewbank')
            // ->clickLink('Edit')
            ->visit('/admin/bank/edit/1')
            ->clickLink('View Accounts')
            ->visit('/admin/viewbank')
            // ->clickLink('Edit')
            ->visit('/admin/bank/edit/1')
            ->press('Update')
            ->assertPathIs('/admin/bank/edit/1')
            ->clickLink('View Accounts')
            ->visit('/admin/viewbank')
            ->clickLink('View Accounts')
            ->visit('/admin/viewbank')
            ->clickLink('Create Bank Account')
            ->visit('/admin/bank')
            ->clickLink('View Accounts')
            ->visit('/admin/viewbank')
            // ->clickLink('Edit')
            ->visit('/admin/bank/edit/1')
            ->clickLink('View Accounts')
            ->visit('/admin/viewbank');
    }

    public function Reconcile()
    {
        $this->browser
             ->clickLink('Reconcile')
            ->clickLink('Create')
            ->visit('/admin/reconcile/create')
            ->clickLink('List')
            ->visit('/admin/reports/reconcile');
    }

    public function template_management()
    {
        $Template=Template::first();
        $this->browser->clickLink('Template Management');
        $this->browser->clickLink('View Template');
        $this->browser->visit('/admin/template');
        $this->browser->visit('/admin/template/create');
        $this->browser->visit('/admin/template');
        if($Template){
            $template_id = $Template->id;
            $this->browser->visit("/admin/template/edit/$template_id");
        }
        $this->browser->visit('/admin/template');;
    }

    public function Settings()
    {
        $this->browser

            ->clickLink('Settings')
           ->clickLink('Advance Settings')
           ->visit('/admin/settings')
           ->press('Update')
           ->assertPathIs('/admin/settings')
           ->clicklink('System Settings')
           ->visit('admin/settings/system_settings')
           ->press('Update')
           ->assertPathIs('/admin/settings/system_settings')
           ->clickLink('Re-assign')
           ->visit('/admin/re-assign')
           ->clickLink('All Status')
           ->visit('/admin/sub_status')
           ->clickLink('Add Status')
           ->visit('/admin/sub_status/create')
           ->press('Create')
           ->clickLink('View all')
           ->visit('/admin/sub_status')
           // ->clickLink('Edit')
           ->visit('/admin/sub_status/edit/1')
           ->press('Update')
            ->visit('/admin/sub_status')
            ->visit('/admin/sub_status/edit/1')
           ->clickLink('View all')
           ->visit('/admin/sub_status')
           ->clickLink('Label')
           ->visit('/admin/label')
           ->clickLink('Add Label')
           ->visit('/admin/label/create')
           ->press('Create')
           ->clickLink('View all')
           ->visit('/admin/label')
           // ->clickLink('Edit')
           ->visit('/admin/label/edit/1')
           ->press('Update')
           ->visit('/admin/label')
           ->visit('/admin/label/edit/1')
           ->clickLink('View all')
           ->visit('/admin/label')
           ->clickLink('Calender for Holidays')
           ->visit('/admin/fullcalender')
           ->clickLink('Liquidity Adjuster')
           ->visit('/admin/admin/liquidity_adjuster')
           // ->clickLink('Edit')
           ->visit('/admin/admin/create_liquidity_adjuster/26')
           ->press('Update')
            ->visit('/admin/admin/liquidity_adjuster')
             ->visit('/admin/admin/create_liquidity_adjuster/26')
           ->clickLink('View List')
           ->visit('/admin/admin/liquidity_adjuster')
            ->clickLink('Two Factor Authentication')
            ->visit('/admin/two-factor-authentication');
            // ->clickLink('Carry Forwards')
            // ->visit('/admin/carryforwards');

    }

    public function penny_adjustement()
    {
        $this->browser

            ->clickLink('Penny Adjustment')
            ->clickLink('Liquidity Difference')
            ->visit('/PennyAdjustment/LiquidityDifference')
            ->clickLink('Update')
            ->visit('/PennyAdjustment/LiquidityDifference')
            ->clickLink('Merchant Value Difference')
            ->visit('/PennyAdjustment/MerchantValueDifference')
            ->clickLink('Update RTR')
            ->visit('/PennyAdjustment/MerchantValueDifference')
            ->clickLink('Company Amount Difference')
            ->visit('/PennyAdjustment/CompanyAmountDifference')
            ->clickLink('Update')
            ->visit('/PennyAdjustment/CompanyAmountDifference')
            ->clickLink('Update')
            ->visit('/PennyAdjustment/CompanyAmountDifference')
            ->clickLink('Zero Participant Amount')
            ->visit('/PennyAdjustment/ZeroParticipantAmount')
            ->clickLink('Update To Rcode not found')
            ->visit('/PennyAdjustment/ZeroParticipantAmount')
            ->clickLink('Final Participant Share Difference')
            ->visit('/PennyAdjustment/FinalParticipantShare')
            ->clickLink('Merchant Investor Share Difference')
            ->visit('/PennyAdjustment/MerchantInvestorShareDifference')
            ->clickLink('Merchants Fund Amount Check')
            ->visit('/PennyAdjustment/MerchantsFundAmountCheck')
            ->clickLink('Investment Amount Check')
            ->visit('/PennyAdjustment/InvestmentAmountCheck')
            ->clickLink('Penny Investment')
            ->visit('/PennyAdjustment/PennyInvestment');
        // ->clickLink('Remove Penny Investment')
            // ->visit('/PennyAdjustment/RemovePennyInvestment')
    }

    public function Logout()
    {
        $this->browser
            ->clickLink('Logout')
            ->visit('/login');
    }
}
