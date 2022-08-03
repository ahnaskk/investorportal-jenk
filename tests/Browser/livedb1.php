<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class livedb1 extends DuskTestCase
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
            $this->CompanyVP();
            $this->CompanyVelocity();
            $this->Syndicate();
            $this->label();
            $this->Overpayment();
            $this->Fees();
            $this->Investor1();
            $this->Investor2();
            $this->Investor3();
            $this->Investor4();
            $this->Investor5();
            $this->Investor6();
            $this->Investor7();
            $this->Investor8();
            $this->Investor9();
            $this->Investor10();
            $this->Investor11();
            $this->Investor12();
            $this->lender();
        });
    }

    public function Login()
    {
        $this->browser
                    ->visit('/login')
                    ->type('email', 'admin@investor.portal')
                    ->type('password', 'admin987987')
                    ->press('Login')
                    ->visit('/admin/dashboard')
                    ->assertPathIs('/admin/dashboard');
    }

    public function CompanyVP()
    {
        $this->browser
                    ->visit('/admin/dashboard')
                    ->clickLink('Companies')
                    ->clickLink('All Companies')
                    ->visit('/admin/sub_admins')
                    ->clickLink('Create Companies')
                    ->visit('/admin/sub_admins/create')
                    ->type('name', 'VP Advance Funding')
                    ->pause('6000')
                    ->type('email', 'pactolus@vgusa.com')
                    ->attach('logo', '/Users/priyatp/Desktop/logo-2.png')
                    ->pause('1000')
                    ->type('brokerage', '15')
                    ->type('password', '123123')
                    ->type('password_confirmation', '123123')
                    ->press('Create')
                    ->assertPathIs('/admin/sub_admins/create')
                    ->clickLink('View Compaines')
                    ->visit('/admin/sub_admins');
    }

    public function CompanyVelocity()
    {
        $this->browser

                   ->visit('/admin/dashboard')
                    ->clickLink('Companies')
                    ->clickLink('All Companies')
                    ->visit('/admin/sub_admins')
                    ->clickLink('Create Companies')
                    ->visit('/admin/sub_admins/create')
                    ->type('name', 'Velocity')
                    ->type('email', 'abcd@gmail.com')
                    ->attach('logo', '/Users/priyatp/Desktop/logo-2.png')
                     ->pause('1000')
                    ->type('brokerage', '3')
                    ->type('password', '123123')
                    ->type('password_confirmation', '123123')
                    ->press('Create')
                    ->assertPathIs('/admin/sub_admins/create')
                    ->clickLink('View Compaines')
                    ->visit('/admin/sub_admins');
    }

    public function Syndicate()
    {
        $this->browser

                   ->visit('/admin/dashboard')
                    ->clickLink('Companies')
                    ->clickLink('All Companies')
                    ->visit('/admin/sub_admins')
                    ->clickLink('Create Companies')
                    ->visit('/admin/sub_admins/create')
                    ->type('name', 'Syndicate')
                    ->type('email', 'syndicate@gmail.com')
                    ->attach('logo', '/Users/priyatp/Desktop/logo-2.png')
                     ->pause('1000')
                    ->type('brokerage', '100')
                    ->type('password', '123123')
                    ->type('password_confirmation', '123123')
                    ->press('Create')
                    ->assertPathIs('/admin/sub_admins/create')
                    ->clickLink('View Compaines')
                    ->visit('/admin/sub_admins');
    }

    public function label()
    {
        $this->browser

           ->visit('/admin/label')
           ->clickLink('Add Label')
           ->visit('/admin/label/create')
           ->type('name', 'MCA (Default)')
           ->press('Create')
           ->assertPathIs('/admin/label/edit/1')
           ->press('×')

   ->pause('6000')

           ->visit('/admin/label')
           ->clickLink('Add Label')
           ->visit('/admin/label/create')
           ->type('name', 'Luthersales')
           ->press('Create')
           ->assertPathIs('/admin/label/edit/2')
           ->press('×')

  ->pause('6000')

            ->visit('/admin/label')
           ->clickLink('Add Label')
           ->visit('/admin/label/create')
           ->type('name', 'Insurance')
           ->check('flag')
           ->pause('2000')
           ->press('Create')
           ->assertPathIs('/admin/label/edit/3')
           ->press('×')

  ->pause('6000')

            ->visit('/admin/label')
           ->clickLink('Add Label')
           ->visit('/admin/label/create')
           ->type('name', 'Insurance 1')
            ->check('flag')
           ->pause('2000')
           ->press('Create')
           ->assertPathIs('/admin/label/edit/4')
           ->press('×')

  ->pause('6000')

            ->visit('/admin/label')
           ->clickLink('Add Label')
           ->visit('/admin/label/create')
           ->type('name', 'Insurance 2')
            ->check('flag')
           ->pause('2000')
           ->press('Create')
           ->assertPathIs('/admin/label/edit/5')
           ->press('×');
    }

    public function Overpayment()
    {
        $this->browser

               ->visit('/admin/investors/create')
                ->type('name', 'Overpayment')
                ->select('investor_type', '6')
                ->select('role_id', '13')
                // ->select('management_fee', '0')
                // ->select('global_syndication', '0')
                ->type('cell_phone', '(195) 150-2329')
                ->type('email', 'overpayment@yahoo.in')
                ->type('password', '123123')
                ->type('password_confirmation', '123123')
                ->pause('6000')
                 // ->type('notification_email', 'investor1@yahoo.in')
                 ->pause('6000')
                 ->select('notification_recurence', '4')
                 ->check('email_notification')
                 ->select('company', '5')
                  ->press('Create')
                  ->assertPathIs('/admin/investors/create')
                  ->pause('1000');
    }

    public function Fees()
    {
        $this->browser

               ->visit('/admin/investors/create')
                ->type('name', 'Fee')
                ->select('investor_type', '6')
                ->select('role_id', '14')
                // ->select('management_fee', '0')
                // ->select('global_syndication', '0')
                 ->type('cell_phone', '(123) 456-1234')
                ->type('email', 'fees@yahoo.in')
                ->type('password', '123123')
                ->type('password_confirmation', '123123')
                ->pause('6000')
                 // ->type('notification_email', 'investor1@yahoo.in')
                 ->pause('6000')
                 ->select('notification_recurence', '4')
                 ->check('email_notification')
                 ->select('company', '5')
                  ->press('Create')
                  ->assertPathIs('/admin/investors/create')
                  ->pause('1000');
    }

    public function Investor1()
    {
        $this->browser

                ->visit('/admin/investors/create')
                ->type('name', 'InvestorVP-1')
                ->select('investor_type', '1')
                 ->select('role_id', '2')
                // ->select('management_fee', '0')
                // ->select('global_syndication', '0')
                ->select('interest_rate', '10')
                ->type('email', 'investor1@yahoo.in')
                 ->select('role_id', '2')
                ->type('password', '123123')
                ->type('password_confirmation', '123123')
                 ->type('cell_phone', '(195) 375-3895')
                ->pause('6000')
                 // ->type('notification_email', 'investor1@yahoo.in')
                 ->pause('6000')
                 ->select('notification_recurence', '1')

                 ->pause('6000')
                 ->select('label[]', '3')
                ->select('label[]', '4')
                ->select('label[]', '5')
                 ->pause('6000')
                 ->check('email_notification')
                 ->select('company', '3')
                  ->press('Create')
                  ->assertPathIs('/admin/investors/create')
                  ->pause('1000')
                  ->visit('/admin/investors/transactions/8')
                 ->clickLink('Create Transactions')
                 ->visit('/admin/investors/transactions/8/create')
                  ->type('amount', '100000')
                  ->select('transaction_category', '1')
                 ->select('transaction_method', '1')
                 ->press('Create')
                  ->assertPathIs('/admin/investors/transactions/8/create');
    }

    public function Investor2()
    {
        $this->browser

                ->visit('/admin/investors/create')
                ->type('name', 'InvestorVP-2')
                ->select('investor_type', '3')
                 ->select('role_id', '2')
                // ->select('management_fee', '0')
                // ->select('global_syndication', '0')
                ->select('interest_rate', '15')
                ->type('email', 'investor24@yahoo.in')
                ->type('password', '123123')
                ->type('password_confirmation', '123123')
                ->pause('6000')
                 // ->type('notification_email', 'investor1@yahoo.in')
                 ->pause('6000')
                  ->type('cell_phone', '(195) 375-3895')
                 ->select('notification_recurence', '3')

                  ->select('label[]', '3')
                ->select('label[]', '4')
                ->select('label[]', '5')

                 ->check('email_notification')
                 ->select('company', '3')
                  ->press('Create')
                  ->assertPathIs('/admin/investors/create')
                  ->pause('1000')
                  ->visit('/admin/investors/transactions/9')
                 ->clickLink('Create Transactions')
                 ->visit('/admin/investors/transactions/9/create')
                  ->type('amount', '100000')
                  ->select('transaction_category', '1')
                   ->select('transaction_method', '1')
                 ->press('Create')
                  ->assertPathIs('/admin/investors/transactions/9/create');
    }

    public function Investor3()
    {
        $this->browser

                ->visit('/admin/investors/create')
                ->type('name', 'InvestorVel-1')
                ->select('investor_type', '2')
                 ->select('role_id', '2')
                // ->select('management_fee', '0')
                // ->select('global_syndication', '0')
                ->type('email', 'investorvel1@gmail.com')
                ->type('password', '123123')
                ->type('password_confirmation', '123123')
                ->pause('6000')
                 // ->type('notification_email', 'investor1@yahoo.in')
                 ->pause('6000')
                  ->type('cell_phone', '(195) 375-3895')
                 ->select('notification_recurence', '2')
                 ->select('label[]', '3')
                ->select('label[]', '4')
                ->select('label[]', '5')
                 ->check('email_notification')
                 ->select('company', '4')
                  ->press('Create')
                  ->assertPathIs('/admin/investors/create')
                   ->pause('1000')
                  ->visit('/admin/investors/transactions/10')
                 ->clickLink('Create Transactions')
                 ->visit('/admin/investors/transactions/10/create')
                  ->type('amount', '100000')
                  ->select('transaction_category', '1')
                    ->select('transaction_method', '1')
                 ->press('Create')
                  ->assertPathIs('/admin/investors/transactions/10/create');
    }

    public function Investor4()
    {
        $this->browser

                ->visit('/admin/investors/create')
                ->type('name', 'InvestorVel-2')
                ->select('investor_type', '4')
                 ->select('role_id', '2')
                // ->select('management_fee', '0')
                // ->select('global_syndication', '0')
                ->select('interest_rate', '13')
                ->type('email', 'investor49@yahoo.in')
                ->type('password', '123123')
                ->type('password_confirmation', '123123')
                ->pause('6000')
                 // ->type('notification_email', 'investor1@yahoo.in')
                 ->pause('6000')
                  ->type('cell_phone', '(195) 375-3895')
                 ->select('notification_recurence', '4')
                  ->select('label[]', '3')
                ->select('label[]', '4')
                ->select('label[]', '5')

                 ->check('email_notification')
                 ->select('company', '4')
                  ->press('Create')
                  ->assertPathIs('/admin/investors/create')
                  ->pause('1000')
                  ->visit('/admin/investors/transactions/11')
                 ->clickLink('Create Transactions')
                 ->visit('/admin/investors/transactions/11/create')
                  ->type('amount', '100000')
                  ->select('transaction_category', '1')
                    ->select('transaction_method', '1')
                 ->press('Create')
                  ->assertPathIs('/admin/investors/transactions/11/create');
    }

    public function Investor5()
    {
        $this->browser

                ->visit('/admin/investors/create')
                ->type('name', 'InvestorVel-3')
                ->select('investor_type', '1')
                 ->select('role_id', '2')
                // ->select('management_fee', '0')
                // ->select('global_syndication', '0')
                 ->select('interest_rate', '11')
                ->type('email', 'investor5@yahoo.in')
                ->type('password', '123123')
                ->type('password_confirmation', '123123')
                ->pause('6000')
                 // ->type('notification_email', 'investor1@yahoo.in')
                 ->pause('6000')
                  ->type('cell_phone', '(195) 375-3895')
                 ->select('notification_recurence', '1')

                ->select('label[]', '3')
                ->select('label[]', '4')
                ->select('label[]', '5')

                 ->check('email_notification')
                 ->select('company', '4')
                  ->press('Create')
                  ->assertPathIs('/admin/investors/create')
                  ->pause('1000')
                  ->visit('/admin/investors/transactions/12')
                 ->clickLink('Create Transactions')
                 ->visit('/admin/investors/transactions/12/create')
                  ->type('amount', '50000')
                  ->select('transaction_category', '1')
                    ->select('transaction_method', '1')
                 ->press('Create')
                  ->assertPathIs('/admin/investors/transactions/12/create');
    }

    public function Investor6()
    {
        $this->browser

                ->visit('/admin/investors/create')
                ->type('name', 'InvestorVel-4')
                ->select('investor_type', '3')
                 ->select('role_id', '2')
                ->select('management_fee', '0')
                 ->select('interest_rate', '15')
                ->select('global_syndication', '0')
                ->type('email', 'investor6@yahoo.in')
                ->type('password', '123123')
                ->type('password_confirmation', '123123')
                ->pause('6000')
                 // ->type('notification_email', 'investor1@yahoo.in')
                 ->pause('6000')
                  ->type('cell_phone', '(195) 375-3895')
                 ->select('notification_recurence', '2')
                ->select('label[]', '3')
                ->select('label[]', '4')
                ->select('label[]', '5')
                 ->check('email_notification')
                 ->select('company', '4')
                  ->press('Create')
                  ->assertPathIs('/admin/investors/create')
                  ->pause('1000')
                  ->visit('/admin/investors/transactions/13')
                 ->clickLink('Create Transactions')
                 ->visit('/admin/investors/transactions/13/create')
                  ->type('amount', '25698')
                  ->select('transaction_category', '1')
                    ->select('transaction_method', '1')
                 ->press('Create')
                  ->assertPathIs('/admin/investors/transactions/13/create');
    }

    public function Investor7()
    {
        $this->browser

                ->visit('/admin/investors/create')
                ->type('name', 'InvestorVP-3')
                ->select('investor_type', '2')
                 ->select('role_id', '2')
                // ->select('management_fee', '0')
                // ->select('global_syndication', '0')
                ->type('email', 'investor7@yahoo.in')
                ->type('password', '123123')
                ->type('password_confirmation', '123123')
                ->pause('6000')
                 // ->type('notification_email', 'investor1@yahoo.in')
                 ->pause('6000')
                  ->type('cell_phone', '(195) 375-3895')
                 ->select('notification_recurence', '3')
               ->select('label[]', '3')
                ->select('label[]', '4')
                ->select('label[]', '5')
                 ->check('email_notification')
                 ->select('company', '3')
                  ->press('Create')
                  ->assertPathIs('/admin/investors/create')
                  ->pause('1000')
                  ->visit('/admin/investors/transactions/14')
                 ->clickLink('Create Transactions')
                 ->visit('/admin/investors/transactions/14/create')
                  ->type('amount', '75000')
                  ->select('transaction_category', '1')
                    ->select('transaction_method', '1')
                 ->press('Create')
                  ->assertPathIs('/admin/investors/transactions/14/create');
    }

    public function Investor8()
    {
        $this->browser

                ->visit('/admin/investors/create')
                ->type('name', 'InvestorVP-4')
                ->select('investor_type', '3')
                 ->select('role_id', '2')
                // ->select('management_fee', '0')
                // ->select('global_syndication', '0')
                   ->select('interest_rate', '15')
                ->type('email', 'investor8@yahoo.in')
                ->type('password', '123123')
                ->type('password_confirmation', '123123')
                ->pause('6000')
                 // ->type('notification_email', 'investor1@yahoo.in')
                 ->pause('6000')
                  ->type('cell_phone', '(195) 375-3895')
                 ->select('notification_recurence', '4')

                 ->select('label[]', '3')
                ->select('label[]', '4')
                ->select('label[]', '5')

                 ->check('email_notification')
                 ->select('company', '3')
                  ->press('Create')
                  ->assertPathIs('/admin/investors/create')
                  ->pause('1000')
                  ->visit('/admin/investors/transactions/15')
                 ->clickLink('Create Transactions')
                 ->visit('/admin/investors/transactions/15/create')
                  ->type('amount', '5000')
                  ->select('transaction_category', '1')
                    ->select('transaction_method', '1')
                 ->press('Create')
                  ->assertPathIs('/admin/investors/transactions/15/create');
    }

    public function Investor9()
    {
        $this->browser

                ->visit('/admin/investors/create')
                ->type('name', 'Syndicate1')
                ->select('investor_type', '5')
                 ->select('role_id', '2')
                ->select('management_fee', '3')
                ->select('global_syndication', '2')
                ->type('email', 'investor9@yahoo.in')
                ->type('password', '123123')
                ->type('password_confirmation', '123123')
                ->pause('6000')
                 // ->type('notification_email', 'investor1@yahoo.in')
                 ->pause('6000')
                  ->type('cell_phone', '(195) 375-3895')
                 ->select('notification_recurence', '1')

                ->select('label[]', '3')
                ->select('label[]', '4')
                ->select('label[]', '5')

                 ->check('email_notification')
                 ->select('company', '5')
                  ->press('Create')
                  ->assertPathIs('/admin/investors/create')
                  ->pause('1000')
                  ->visit('/admin/investors/transactions/16')
                 ->clickLink('Create Transactions')
                 ->visit('/admin/investors/transactions/16/create')
                  ->type('amount', '40000')
                  ->select('transaction_category', '1')
                    ->select('transaction_method', '1')
                 ->press('Create')
                  ->assertPathIs('/admin/investors/transactions/16/create');
    }

    public function Investor10()
    {
        $this->browser

                ->visit('/admin/investors/create')
                ->type('name', 'Syndicate2')
                ->select('investor_type', '5')
                 ->select('role_id', '2')
                ->select('management_fee', '2')
                // ->select('global_syndication', '0')
                ->select('role_id', '2')
                ->type('email', 'investor10@yahoo.in')
                ->type('password', '123123')
                ->type('password_confirmation', '123123')
                ->pause('6000')
                 // ->type('notification_email', 'investor1@yahoo.in')
                 ->pause('6000')
                  ->type('cell_phone', '(195) 375-3895')
                 ->select('notification_recurence', '3')
                 ->check('email_notification')
                 ->select('company', '5')
                  ->press('Create')
                  ->assertPathIs('/admin/investors/create')
                  ->pause('1000')
                  ->visit('/admin/investors/transactions/17')
                 ->clickLink('Create Transactions')
                 ->visit('/admin/investors/transactions/17/create')
                  ->type('amount', '40000')
                  ->select('transaction_category', '1')
                    ->select('transaction_method', '1')
                 ->press('Create')
                  ->assertPathIs('/admin/investors/transactions/17/create');
    }

    public function Investor11()
    {
        $this->browser

                ->visit('/admin/investors/create')
                ->type('name', 'Syndicate3')
                ->select('investor_type', '5')
                 ->select('role_id', '2')
                ->select('management_fee', '2')
                ->select('global_syndication', '2')
                ->select('role_id', '2')
                ->type('email', 'investor11@yahoo.in')
                ->type('password', '123123')
                ->type('password_confirmation', '123123')
                ->pause('6000')
                 // ->type('notification_email', 'investor1@yahoo.in')
                 ->pause('6000')
                  ->type('cell_phone', '(195) 375-3895')
                 ->select('notification_recurence', '1')
                 ->select('label[]', '3')
                ->select('label[]', '5')
                 ->check('email_notification')
                 ->select('company', '5')
                  ->press('Create')
                  ->assertPathIs('/admin/investors/create')
                  ->pause('1000')
                  ->visit('/admin/investors/transactions/18')
                 ->clickLink('Create Transactions')
                 ->visit('/admin/investors/transactions/18/create')
                  ->type('amount', '100000')
                  ->select('transaction_category', '1')
                    ->select('transaction_method', '1')
                 ->press('Create')
                  ->assertPathIs('/admin/investors/transactions/18/create');
    }

    public function Investor12()
    {
        $this->browser

                ->visit('/admin/investors/create')
                ->type('name', 'Syndicate4')
                ->select('investor_type', '5')
                 ->select('role_id', '2')
                ->select('management_fee', '3')
                ->select('global_syndication', '2.5')
                ->select('role_id', '2')
                ->type('email', 'investor12@yahoo.in')
                ->type('password', '123123')
                ->type('password_confirmation', '123123')
                ->pause('6000')
                 // ->type('notification_email', 'investor1@yahoo.in')
                 ->pause('6000')
                  ->type('cell_phone', '(195) 375-3895')
                 ->select('notification_recurence', '4')
                 ->check('email_notification')
                 ->select('company', '5')
                  ->press('Create')
                  ->assertPathIs('/admin/investors/create')
                  ->pause('1000')
                  ->visit('/admin/investors/transactions/19')
                 ->clickLink('Create Transactions')
                 ->visit('/admin/investors/transactions/19/create')
                  ->type('amount', '40000')
                  ->select('transaction_category', '1')
                    ->select('transaction_method', '1')
                 ->press('Create')
                  ->assertPathIs('/admin/investors/transactions/19/create');
    }

    public function lender()
    {
        $this->browser

     ->visit('/admin/lender/create')
           ->type('name', 'LenderA')
           ->type('email', 'shabeer@gmail.com')
           ->type('password', '123123')
           ->type('password_confirmation', '123123')
           ->select('management_fee', '3')
           ->select('global_syndication', '0')
           ->select('underwriting_fee', '0')
           ->type('lag_time', '10')
           ->press('Create')
           ->assertPathIs('/admin/lender/create')

            ->visit('/admin/lender/create')
           ->type('name', 'LenderB')
           ->type('email', 'priya@gmail.com')
           ->type('password', '123123')
           ->type('password_confirmation', '123123')
           ->select('management_fee', '3')
           ->select('global_syndication', '2')
           ->radio('s_prepaid_status', '2')
           ->pause('1000')
           ->select('underwriting_fee', '0')
           ->type('lag_time', '3')
           ->press('Create')
           ->assertPathIs('/admin/lender/create')

            ->visit('/admin/lender/create')
           ->type('name', 'LenderC')
           ->type('email', 'reshma@gmail.com')
           ->type('password', '123123')
           ->type('password_confirmation', '123123')
           ->select('management_fee', '0')
           ->select('global_syndication', '2')
           ->radio('s_prepaid_status', '2')
            ->pause('1000')
           ->select('underwriting_fee', '0')
           ->type('lag_time', '5')
           ->press('Create')
           ->assertPathIs('/admin/lender/create')

             ->visit('/admin/lender/create')
           ->type('name', 'LenderD')
           ->type('email', 'amit@gmail.com')
           ->type('password', '123123')
           ->type('password_confirmation', '123123')
           ->select('management_fee', '2')
           ->select('global_syndication', '1')
           ->radio('s_prepaid_status', '2')
            ->pause('1000')
           ->select('underwriting_fee', '0')
           ->type('lag_time', '14')
           ->press('Create')
           ->assertPathIs('/admin/lender/create')

           ->visit('/admin/lender/create')
           ->type('name', 'LenderE')
           ->type('email', 'lendere@gmail.com')
           ->type('password', '123123')
           ->type('password_confirmation', '123123')
           ->select('management_fee', '3')
           ->select('global_syndication', '2.5')
           ->radio('s_prepaid_status', '2')
            ->pause('1000')
            ->type('lag_time', '8')
           ->select('underwriting_fee', '2')
           ->check('underwriting_status[]', '1')
            ->check('underwriting_status[]', '2')
            ->check('underwriting_status[]', '3')
             ->pause('1000')
           ->press('Create')
           ->assertPathIs('/admin/lender/create');
    }
}
