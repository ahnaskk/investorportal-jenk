<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class viewer extends DuskTestCase
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
            $this->Investor();
            $this->Merchants();
            $this->Transactions();
            $this->Velocity_distributions();
            $this->Marketplace();
            $this->Report();
            $this->Logs();
            $this->Bank_details();
            $this->Reconcile();
            $this->Settings();
            $this->Logout();
        });
    }

    public function Login()
    {
        $this->browser->visit('/login')
                    ->type('email', '184email@iocod.com')
                    ->type('password', '184email@iocod.com')
                    ->press('Login')
                    ->visit('/admin/dashboard')
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
            ->visit('/admin/investors/portfolio/26')
            ->visit('/admin/investors')
            ->clickLink('Edit')
            ->visit('/admin/investors/edit/26')
            ->clickLink('View Investors')
            ->visit('/admin/investors')
            ->clickLink('Transactions')
            ->visit('/admin/investors/transactions/26')
            ->visit('/admin/investors')
            ->visit('/admin/merchant_investor/documents_upload/26')
            ->clickLink('View')
            ->visit('/documents/26/doc_1529960279.pdf')
            ->visit('/admin/investors')
            ->clickLink('Bank')
            ->visit('/admin/investors/bank/26')
            ->clickLink('Back to list')
            ->visit('/admin/investors')
            ->clickLink('Create Investors')
            ->visit('/admin/investors/create')
            ->clickLink('View Investors')
            ->visit('/admin/investors')
            ->visit('/admin/investors/create')
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
             ->visit('/admin/merchants/view/8465')
            ->visit('/admin/merchants')
            ->clickLink('Marketing Offers')
            ->visit('/admin/market_offer')
            ->clickLink('Create Offers')
            ->visit('/admin/addEditOffers')
            ->clickLink('View Offers')
            ->visit('/admin/market_offer');
    }

    public function Transactions()
    {
        $this->browser
            ->clickLink('Transactions')
            ->clickLink('All Transactions')
            ->visit('/admin/bills');
    }

    public function Velocity_distributions()
    {
        $this->browser
          ->clickLink('Velocity Distributions')
            ->clickLink('All Distributions')
            ->visit('/admin/vdistribution');
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
            ->visit('/admin/reports/defaultRateReport')
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
            ->clickLink('Revenue Recognition')
            ->visit('/admin/merchants/export2')
            ->clickLink('Transactions')
            ->visit('/admin/investors/transaction-report')
            ->clickLink('Accrued Pre Return')
            ->visit('/admin/reports/investorInterestAccured')
            ->clickLink('Debt Investor')
            ->visit('/admin/reports/investorProfitReport')
            ->clickLink('Equity Investor')
            ->visit('/admin/reports/equityInvestorReport')
            ->clickLink('Total Portfolio Earnings')
            ->pause('6000')
            ->visit('/admin/reports/totalPortfolioEarnings')
            ->clickLink('OverPayment Report')
            ->visit('/admin/reports/overpayment-report');
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
            ->clickLink('Activity Log')
            ->visit('/admin/activity_log');
    }

    public function Bank_details()
    {
        $this->browser

            ->clickLink('View Accounts')
            ->visit('/admin/viewbank');
    }

    public function Reconcile()
    {
        $this->browser
             ->clickLink('Reconcile')
            ->clickLink('List')
            ->visit('/admin/reports/reconcile');
    }

    public function Settings()
    {
        $this->browser

             ->clickLink('Settings')

            ->clickLink('All sub status')
            ->visit('/admin/sub_status');
    }

    public function Logout()
    {
        $this->browser
            ->clickLink('Logout')
            ->visit('/login');
    }
}
