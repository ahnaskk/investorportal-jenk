<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class accounts extends DuskTestCase
{
    /**
     * A Dusk test example.
     *
     * @return void
     */

    protected $browser;

    public function testExample()
    {
        $this->browse(function (Browser $browser) {
            $this->browser = $browser;
            $this->Login();
            $this->accounts();
            $this->merchant();
            $this->payments();
            $this->reports();
            $this->logs();
           $this->Logout();

        });
    }



    public function Login()
    {
        $this->browser
                    ->visit('/login')
                    ->type('email', 'accounts@iocod.com')
                    ->type('password', 'accounts@iocod')
                    ->press('Login')
                    ->visit('/admin/dashboard')
                    ->assertPathIs('/admin/dashboard');
}

    public function accounts()

    {
        $this->browser


        ->clickLink('Accounts')
           ->clickLink('All Accounts')
           ->visit('/admin/investors')
           ->press('download')
          
           ->visit('/admin/investors/portfolio/26')
           ->press('Delete')
           ->clickLink('Transfer To Velocity')
           ->visit('/admin/investors/achRequest/26')
           ->clickLink('Bank +')
           ->visit('/admin/investors/bank_details/26')
           ->visit('/admin/investors/achRequest/26')
           ->clickLink('Edit')
           ->visit('/admin/investors/edit/26')
           ->clickLink('Portfolio')
           ->visit('/admin/investors/portfolio/26')
           ->clickLink('Transfer To Bank')
           ->visit('/admin/investors/achRequest/Credit/26')
           ->visit('/admin/investors/portfolio/26')
           ->clickLink('Edit')
           ->visit('/admin/investors/edit/26')
           ->visit('/admin/investors/portfolio/26')
         
           ->visit('/admin/investors/transactions/26')
           ->press('download')
           ->visit('/admin/investors/portfolio/26')
          
          
          
           ->visit('/admin/investors/bank_details/26')
           ->visit('/admin/investors/portfolio/26')
           // ->clickLink('Generate PDF')
           ->visit('/admin/pdf_for_investors')
           ->visit('/admin/investors/portfolio/26')
           
           
           ->visit('/admin/investors/portfolio/26')
           // ->press('download')
           // ->clickLink('Accounts')
           // ->clickLink('Create Account')
           ->visit('/admin/investors/create')
           // ->clickLink('Generate PDF For Investors')
           ->visit('/admin/pdf_for_investors')
           // ->clickLink('Generated PDF/CSV')
           ->visit('/admin/generatedPdfCsv')
           // ->clickLink('FAQ')
           // ->visit('/admin/investors/faq')

           ->visit('/admin/investors/transaction-report')
           // ->press('Download report')
           // ->clickLink('Delete   Selected')
           // ->clickLink('Add Transactions')
           ->visit('/admin/merchants/investor-transactions')
           // ->clickLink('Reset')
           ->visit('/admin/merchants/investor-transactions')
           ->press('View Investors');


       }

            public function merchant()
    {
        $this->browser
            
            ->visit('/admin/sub_admins')
            
            ->visit('/admin/merchants')
            
            ->visit('/admin/merchants/view/9875')
            // ->clickLink('Roll Ins Payments')
            // ->press('')
            // ->clickLink('Credit Card')
            ->visit('/admin/merchants/creditcard-payment/9875')
            ->visit('/admin/merchants/view/9875')
            ->clickLink('ACH Terms')
            ->visit('/admin/merchants/9875/terms')
            // ->clickLink('View')
            ->visit('/admin/merchants/view/9875')
            // ->clickLink('Edit')
            ->visit('/admin/merchants/edit/9875')
            
            ->visit('/admin/merchants/view/9875')
            // ->clickLink('Add Payment')
            ->visit('/admin/payment/create/9875')
            // ->clickLink('View merchant')
            ->visit('/admin/merchants/view/9875')
            
            // ->clickLink('Log')
            ->visit('/admin/merchants/activity-logs/9875')
            ->visit('/admin/merchants/view/9875')
            ->clickLink('PayOff Letter')
            ->clickLink('Balance Report')
            ->clickLink('Upload Docs')
           
            ->visit('/admin/merchants/view/9875')
            
            ->clickLink('Payments')
            ->clickLink('ACH Schedule Of Payments')

           
            
            ->visit('/admin/merchants/create')
            ->clickLink('Graph')
            ->visit('/admin/percentage_deal')
            ->clickLink('Change to Default')
            ->visit('/admin/change_merchant_status')
            ->clickLink('Change to Advanced Status')
            ->visit('/admin/change_advanced_status')
            ->clickLink('Generate Statement')
            ->visit('/admin/pdf_for_merchants')
            // ->clickLink('Generated Statement')
            ->visit('/admin/generated_pdf_merchants')
            ->visit('/admin/merchants/faq');

}
             public function payments()
    {
        $this->browser
            ->visit('/admin/addEditMerchantsOffers')
            ->clickLink('View Offers')
            ->visit('/admin/merchantMarketOfferList')
            // ->clickLink('Edit')
            ->visit('/admin/addEditMerchantsOffers')
            ->clickLink('View Offers')
            ->visit('/admin/merchantMarketOfferList')
            ->clickLink('Merchant Offers List')
            ->visit('/admin/merchantMarketOfferList')
            ->clickLink('Create Investor Offers')
            ->visit('/admin/addEditInvestorsOffers')
            ->clickLink('Investors Offers List')
            ->visit('/admin/investorMarketOfferList')
            // ->clickLink('Edit')
            ->visit('/admin/addEditInvestorsOffers')
            ->clickLink('View Offers')
            ->visit('/admin/investorMarketOfferList')
            ->clickLink('Payments')
            ->clickLink('Generate Payment For Lenders')
            ->visit('/admin/payment/lender_payment_generation')
            ->clickLink('Pending Transactions')
            ->visit('/admin/payment/PendingTransactions');

    }

    public function reports()
{
$this->browser

           ->clickLink('Reports')
            ->clickLink('Default Rate')
            ->visit('/admin/reports/defaultRateReport')
            ->press('download')
            ->clickLink('Default Rate (Merchants)')
            ->visit('/admin/reports/defaultRateMerchantReport')
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
          ->press('download')
          ->clickLink('Upsell Commission')
          ->visit('/admin/reports/upsell-commission')
          ->press('download')
          ->clickLink('Investor Assignment')
          ->visit('/admin/reports/investorAssignment')
          // ->press('Download report')
          ->clickLink('Investor Reassignment')
          ->visit('/admin/reports/reassignReport')
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
          ->press('Download')
          ->clickLink('Agent Fee Report')
          ->visit('/admin/reports/agent-fee-report');

       }

       public function logs()
{
$this->browser
            ->clickLink('Logs')
            ->visit('/admin/reports/liquidityLog')
            ->clickLink('Merchant Liquidity Log')
            ->visit('/admin/reports/MerchantliquidityLog')
            ->clickLink('Merchant Status Log')
            ->visit('/admin/merchant_status_log')
            ->clickLink('User Activity Log')
            ->visit('/admin/activity-log')
            ->clickLink('Investor Transaction Log')
            ->visit('/admin/investor-transaction-log')
            ->clickLink('Settings')
            // ->clickLink('Advance Settings')
            ->visit('/admin/settings')
            // ->clickLink('System Settings')
            ->visit('/admin/settings/system_settings')
            ->visit('/admin/settings');



}

            public function Logout()
    {

        $this->browser

             // ->clicklink('Logout')
             ->visit('/login');
         }
}
