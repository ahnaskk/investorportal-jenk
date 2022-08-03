<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class editor_update extends DuskTestCase
{
    /**
     * A Dusk test example.
     *
     * @return void
     */
    public function testExample()
    {
        $this->browse(function (Browser $browser) {
            $this->browser = $browser;
            $this->Login();
            $this->accounts();
            $this->merchants();
            $this->reports();
            $this->logs();
            $this->Logout();
        });
    }

    public function Login()
    {
        $this->browser
                    ->visit('/login')
                    ->type('email', 'editor@iocod.com')
                    ->type('password', 'editor@iocod')
                    ->press('Login')
                    ->visit('/admin/dashboard')
                    ->assertPathIs('/admin/dashboard');
}

         public function accounts()

    {
        $this->browser

        ->visit('/admin/dashboard')
            ->clickLink('Accounts')
            ->clickLink('All Accounts')
            ->visit('/admin/investors')
           
            ->visit('/admin/investors/portfolio/26')
            ->clickLink('Edit')
            ->visit('/admin/investors/edit/26')
            ->visit('/admin/investors/portfolio/26')
            ->clickLink('Transactions')
            ->visit('/admin/investors/transactions/26')
            ->visit('/admin/investors/portfolio/26')
            ->clickLink('Documents')
            ->visit('/admin/merchant_investor/documents_upload/26')
            ->visit('/admin/investors/portfolio/26')
            ->clickLink('Bank')
            ->visit('/admin/investors/bank_details/26')
            ->visit('/admin/investors/portfolio/26')
            ->clickLink('Accounts')
            ->clickLink('Create Account')
            ->visit('/admin/investors/create')
            ->type('email', '72email@iocod.com')
            ->type('password', '72email@iocod.com')
            ->clickLink('Generated PDF/CSV')
            ->visit('/admin/generatedPdfCsv')
            ->clickLink('FAQ')
            ->visit('/admin/investors/faq')

            ->visit('/admin/investors/transaction-report')
           // ->press('Download report')
           // ->clickLink('Delete   Selected')
           // ->clickLink('Add Transactions')
           ->visit('/admin/merchants/investor-transactions')
           // ->clickLink('Reset')
           ->visit('/admin/merchants/investor-transactions')
           ->press('View Investors')
           
            ->clickLink('Companies')
            ->clickLink('All Companies')
            ->visit('/admin/sub_admins');
        }


              public function merchants()

    {
        $this->browser

            ->clickLink('Merchants')
            ->clickLink('All Merchants')
            ->visit('/admin/merchants')
            // ->clickLink('View (42)')
            ->visit('/admin/merchants/view/9678')
            // ->clickLink('Edit')
            ->visit('/admin/merchants/edit/9678')
            ->visit('/admin/merchants/view/9678')
            // ->clickLink('Log')
            ->visit('/admin/merchants/activity-logs/9678')
            ->visit('/admin/merchants/view/9678')
            ->clickLink('PayOff Letter')
            ->clickLink('Payments')
            ->clickLink('ACH Schedule Of Payments')
            ->clickLink('Merchants')
            // ->clickLink('Create Merchants')
            ->visit('/admin/merchants/create')
            ->clickLink('Graph')
            ->visit('/admin/percentage_deal')
            // ->clickLink('Change to Advanced Status')
            ->visit('/admin/change_advanced_status')
            // ->clickLink('Generated Statement')
            ->visit('/admin/generated_pdf_merchants')
            // ->clickLink('FAQ')
            ->visit('/admin/merchants/faq');


        }
    
        public function reports()

    {
        $this->browser

        ->clickLink('Payments')
            ->clickLink('Payments')
            ->clickLink('Payments')
            ->clickLink('Reports')
            ->clickLink('Default Rate')
            ->visit('/admin/reports/defaultRateReport')
            ->press('download')
            ->clickLink('Default Rate (Merchants)')
            ->visit('/admin/reports/defaultRateMerchantReport')
            ->press('download')
            ->clickLink('Profitability(65/20/15)')
            ->visit('/admin/reports/profitability2')
            ->press('Download')
            ->clickLink('Profitability(50/30/20)')
            ->visit('/admin/reports/profitability3')
            ->press('Download')
            ->clickLink('Profitability(50/30/20) - 2021')
            ->visit('/admin/reports/profitability21')
            ->press('Download')
            ->clickLink('Profitability(50/50)')
            ->visit('/admin/reports/profitability4')
            ->press('Download')
            ->clickLink('Investment')
            ->visit('/admin/reports/investor')
            ->press('download')
            ->press('download')
            ->clickLink('Upsell Commission')
            ->visit('/admin/reports/upsell-commission')
            ->press('download')
            ->clickLink('Investor Assignment')
            ->visit('/admin/reports/investorAssignment')
            ->clickLink('Payments')
            ->visit('/admin/reports/payments')
            ->press('download')
            ->press('download')
            ->clickLink('Transactions')
            ->visit('/admin/investors/transaction-report')
            ->press('Download report')
            // ->clickLink('Accrued Pref Return')
            ->visit('/admin/reports/InvestorAccruedPrefReturn')
            ->clickLink('Equity Investor')
            ->visit('/admin/reports/equityInvestorReport')
            ->clickLink('Total Portfolio Earnings')
            ->visit('/admin/reports/totalPortfolioEarnings')
            ->clickLink('OverPayment Report')
            ->visit('/admin/reports/overpayment-report')
            ->clickLink('Velocity Profitability')
            ->visit('/admin/reports/velocity-profitability')
            ->press('Download');

        }

        public function logs()

    {
        $this->browser

        ->clickLink('Logs')
            ->clickLink('Liquidity Log')
            ->visit('/admin/reports/liquidityLog')
            ->clickLink('Merchant Liquidity Log')
            ->visit('/admin/reports/MerchantliquidityLog')
            ->clickLink('Merchant Status Log')
            ->visit('/admin/merchant_status_log')
            ->clickLink('User Activity Log')
            ->visit('/admin/activity-log')
            ->clickLink('Investor Transaction Log')
            ->visit('/admin/investor-transaction-log')
            ->clickLink('Settings');

        }



         public function Logout()
    {

        $this->browser->clicklink('Logout')

             ->visit('/login');
         }
}