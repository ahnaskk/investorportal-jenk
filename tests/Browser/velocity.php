<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class velocity extends DuskTestCase
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
            ->type('email', '89email@iocod.com')
            ->type('password', '89email@iocod.com')
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
            ->visit('/admin/investors/transactions/26')
            ->visit('/admin/investors/portfolio/26')
            ->clickLink('Documents')
            ->visit('/admin/merchant_investor/documents_upload/26')
            ->visit('/admin/investors/portfolio/26')
            ->visit('/admin/merchants')
            ->press('download')
            ->visit('/admin/merchants/view/8446')
            ->visit('/admin/merchants/activity-logs/8446')
            ->visit('/admin/merchants/view/8446')
            ->clickLink('PayOff Letter')
            ->visit('/admin/merchants/faq');      

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
            ->clickLink('Accrued Pref Return')
            ->visit('/admin/reports/InvestorAccruedPrefReturn');

        }

            public function Logout()
    {

        $this->browser->clicklink('Logout')

             ->visit('/login');

         }
}
