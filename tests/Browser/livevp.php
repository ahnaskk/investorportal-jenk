<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class livevp extends DuskTestCase
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
            $this->investor_vp();
            $this->logout();
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

    public function investor_vp()
    {
        $this->browser->visit('/admin/investors/portfolio/177')
                    
                    ->visit('/admin/investors/portfolio/59')
                    
                    ->visit('/admin/investors/portfolio/60')
                    
                    ->visit('/admin/investors/portfolio/61')
                    
                    ->visit('/admin/investors/portfolio/62')

                    ->visit('/admin/investors/portfolio/63')
                    
                    ->visit('/admin/investors/portfolio/64')
                    
                    ->visit('/admin/investors/portfolio/65')
                    
                    ->visit('/admin/investors/portfolio/66')
                    
                    ->visit('/admin/investors/portfolio/81')
                    
                    ->visit('/admin/investors/portfolio/82')
                    
                    ->visit('/admin/investors/portfolio/83')
                    
                    ->visit('/admin/investors/portfolio/84')
                    
                    ->visit('/admin/investors/portfolio/85')
                    
                    ->visit('/admin/investors/portfolio/86')
                    
                    ->visit('/admin/investors/portfolio/87')
                    
                    ->visit('/admin/investors/portfolio/88')
                    
                    ->visit('/admin/investors/portfolio/121')

                    ->visit('/admin/investors/portfolio/144')
                    
                    ->visit('/admin/investors/portfolio/145')
                    
                    ->visit('/admin/investors/portfolio/146')
                    
                    ->visit('/admin/investors/portfolio/147')
                    
                    ->visit('/admin/investors/portfolio/148')
                    
                    ->visit('/admin/investors/portfolio/149')

                    ->visit('/admin/investors/portfolio/150')
                    
                    ->visit('/admin/investors/portfolio/151')
                    
                    ->visit('/admin/investors/portfolio/157')
                    
                    ->visit('/admin/investors/portfolio/158')
                    
                    ->visit('/admin/investors/portfolio/167')
                    
                    ->visit('/admin/investors/portfolio/178')
                    
                    ->visit('/admin/investors/portfolio/179')
                    
                    ->visit('/admin/investors/portfolio/180')
                                                                            
                    ->visit('/admin/investors/portfolio/189')
                    
                    ->visit('/admin/investors/portfolio/190')
                    
                    ->visit('/admin/investors/portfolio/191')
                    
                    ->visit('/admin/investors/portfolio/192')
                    
                    ->visit('/admin/investors/portfolio/197')

                    ->visit('/admin/investors/portfolio/198')
                    
                    ->visit('/admin/investors/portfolio/199')
                    
                    ->visit('/admin/investors/portfolio/200')
                    
                    ->visit('/admin/investors/portfolio/201')
                    
                    ->visit('/admin/investors/portfolio/202')

                    ->visit('/admin/investors/portfolio/602')
                    
                    ->pause('500')
                   
                    ->press('Select Table And Copy To Clipboard');
                    
                     }
                     public function Logout()
    {
        $this->browser->clicklink('Logout')

             ->visit('/login');
                    
    }
}
