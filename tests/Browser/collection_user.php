<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class collection_user extends DuskTestCase
{
    /**
     * A Dusk test testReport.
     *
     * @return void
     *
     */
    protected $browser;

    public function testExample()
    {
        $this->browse(function (Browser $browser) {
            $this->browser = $browser;
            $this->Login();
            $this->collection_user();
            $this->merchants();
            $this->reports();
            $this->Logout();
        });
    }

    public function Login()
    {
        $this->browser
                    ->visit('/login')
                    ->type('email', 'collection@iocod.com')
                    ->type('password', 'collection@iocod')
                    ->press('Login')
                    ->visit('/admin/dashboard')
                    ->assertPathIs('/admin/dashboard');
                    
                    
    }

    public function collection_user()
    {
        $this->browser
            ->clickLink('Lender')
            ->clickLink('All Lenders')
            ->visit('/admin/lender')
            ->clickLink('View')
            ->visit('/admin/lender/view/16')
            ->clickLink('View Lenders')
            ->visit('/admin/lender')
            ->clickLink('Merchants')
            ->clickLink('All Merchants')
            ->visit('/admin/merchants')
            // ->clickLink('View (42)')
            ->visit('/admin/merchants/view/9678')
            // ->clickLink('Notes (0)')
            ->visit('/admin/notes/9678/update')
            ->assertPathIs('/admin/notes/9678/update')
            ->visit('/admin/merchants/view/9678')
            
            ->visit('/admin/merchants/view/9678')
            // ->clickLink('Credit Card')
            ->visit('/admin/merchants/creditcard-payment/9678')
            ->visit('/admin/merchants/view/9678')
            // ->clickLink('Edit')
            ->visit('/admin/merchants/edit/9678')

            ->visit('/admin/merchants/view/9678')
            
            ->visit('/admin/merchants/activity-logs/9678')
            ->visit('/admin/merchants/view/9678')
            
            
            ->clickLink('PayOff Letter')
            ->visit('/admin/merchants/view/9678')
            // ->clickLink('Balance Report')
            ->visit('/admin/merchants/view/9678')
            // ->clickLink('Upload Docs')
            ->visit('/admin/merchants/9678/documents')
            ->visit('/admin/merchants/view/9678');
            
    }

    public function merchants()

{





    $this->browser->visit('/admin/merchants')
            ->clickLink('Change to Default')
            ->visit('/admin/change_merchant_status')
            ->clickLink('Change to Advanced Status')
            ->visit('/admin/change_advanced_status')
            ->clickLink('Generate Statement')
            ->visit('/admin/pdf_for_merchants')
            ->clickLink('Generated Statement')
            ->visit('/admin/generated_pdf_merchants')
            ->clickLink('Generated Statement')
            ->visit('/admin/generated_pdf_merchants')
            ->clickLink('FAQ')
            ->visit('/admin/merchants/faq')
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
            // ->press('download')
            ->clickLink('Default Rate (Merchants)')
            ->visit('/admin/reports/defaultRateMerchantReport')
            // ->press('download')
            ->clickLink('Settings');
            
}

    public function Logout()
    {

        $this->browser->clicklink('Logout')

             ->visit('/login');
         }
}
