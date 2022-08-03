<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class edit extends DuskTestCase
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
            $this->assign();
            $this->Edit_Merchant1();
            $this->Edit_Merchant2();
        });
    }

    public function Login()
    {
        $this->browser
                    ->visit('/login')
                    ->type('email', '1email@iocod.com')
                    ->type('password', 'admin987987')
                    ->press('Login')
                    ->visit('/admin/dashboard')
                    ->assertPathIs('/admin/dashboard');
    }

    public function assign()
    {
        $this->browser->visit('/admin/merchants/view/9812')
                      ->clickLink('Add Payment')
                      ->press('Select All Investors')
                      ->type('payment_date1', '04-02-2020')
                      ->pause('6000')
                      ->type('payment', '1000')
                      ->press('Create')
                      ->pause('6000')
                      ->visit('/admin/payment/create/9812')
                      ->pause('5000');
    }

    public function Edit_Merchant1()
    {
        $this->browser
                       ->visit('/admin/merchants/edit/9812')
                       ->pause('6000')
                       ->select('sub_status_id', '4')
                       ->press('Update')
                       ->pause('6000')
                       ->assertPathIs('/admin/merchants/edit/9812');
    }

    public function Edit_Merchant2()
    {
        $this->browser
                       ->visit('/admin/merchants/edit/9811')
                       ->pause('6000')
                       ->select('sub_status_id', '4')
                       ->press('Update')
                        ->pause('6000')
                       ->assertPathIs('/admin/merchants/edit/9811');
    }
}
