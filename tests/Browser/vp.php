<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class vp extends DuskTestCase
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
            $this->reports();
            $this->Logout();
            
        });
    }

     public function Login()
    {
        $this->browser
            ->visit('/login')
            ->type('email', 'company@iocod.com')
            ->type('password', 'company@iocod')
            ->press('Login')
            ->visit('/admin/dashboard')
            ->assertPathIs('/admin/dashboard');
}

        public function accounts()

    {
        $this->browser
                   
            ->visit('/admin/investors')
            ->visit('/admin/investors/portfolio/59')
            ->clickLink('Transactions')
            ->visit('/admin/investors/transactions/59')
            ->visit('/admin/investors/portfolio/59')
            // ->clickLink('Documents')
            ->visit('/admin/merchant_investor/documents_upload/59')
            ->visit('/admin/investors/portfolio/59')
            ->clickLink('Merchants')
            ->clickLink('All Merchants')
            ->visit('/admin/merchants')
            ->press('download')
            // ->clickLink('View (42)')
            ->visit('/admin/merchants/view/9678')
         
            ->visit('/admin/merchants/activity-logs/9678')
            ->visit('/admin/merchants/view/9678')
            ->clickLink('PayOff Letter')
            ->visit('/admin/merchants/faq')

            ->visit('/admin/investors/transaction-report')
           // ->press('Download report')
           // ->clickLink('Delete   Selected')
           // ->clickLink('Add Transactions')
           ->visit('/admin/merchants/investor-transactions')
           // ->clickLink('Reset')
           ->visit('/admin/merchants/investor-transactions')
           ->press('View Investors');

        }

         public function reports()

    {
        $this->browser

            ->visit('/admin/merchants/view/9678')
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
            // ->clickLink('Accrued Pref Return')
            ->visit('/admin/reports/InvestorAccruedPrefReturn');



    }
         public function Logout()
    {

        $this->browser->clicklink('Logout')

             ->visit('/login');
         }
}
