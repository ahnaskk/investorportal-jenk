<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

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
            // $this->branch_manager();
            $this->Companies();
            // $this->Admin();
            // $this->Editor();
            $this->Lender();
            // $this->Viewer();
            $this->Roles_Permissions();
            $this->Merchants();
            $this->market_offers();
            // $this->Transactions();
            $this->Reconcilation();
            // $this->Velocity_distributions();
            // $this->merchant_batches();
            $this->Payments();
            $this->Investor_ACH();
            // $this->Marketplace();
            $this->Report();
            $this->Logs();
            // $this->Bank_details();
            // $this->Reconcile();
            $this->template_management();
            $this->Settings();
            // $this->penny_adjustement();
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
        $this->browser
                    ->clickLink('Accounts')
        ->clickLink('All Accounts')
        ->visit('/admin/investors')
        ->pause('10000')
        // ->clickLink('Portfolio')
        ->visit('/admin/investors/portfolio/26')
        // ->clickLink('Send To Velocity(ACH Debit)')
        ->visit('/admin/investors/achRequest/26')
        // ->press('transaction_type')
        ->assertPathIs('/admin/investors/achRequest/26')
        // ->clickLink('Bank +')
        ->visit('/admin/investors/bank_details/26')
        // ->clickLink('Create Bank Account')
        ->visit('/admin/investors/bankCreate/26')
        ->press('Create')
        // ->clickLink('Back to list')
        ->visit('/admin/investors/bank_details/26')
        ->visit('/admin/investors/bankCreate/26')
        ->visit('/admin/investors/achRequest/26')
        // ->clickLink('Edit')
        ->visit('/admin/investors/edit/26')
        // ->press('Update')
        // ->clickLink('Portfolio')
        ->visit('/admin/investors/portfolio/26')
        // ->clickLink('Accounts')
        // ->clickLink('All Accounts')
        ->visit('/admin/investors')
        ->pause('10000')
        ->press('download')
        // ->clickLink('Create Account')
        ->visit('/admin/investors/create')
        // ->clickLink('View Accounts')
        ->visit('/admin/investors')
        ->pause('10000')
        // ->clickLink('Transactions')
        ->visit('/admin/investors/transactions/26')

        ->visit('/admin/investors/transactions/26/20/edit')
        ->press('Update')
        ->visit('/admin/investors/transactions/26')
        // ->clickLink('View Lists')
        ->visit('/admin/investors/transactions/26')
        ->visit('/admin/investors/transactions/26/20/edit')
        ->visit('/admin/investors')
        ->pause('10000')
        // ->clickLink('Documents')
        ->visit('/admin/merchant_investor/documents_upload/26')
        // ->clickLink('View')
        // ->press('update')
        // ->assertPathIs('/admin/investors')
        // ->clickLink('Edit')
        ->visit('/admin/investors/edit/26')
        // ->press('Update')
        // ->clickLink('Portfolio')
        ->visit('/admin/investors/portfolio/26')
        ->visit('/admin/investors/edit/26')
        ->visit('/admin/investors')
        ->pause('10000')
        // ->clickLink('Bank')
        ->visit('/admin/investors/bank_details/26')
        // ->clickLink('Create Bank Account')
        ->visit('/admin/investors/bankCreate/26')
        ->press('Create')
        // ->clickLink('Back to list')
        ->visit('/admin/investors/bank_details/26')
        ->visit('/admin/investors')
        ->pause('10000')
        // ->clickLink('Generate PDF')
        ->visit('/admin/pdf_for_investors')
        ->visit('/admin/investors')
        ->pause('10000')
        // ->clickLink('Portfolio')
        ->visit('/admin/investors/portfolio/26')
        // ->clickLink('Send To Velocity(ACH Debit)')
        ->visit('/admin/investors/achRequest/26')
        ->visit('/admin/investors/portfolio/26')
        // ->clickLink('Send To User Bank(ACH Credit)')
        ->visit('/admin/investors/achRequest/Credit/26')
        // ->press('transaction_type')
        ->assertPathIs('/admin/investors/achRequest/Credit/26')
        // ->clickLink('Bank +')
        ->visit('/admin/investors/bank_details/26')
        // ->clickLink('Create Bank Account')
        ->visit('/admin/investors/bankCreate/26')
        ->press('Create')
        // ->clickLink('Back to list')
        ->visit('/admin/investors/bank_details/26')
        ->visit('/admin/investors/bankCreate/26')
        ->visit('/admin/investors/bank_details/26')
        ->visit('/admin/investors/achRequest/Credit/26')
        // ->clickLink('Edit')
        ->visit('/admin/investors/edit/26')
        // ->press('Update')
        // ->clickLink('Portfolio')
        ->visit('/admin/investors/portfolio/26')
        // ->clickLink('Credit Card')
        ->visit('/admin/investors/creditcard-payment/26')
        // ->press('Submit')
        // ->assertPathIs('/admin/investors/portfolio/26')
        // ->clickLink('Edit')
        ->visit('/admin/investors/edit/26')
        // ->press('Update')
        // ->clickLink('Portfolio')
        ->visit('/admin/investors/portfolio/26')
        // ->clickLink('Transactions')
        ->visit('/admin/investors/transactions/26')
        ->press('download')
        // ->clickLink('Create Transactions')
        ->visit('/admin/investors/transactions/26/create')
        // ->press('Create')
        // ->clickLink('View Lists')
        ->visit('/admin/investors/transactions/26')
        ->visit('/admin/investors/transactions/26/create')
        ->visit('/admin/investors/transactions/26')
        ->visit('/admin/investors/portfolio/26')
        // ->clickLink('Documents')
        ->visit('/admin/merchant_investor/documents_upload/26')
        // ->clickLink('View')
        // ->press('update')
        // ->assertPathIs('/admin/investors/portfolio/26')
        // ->clickLink('Bank')
        ->visit('/admin/investors/bank_details/26')
        // ->clickLink('Create Bank Account')
        ->visit('/admin/investors/bankCreate/26')
        ->press('Create')
        // ->clickLink('Back to list')
        ->visit('/admin/investors/bank_details/26')
        ->visit('/admin/investors/bankCreate/26')
        ->visit('/admin/investors/bank_details/26')
        ->visit('/admin/investors/portfolio/26')
        // ->clickLink('Accounts')
        // ->clickLink('Create Account')
        ->visit('/admin/investors/create')
        // ->press('Create')
        // ->clickLink('View Accounts')
        ->visit('/admin/investors')
        ->pause('10000')
        // ->clickLink('Generate PDF For Investors')
        ->visit('/admin/pdf_for_investors')
        // ->clickLink('FAQ')
        ->visit('/admin/investors/faq')
        // ->clickLink('Create New')
        ->visit('admin/investors/faq/create')
        // ->clickLink('Cancel')
        ->visit('/admin/investors/faq')
        // ->clickLink('Create New')
        ->visit('admin/investors/faq/create')
        ->press('Create')
        // ->clickLink('Cancel')
        ->visit('/admin/investors/faq');
        // ->clickLink('Edit')
        // ->visit('/admin/faq/2/edit')
        // // ->press('Update')
        // ->visit('/admin/faq/2/edit');
    }

