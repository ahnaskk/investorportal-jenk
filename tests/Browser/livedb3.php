<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class livedb3 extends DuskTestCase
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
            $this->payment();
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

    public function payment()
    {
        $this->browser
                       ->visit('/admin/merchants/edit/2')
                       ->pause('6000')
                       ->select('sub_status_id', '18')
                       ->press('Update')
                       ->pause('6000')
                       ->assertPathIs('/admin/merchants/edit/2');
        $this->browser
                       ->visit('/admin/merchants/edit/9')
                       ->pause('6000')
                       ->select('sub_status_id', '19')
                       ->press('Update')
                       ->pause('6000')
                       ->assertPathIs('/admin/merchants/edit/9');

          $this->browser
                       ->visit('/admin/merchants/edit/16')
                       ->pause('6000')
                       ->select('sub_status_id', '20')
                       ->press('Update')
                       ->pause('6000')
                       ->assertPathIs('/admin/merchants/edit/16');


        $this->browser->visit('/admin/dashboard')
                    ->assertPathIs('/admin/dashboard')
                     ->pause('12000')
                      ->pause('12000')
                      ->pause('6000');


            $this->browser
                       ->visit('/admin/merchants/edit/9')
                       ->pause('6000')
                       ->select('sub_status_id', '1')
                       ->press('Update')
                       ->pause('6000')
                       ->assertPathIs('/admin/merchants/edit/9'); 


          $this->browser->visit('/admin/dashboard')
                    ->assertPathIs('/admin/dashboard')
                     ->pause('12000')
                      ->pause('12000')
                      ->pause('6000');
                       


      $this->browser->visit('/admin/merchants/view/9')
                   ->clickLink('Add Payment')
                    ->visit('admin/payment/create/9')
                   ->press('Select All Investors')
                   ->pause('6000')
                  ->type('payment_date1', '04-05-2021')
                   ->pause('6000')
                   ->press('Create')
                   ->pause('6000')
                   ->visit('/admin/payment/create/9');


 $this->browser->visit('/admin/dashboard')
                    ->assertPathIs('/admin/dashboard')
                     ->pause('12000')
                      ->pause('12000')
                      ->pause('6000');



       $this->browser->visit('/admin/merchants/view/14')
                        ->clickLink('Roll Ins Payments')
                       ->type('date_start1','01-01-2020')
                      ->pause('6000')
                      type('date_end1','01-12-2021')
                      ->pause('6000')
                      ->press('Assign')
                      ->pause('6000');


$this->browser->visit('/admin/merchants/view/14')
               ->assertPathIs('/admin/merchants/view/14')
                       ->pause('12000')
                      ->pause('12000');

$this->browser->visit('/admin/reports/investor')
              ->assertPathIs('/admin/reports/investor')
                       ->pause('12000')
                      ->pause('12000');


$this->browser->visit('/admin/dashboard')
                    ->assertPathIs('/admin/dashboard')
                     ->pause('12000')
                      ->pause('12000')
                      ->pause('6000');







 $this->browser->visit('/admin/merchants/view/14')
                   ->clickLink('Add Payment')
                    ->visit('admin/payment/create/14')
                   ->press('Select All Investors')
                   ->pause('6000')
                   ->type('payment_date1', '05-05-2021','05-06-2021','05-08-2021')
                   ->pause('6000')
                   ->press('Create')
                   ->pause('6000')
                   ->visit('/admin/payment/create/14');



$this->browser->visit('/admin/dashboard')
                    ->assertPathIs('/admin/dashboard')
                     ->pause('12000')
                      ->pause('12000')
                      ->pause('6000');



 $this->browser->visit('/admin/merchants/view/14')
                   ->clickLink('Add Payment')
                    ->visit('admin/payment/create/13')
                   ->press('Select All Investors')
                   ->pause('6000')
                   ->type('payment_date1', '05-05-2021','05-06-2021','05-08-2021')
                   ->pause('6000')
                   ->press('Create')
                   ->pause('6000')
                   ->visit('/admin/payment/create/14');


$this->browser->visit('/admin/dashboard')
                    ->assertPathIs('/admin/dashboard')
                     ->pause('12000')
                      ->pause('12000')
                      ->pause('6000');


$this->browser->visit('admin/investors/portfolio/6')
                    ->assertPathIs('/admin/investors/portfolio/6')
                     ->pause('12000')
                      ->pause('12000')
                      ->pause('6000');


        $this->browser->visit('/admin/merchants/view/4')
                   ->clickLink('Add Payment')
                   ->press('Select All Investors')
                   ->pause('6000')
                   ->type('payment_date', '2020-06-09')
                   ->pause('6000')
                   ->type('payment', '1000')
                    ->check('net_payment')
                   ->press('Create')
                   ->pause('6000')
                   ->visit('/admin/payment/create/4');

        $this->browser->visit('/admin/merchants/view/4')
                   ->clickLink('Add Payment')
                   ->press('Select All Investors')
                   ->pause('6000')
                   ->type('payment_date', '2020-06-15')
                   ->pause('6000')
                   ->type('payment', '500')
                    ->check('net_payment')
                   ->press('Create')
                   ->pause('6000')
                   ->visit('/admin/payment/create/4');

        $this->browser->visit('/admin/merchants/view/4')
                   ->clickLink('Add Payment')
                   ->press('Select All Investors')
                   ->pause('6000')
                   ->type('payment_date', '2020-06-20')
                   ->pause('6000')
                   ->type('payment', '2,560.80')
                    ->check('net_payment')
                   ->press('Create')
                   ->pause('6000')
                   ->visit('/admin/payment/create/4');
    }
}
