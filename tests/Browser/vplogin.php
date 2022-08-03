<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class vplogin extends DuskTestCase
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
            $this->login();
            $this->Investor();
            $this->Merchants();
            $this->Reports();
            $this->Wires();
            $this->logout();
        });
    }

    public function login()
    {
        $this->browser
             ->visit('/login')
            ->type('email', 'pactolus@vgusa.com')
            ->type('password', '123123')
            ->press('Login')
            ->assertPathIs('/admin/dashboard');
    }

    public function Investor()
    {
        $this->browser

           ->clickLink('Investors')
           ->clickLink('All Investors')
           ->visit('/admin/investors')
           ->clickLink('Create Investor')
           ->visit('/admin/investors/create')
           ->clickLink('View Investors')
           ->visit('/admin/investors')
           ->clickLink('Portfolio')
           ->visit('/admin/investors/portfolio/59')
           ->clickLink('Edit')
           ->visit('/admin/investors/edit/59')
           ->press('Update')
           ->assertPathIs('/admin/investors/edit/59')
           ->clickLink('View Investors')
           ->visit('/admin/investors')
           ->clickLink('Portfolio')
           ->visit('/admin/investors/portfolio/59')
           ->clickLink('Edit')
           ->visit('/admin/investors/edit/59')
           ->clickLink('View Investors')
           ->visit('/admin/investors')
           ->visit('/admin/investors/portfolio/59')
           ->clickLink('Transactions')
           ->visit('/admin/investors/transactions/59')
           ->clickLink('Create Transactions')
           ->visit('/admin/investors/transactions/59/create')
           ->clickLink('View Lists')
           ->visit('/admin/investors/transactions/59')
           ->visit('/admin/investors/portfolio/59')
           ->clickLink('Documents')
           ->visit('/admin/merchant_investor/documents_upload/59')
           ->clickLink('View')
           ->visit('/admin/merchant_investor/documents_upload/59')
           ->visit('/admin/investors/portfolio/59')
           ->clickLink('Bank')
           ->visit('/admin/investors/bank/59')
           ->clickLink('Back to list')
           ->visit('/admin/investors')
           ->visit('/admin/investors/portfolio/59')
           ->visit('/admin/investors')
           ->clickLink('Edit')
           ->visit('/admin/investors/edit/59')
           ->clickLink('View Investors')
           ->visit('/admin/investors')
           ->clickLink('Transactions')
           ->visit('/admin/investors/transactions/59')
           ->visit('/admin/investors')
           ->clickLink('Documents')
           ->visit('/admin/merchant_investor/documents_upload/59')
           ->visit('/admin/investors')
           ->clickLink('Create Investors')
           ->visit('/admin/investors/create')
           ->clickLink('View Investors')
           ->visit('/admin/investors')
           ->clickLink('Generated PDF/CSV')
           ->visit('/admin/generatedPdfCsv');
    }

    public function Merchants()
    {
        $this->browser

                ->clickLink('Merchants')
                ->clickLink('All Merchants')
                ->visit('/admin/merchants')
                ->press('download')
                ->clickLink('View (42)')
                ->visit('/admin/merchants/view/9006')
                ->visit('/admin/merchants')
                ->clickLink('Notes (1)')
                ->clickLink('Create Merchants')
                ->visit('/admin/merchants/create')
                ->clickLink('Graph')
                ->visit('/admin/percentage_deal')
                ->press('download')
                ->clickLink('Update Graph')
                ->clickLink('Clear Filter')
                ->visit('/admin/percentage_deal');
    }

    public function Reports()
    {
        $this->browser
           ->clickLink('Reports')
           ->clickLink('Default Rate')
           ->visit('/admin/reports/defaultRateReport')
           ->clickLink('Default Rate (Merchants)')
           ->visit('/admin/reports/defaultRateMerchantReport')
           ->clickLink('Delinquent')
           ->visit('/admin/reports/delinquent')
           ->clickLink('Payment Left Report')
           ->visit('/admin/reports/paymentLeftReport')
           ->clickLink('Lender Delinquent')
           ->visit('/admin/reports/lenderReport')
           ->clickLink('Profitability(65/20/15)')
           ->visit('/admin/reports/profitability2')
           ->clickLink('Profitability(50/30/20)')
           ->visit('/admin/reports/profitability3')
           ->clickLink('Profitability(50/50)')
           ->visit('/admin/reports/profitability4')
           ->clickLink('Investment')
           ->visit('/admin/reports/investor')
           ->clickLink('Investor Assignment')
           ->visit('/admin/reports/investorAssignment')
           ->clickLink('Investor Reassignment')
           ->visit('/admin/reports/reassignReport')
           ->clickLink('Liquidity')
           ->visit('/admin/reports/liquidityReport')
           ->clickLink('Payments')
           ->visit('/admin/reports/payments')
           ->clickLink('Transactions')
           ->visit('/admin/investors/transaction-report')
           ->clickLink('Accrued Pre Return')
           ->visit('/admin/reports/investorInterestAccured')
           ->clickLink('Debt Investor')
           ->visit('/admin/reports/investorProfitReport')
           ->clickLink('Total Portfolio Earnings')
           ->visit('/admin/reports/totalPortfolioEarnings');
    }

    public function Wires()
    {
        $this->browser

           ->clickLink('Wires/ach')
           ->clickLink('Wires/Ach List')
           ->visit('/admin/wires')
           ->clickLink('Edit')
           ->visit('/admin/wires/edit/452')
           ->press('Update')
           ->assertPathIs('/admin/wires/edit/452')
           ->clickLink('Back to lists')
           ->visit('/admin/wires')
           ->clickLink('Documents')
           ->visit('/admin/merchant_investor/wires_documents_upload/452')
           ->visit('/admin/wires');
    }

    public function logout()
    {
        $this->browser
        ->clickLink('Logout')
           ->visit('/login');
    }
}
