<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class livevelocity extends DuskTestCase
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
            $this->investor_velocity();
            $this->Logout();
        });
    }

    public function Login()
    {
        $this->browser
                    ->visit('/login')
                    ->type('email', 'akshaya@iocod.com')
                    ->type('password', 'gdwin@12345')
                    ->press('Login');
                    
    }

    public function investor_velocity()
    {
        $this->browser->visit('/admin/investors/portfolio/15')
                     
                    
                    ->visit('/admin/investors/portfolio/19')
                    
                    ->visit('/admin/investors/portfolio/22')
                    
                    ->visit('/admin/investors/portfolio/26')
                    
                    ->visit('/admin/investors/portfolio/32')
                    
                    ->visit('/admin/investors/portfolio/35')
                    
                    ->visit('/admin/investors/portfolio/36')

                    ->visit('/admin/investors/portfolio/43')
                    
                    ->visit('/admin/investors/portfolio/46')
                    
                    ->visit('/admin/investors/portfolio/47')
                    
                    ->visit('/admin/investors/portfolio/48')
                    
                    ->visit('/admin/investors/portfolio/49')
                    
                    ->visit('/admin/investors/portfolio/56')
                    
                    ->visit('/admin/investors/portfolio/67')
                    
                    ->visit('/admin/investors/portfolio/70')
                                                                            
                    ->visit('/admin/investors/portfolio/71')
                    
                    ->visit('/admin/investors/portfolio/140')
                    
                    ->visit('/admin/investors/portfolio/163')
                    
                    ->visit('/admin/investors/portfolio/219')
                    
                    ->visit('/admin/investors/portfolio/50')

                    ->visit('/admin/investors/portfolio/20')
                    
                    ->visit('/admin/investors/portfolio/25')
                    
                    ->visit('/admin/investors/portfolio/33')
                    
                    ->visit('/admin/investors/portfolio/41')
                    
                    ->visit('/admin/investors/portfolio/44')
                    
                    ->visit('/admin/investors/portfolio/68')

                    ->visit('/admin/investors/portfolio/77')
                    
                    ->visit('/admin/investors/portfolio/78')
                    
                    ->visit('/admin/investors/portfolio/122')
                    
                    ->visit('/admin/investors/portfolio/123')
                    ->pause('500')
                    ->press('Select Table And Copy To Clipboard');
    }
    public function Logout()
    {
        $this->browser->clicklink('Logout')

             ->visit('/login');
         }
}