public function transactions()
{
    
$this->browser

          ->clickLink('Transactions')
           ->visit('/admin/investors/transaction-report')
           ->press('Download report')
           ->clickLink('Add Transactions')
           ->visit('/admin/merchants/investor-transactions')
           ->press('View Investors')
           ->clickLink('Reset')
           ->visit('/admin/merchants/investor-transactions');



}
    public function branch_manager()
    {
        $this->browser
           ->clickLink('Branch Manager')
            ->clickLink('All Branch Manager')
            ->visit('/admin/branch_manager')
            ->clickLink('Create Branch Manager')
            ->visit('/admin/branch_manager/create')
            ->clickLink('View Branch Manager')
            ->visit('/admin/branch_manager')
            ->clickLink('Create Branch Manager')
            ->visit('/admin/branch_manager/create')
            // ->press('Create')
            ->clickLink('View Branch Manager')
            ->visit('/admin/branch_manager')
            // ->clickLink('Edit')
            ->visit('/admin/branch_manager/edit/6')
            ->assertPathIs('/admin/branch_manager/edit/6')
            ->clickLink('View Branch Manager')
            ->visit('/admin/branch_manager')
            ->clickLink('Create Branch Manager')
            ->visit('/admin/branch_manager/create')
            ->clickLink('View Branch Manager')
            ->visit('/admin/branch_manager')
            ->visit('/admin/branch_manager/create')
            ->press('Create')
            ->clickLink('View Branch Manager')
            ->visit('/admin/branch_manager');
    }

    public function Companies()
    {
        $this->browser
            ->clickLink('Companies')
            ->clickLink('All Companies')
            ->visit('/admin/sub_admins')
            ->clickLink('Create Companies')
            ->visit('/admin/sub_admins/create')
            // ->press('Create')
            ->clickLink('View Compaines')
            ->visit('/admin/sub_admins')
            // ->clickLink('Edit')
            ->visit('/admin/sub_admins/edit/58')
            ->assertPathIs('/admin/sub_admins/edit/58')
            ->clickLink('View Compaines')
            ->visit('/admin/sub_admins')
            ->clickLink('Create Companies')
            ->visit('/admin/sub_admins/create')
            // ->press('Create')
            ->clickLink('View Compaines')
            ->visit('/admin/sub_admins')
            // ->clickLink('Edit')
            ->visit('/admin/sub_admins/edit/58')
            ->assertPathIs('/admin/sub_admins/edit/58')
            ->clickLink('View Compaines')
            ->visit('/admin/sub_admins');
    }

    public function Admin()
    {
        $this->browser
            ->clickLink('Admin')
            ->clickLink('All Admin')
           ->visit('/admin/sub_admins/create')
           ->clickLink('Admin')
           ->clickLink('All Admin')
           ->visit('/admin/admin')
           ->clickLink('Create Admin Users')
           ->visit('/admin/admin/create')
           ->clickLink('View Admin')
           ->visit('/admin/admin')
           // ->clickLink('Edit')
           ->visit('/admin/admin/edit/1')
           ->press('Update')
           ->clickLink('View Admin')
           ->visit('/admin/admin')
           ->clickLink('Create Admin')
           ->visit('/admin/admin/create')
           ->clickLink('View Admin')
           ->visit('/admin/admin');
    }

    public function Editor()
    {
        $this->browser
            ->clickLink('Editor')
            ->clickLink('All Editors')
            ->visit('/admin/editor')
            ->clickLink('Create Editor')
            ->visit('/admin/editor/create')
            ->press('Create')
            ->clickLink('View Editors')
            ->visit('/admin/editor')
            // ->clickLink('Edit')
            ->visit('/admin/editor/edit/38')
            ->press('Update')
            ->assertPathIs('/admin/editor/edit/38')
            ->clickLink('View Editors')
            ->visit('/admin/editor')
            ->clickLink('Create Editor')
            ->visit('/admin/editor/create')
            ->press('Create')
            ->clickLink('View Editors')
            ->visit('/admin/editor');
    }

    public function Lender()
    {
        $this->browser
            ->clickLink('Lender')
            ->clickLink('All Lenders')
            ->visit('/admin/lender')
            ->clickLink('Create Lender')
            ->visit('/admin/lender/create')
            ->press('Create')
            ->clickLink('View Lenders')
            ->visit('/admin/lender')
            // ->clickLink('Edit')
            ->visit('/admin/lender/edit/16')
            ->assertPathIs('/admin/lender/edit/16')
            ->clickLink('View Lenders')
            ->visit('/admin/lender')
            ->clickLink('Create Lender')
            ->visit('/admin/lender/create')
            ->clickLink('View Lenders')
            ->visit('/admin/lender')
            ->clickLink('Lender Settings')
            ->visit('/admin/lenderActivation')
            ->visit('/admin/lender');
    }

    public function Viewer()
    {
        $this->browser
             ->clickLink('Viewer')
            ->clickLink('All Viewers')
            ->visit('/admin/viewer')
            ->clickLink('Create Viewer')
            ->visit('/admin/viewer/create')
            ->press('Create')
            ->clickLink('View Viewer')
            ->visit('/admin/viewer')
            ->clickLink('Create Viewer')
            ->visit('/admin/viewer/create')
            ->press('Create')
            ->clickLink('View Viewer')
            ->visit('/admin/viewer');
    }

    public function Roles_Permissions()
    {
        $this->browser
          ->clickLink('Roles and Permissions')
          ->clickLink('Users and Roles')
          ->visit('/admin/role/show-user-role')
          ->clickLink('Create User')
          ->visit('/admin/role/create-user')
          ->press('Create')
          ->clickLink('View Users')
          ->visit('/admin/role/show-user-role')
          // ->clickLink('Edit')
          ->visit('/admin/role/user-role/edit/1')
          ->clickLink('View Users')
          ->visit('/admin/role/show-user-role')
          ->visit('/admin/firewall/1')
          ->visit('/admin/role/user-user-permissions/edit/1')
          ->clickLink('Roles and Permissions')
          ->visit('/admin/role')
          // ->clickLink('Create Role')
          ->visit('/admin/role/create-role')
          ->press('Create')
          ->assertPathIs('/admin/role/create-role')
          ->clickLink('View Roles')
          ->visit('/admin/role')
          ->clickLink('Permissions')
          ->visit('/admin/role/edit/1')
          ->clickLink('View Roles')
          ->visit('/admin/role')
          ->clickLink('Modules')
          ->visit('/admin/role/show-modules')
          ->clickLink('Create Module')
          ->visit('/admin/role/create-module')
          ->clickLink('View Modules')
          ->visit('/admin/role/show-modules')
          ->clickLink('User Firewall')
          ->visit('/admin/firewall')
          // ->clickLink('Edit')
          ->visit('/admin/firewall/1')
          ->press('Add IP')
          ->assertPathIs('/admin/firewall/1')
          ->visit('/admin/firewall');
    }

    public function Merchants()
    {
        $this->browser
            ->clickLink('Merchants')
            ->clickLink('All Merchants')
            ->visit('/admin/merchants')
            // ->clickLink('Notes (0)')
            ->visit('/admin/merchants')
            // ->clickLink('Requests')
            ->visit('/admin/merchants/requests/view/9006')
            ->clickLink('Merchant Lists')
            ->visit('/admin/merchants')
            // ->clickLink('Edit')
            ->visit('/admin/merchants/edit/9006')
            ->visit('/admin/merchants')
            ->visit('/admin/merchants/view/9820')
            ->clickLink('Upload Docs')
            ->visit('/admin/merchants/9820/documents')
            ->visit('/admin/merchants/view/9820')
            ->clickLink('Notes (0)')
            ->visit('/admin/notes/9820/update')
            ->press('Create')
            // ->clickLink('View
            //                     Merchant')
            ->visit('/admin/merchants/view/9820')
             ->clickLink('Bank')
            ->visit('/admin/merchants/9006/bank_accounts')
            ->clickLink('Create Bank Account')
            ->visit('/admin/merchants/9006/bank_accounts/create')
            ->press('Create')
            ->clickLink('Back to list')
            ->visit('/admin/merchants/9006/bank_accounts')
            ->visit('/admin/merchants/view/9820')
             ->clickLink('ACH Terms')
            ->visit('/admin/merchants/9820/terms')
             ->clickLink('View')
            ->visit('/admin/merchants/view/9820')
            // ->clickLink('Open Last Statement')
            // ->visit('/admin/merchants/view/9820')
            // ->clickLink('Re-Generate')
            //  ->visit('/admin/merchants/view/9820')
             ->clickLink('Add Payment')
            ->visit('/admin/payment/create/9820')
            ->clickLink('View merchant')
            ->visit('/admin/merchants/view/9820')
            ->clickLink('Log')
            ->visit('/admin/merchants/activity-logs/9820')
            ->visit('/admin/merchants/view/9820')
            ->clickLink('PayOff Letter')
            ->visit('/admin/merchants/payoffLetterForMerchants/9820')
            ->visit('/admin/merchants/view/9820')
            ->clickLink('Upload Docs')
            ->visit('/admin/merchants/9820/documents')
            ->visit('/admin/merchants/view/9820')
            // ->clickLink('Date Wise Investor Payment')
            //   ->visit('/admin/merchants/view/9820')
            // ->clickLink('Edit')
            ->visit('/admin/merchant_investor/edit/83293')
            ->visit('/admin/merchants/edit/9820')
            ->visit('/admin/merchants/view/9820')
            // ->press('Update')
            // ->visit('/admin/merchants/view/9820')
            ->clickLink('Merchants')
            ->clickLink('Create Merchants')
            ->visit('/admin/merchants/create')
            ->clickLink('List Merchant')
            ->visit('/admin/merchants')
           //  ->visit('/admin/merchants/view/8408')
           //  ->select('sub_status_id','1')
           //  ->pause('6000')
           //   ->clickLink('Yes')
           //   ->visit('/admin/merchants/view/8408')
           //   ->clickLink('Assign Investors')
           //   ->pause('1000')
           //    ->visit('/admin/merchants/view/8408')
           // ->clickLink('New Investor')
           // ->visit('/admin/merchant_investor/create/8408')
           // ->clickLink('Edit Merchant')
           // ->visit('/admin/merchants/edit/8408')
           // ->visit('/admin/merchant_investor/create/8408')
           // ->press('Update')
           // ->clickLink('View Merchant')
           // ->visit('/admin/merchants/view/8408')
           // ->clickLink('Assign New Investor')
           // ->visit('/admin/merchants/assign-investor/8408')
           // ->press('add_btn')
           //  ->visit('/admin/merchants/assign-investor/8408')
           // ->clickLink('View merchant')
           // ->visit('/admin/merchants/view/8408')
           //  ->visit('/admin/merchants')
           // ->clickLink('Merchants')
            ->clickLink('Graph')
            ->visit('/admin/percentage_deal')
            ->clickLink('Clear Filter')
            ->visit('/admin/percentage_deal')
            ->clickLink('Update Graph')
            ->visit('/admin/percentage_deal')
            ->clickLink('Change to Default')
            ->visit('/admin/change_merchant_status')
            ->visit('/admin/percentage_deal')
            ->visit('/admin/merchants')
            ->clickLink('Change to Advanced Status')
            ->visit('/admin/change_advanced_status')
            ->clickLink('Generate Statement')
            ->visit('/admin/pdf_for_merchants')
            ->clickLink('Generated Statement')
            ->visit('/admin/generated_pdf_merchants');
    }

    public function market_offers()
    {
        $this->browser

            ->clickLink('Marketing Offers')
            ->clickLink('Create Merchant Offers')
            ->visit('/admin/addEditMerchantsOffers')
            // ->press('Create')
            ->clickLink('View Offers')
            ->visit('/admin/merchantMarketOfferList')
            // ->clickLink('Edit')
            ->visit('/admin/addEditMerchantsOffers')
            // ->press('Update')
            ->clickLink('View Offers')
            ->visit('/admin/merchantMarketOfferList')
            ->clickLink('Create Offers')
            ->visit('/admin/addEditMerchantsOffers')
            // ->press('Create')
            ->clickLink('View Offers')
            ->visit('/admin/merchantMarketOfferList')
            ->clickLink('Merchant Offers List')
            ->visit('/admin/merchantMarketOfferList')
            ->clickLink('Create Offers')
            ->visit('/admin/addEditMerchantsOffers')
            // ->press('Create')
            ->clickLink('View Offers')
            ->visit('/admin/merchantMarketOfferList')
            // ->clickLink('Edit')
            ->visit('/admin/addEditMerchantsOffers')
            // ->press('Update')
            ->clickLink('View Offers')
            ->visit('/admin/merchantMarketOfferList')
            ->clickLink('Create Investor Offers')
            ->visit('/admin/addEditInvestorsOffers')
            // ->press('Create')
            ->clickLink('View Offers')
            ->visit('/admin/investorMarketOfferList')
            // ->clickLink('Edit')
            ->visit('/admin/addEditInvestorsOffers')
            // ->press('Update')
            ->clickLink('View Offers')
            ->visit('/admin/investorMarketOfferList')
            ->clickLink('Investors Offers List')
            ->visit('/admin/investorMarketOfferList')
            ->clickLink('Create Offers')
            ->visit('/admin/addEditInvestorsOffers')
            // ->press('Create')
            ->clickLink('View Offers')
            ->visit('/admin/investorMarketOfferList')
            // ->clickLink('Edit')
            ->visit('/admin/addEditInvestorsOffers')
            // ->press('Update')
            ->clickLink('View Offers')
            ->visit('/admin/investorMarketOfferList');
    }

    public function Reconcilation()
    {
        $this->browser

             ->clickLink('Reconciliation Request')
             ->visit('/admin/merchants/reconcilation-request');
    }

    public function Velocity_distributions()
    {
        $this->browser
          ->clickLink('Velocity Distributions')
            ->clickLink('All Distributions')
            ->visit('/admin/vdistribution')
            ->visit('/admin/vdistribution/edit/179')
             ->press('Update')
            ->assertPathIs('/admin/vdistribution/edit/179')
            ->press('×')
            ->clickLink('Back To Lists')
            ->visit('/admin/vdistribution')
            ->clickLink('Create Velocity Distribution')
            ->visit('/admin/vdistribution/createVdistribution')
            ->press('Create')
            ->clickLink('View lists')
            ->visit('/admin/vdistribution');
    }

    public function merchant_batches()
    {
        $this->browser
             ->clickLink('Merchant Batches')
            ->clickLink('All Batches')
            ->visit('/admin/merchant_batches')
            ->clickLink('Add Batches')
            ->visit('/admin/merchant_batches/create')
            ->press('Create')
            ->clickLink('Back to lists')
            ->visit('/admin/merchant_batches')
            // ->clickLink('Edit')
            ->visit('/admin/merchant_batches/edit/5')
            ->clickLink('Back to lists')
            ->visit('/admin/merchant_batches')
            ->clickLink('Create New Batch')
            ->visit('/admin/merchant_batches/create')
            ->clickLink('Back to lists')
            ->visit('/admin/merchant_batches');
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
            ->clickLink('Send Merchant ACH')
            ->pause('6000')
           ->visit('/admin/payment/ach-payment')
           // ->press('Apply Filter')
           // ->assertPathIs('/admin/payment/ach-payment')
           ->visit('/admin/merchants/9302/terms')
           ->clickLink('View')
            ->pause('6000')
           ->visit('/admin/merchants/view/9302')
           ->clickLink('ACH Terms')
            ->pause('6000')
            ->visit('/admin/merchants/9302/terms')
            ->clickLink('View')
             ->pause('6000')
           ->visit('/admin/merchants/view/9245')
           ->visit('/admin/merchants/9302/terms')
           ->clickLink('Add Term')
            ->pause('6000')
           ->visit('/admin/merchants/9302/terms/create')
           ->clickLink('Back')
           ->visit('/admin/merchants/9302/terms')
            ->visit('/admin/merchants/9302/terms')
             ->clickLink('Payments')
              ->pause('6000')
         ->clickLink('Merchant ACH Status Check')
             ->pause('6000')
           ->visit('/admin/payment/ach-requests')
            ->press('Download')
           ->clickLink('Merchant ACH Fees')
               ->pause('6000')
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
        $this->browser

          ->clickLink('Template Management')
            ->clickLink('View Template')
            ->visit('/admin/template')
            ->visit('/admin/template/create')
            ->visit('/admin/template')
            ->visit('/admin/template/edit/1')
            ->visit('/admin/template');
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

