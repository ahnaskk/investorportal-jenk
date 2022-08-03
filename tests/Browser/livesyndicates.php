<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class livesyndicates extends DuskTestCase
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
            $this->investor_syndicates();
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

    public function investor_syndicates()
    {
        $this->browser->visit('/admin/investors/portfolio/92')
                    
                    ->visit('/admin/investors/portfolio/126')
                    
                    ->visit('/admin/investors/portfolio/127')
                    
                    ->visit('/admin/investors/portfolio/128')
                    
                    ->visit('/admin/investors/portfolio/153')
                    
                    ->visit('/admin/investors/portfolio/154')
                    
                    ->visit('/admin/investors/portfolio/155')
                    
                    ->visit('/admin/investors/portfolio/156')
                    
                    ->visit('/admin/investors/portfolio/159')

                    ->visit('/admin/investors/portfolio/160')
                    
                    ->visit('/admin/investors/portfolio/161')
                    
                    ->visit('/admin/investors/portfolio/162')
                    
                    ->visit('/admin/investors/portfolio/168')
                    
                    ->visit('/admin/investors/portfolio/170')
                    
                    ->visit('/admin/investors/portfolio/171')

                    ->visit('/admin/investors/portfolio/172')
                    
                    ->visit('/admin/investors/portfolio/173')
                    
                    ->visit('/admin/investors/portfolio/174')
                    
                    ->visit('/admin/investors/portfolio/186')
                    
                    ->visit('/admin/investors/portfolio/187')
                    
                    ->visit('/admin/investors/portfolio/188')
                    
                    ->visit('/admin/investors/portfolio/195')
                    
                    ->visit('/admin/investors/portfolio/204')
                                                                            
                    ->visit('/admin/investors/portfolio/205')
                    
                    ->visit('/admin/investors/portfolio/206')
                    
                    ->visit('/admin/investors/portfolio/207')
                    
                    ->visit('/admin/investors/portfolio/208')
                    
                    ->visit('/admin/investors/portfolio/209')

                    ->visit('/admin/investors/portfolio/211')
                    
                    ->visit('/admin/investors/portfolio/212')
                    
                    ->visit('/admin/investors/portfolio/214')
                    
                    ->visit('/admin/investors/portfolio/217')
                    
                    ->visit('/admin/investors/portfolio/220')
                    
                    ->visit('/admin/investors/portfolio/223')

                    ->visit('/admin/investors/portfolio/228')
                    
                    ->visit('/admin/investors/portfolio/230')
                    
                    ->visit('/admin/investors/portfolio/231')
                    
                    ->visit('/admin/investors/portfolio/232')
                    
                    ->visit('/admin/investors/portfolio/233')
                    
                    ->visit('/admin/investors/portfolio/234')
                    
                    ->visit('/admin/investors/portfolio/235')
                    
                    ->visit('/admin/investors/portfolio/236')
                    
                    ->visit('/admin/investors/portfolio/237')
                    
                    ->visit('/admin/investors/portfolio/238')
                    
                    ->visit('/admin/investors/portfolio/244')
                    
                    ->visit('/admin/investors/portfolio/245')
                    
                    ->visit('/admin/investors/portfolio/248')

                    ->visit('/admin/investors/portfolio/249')
                    
                    ->visit('/admin/investors/portfolio/251')
                    
                    ->visit('/admin/investors/portfolio/262')
                    
                    ->visit('/admin/investors/portfolio/264')
                    
                    ->visit('/admin/investors/portfolio/273')
                    
                    ->visit('/admin/investors/portfolio/286')

                    ->visit('/admin/investors/portfolio/287')
                    
                    ->visit('/admin/investors/portfolio/290')
                    
                    ->visit('/admin/investors/portfolio/428')
                    
                    ->visit('/admin/investors/portfolio/429')
                    
                    ->visit('/admin/investors/portfolio/434')
                    
                    ->visit('/admin/investors/portfolio/437')
                    
                    ->visit('/admin/investors/portfolio/438')
                    
                    ->visit('/admin/investors/portfolio/439')
                                                                            
                    ->visit('/admin/investors/portfolio/444')
                    
                    ->visit('/admin/investors/portfolio/445')
                    
                    ->visit('/admin/investors/portfolio/446')
                    
                    ->visit('/admin/investors/portfolio/447')
                    
                    ->visit('/admin/investors/portfolio/450')

                    ->visit('/admin/investors/portfolio/499')
                    
                    ->visit('/admin/investors/portfolio/502')
                    
                    ->visit('/admin/investors/portfolio/503')
                    
                    ->visit('/admin/investors/portfolio/506')
                    
                    ->visit('/admin/investors/portfolio/515')
                    
                    ->visit('/admin/investors/portfolio/517')

                    ->visit('/admin/investors/portfolio/518')
                    
                    ->visit('/admin/investors/portfolio/519')
                    
                    ->visit('/admin/investors/portfolio/524')
                    
                    ->visit('/admin/investors/portfolio/528')
                    
                    ->visit('/admin/investors/portfolio/543')
                    
                    ->visit('/admin/investors/portfolio/547')
                    
                    ->visit('/admin/investors/portfolio/555')
                    
                    ->visit('/admin/investors/portfolio/559')
                    
                    ->visit('/admin/investors/portfolio/582')
                    
                    ->visit('/admin/investors/portfolio/583')
                    
                    ->visit('/admin/investors/portfolio/584')
                    
                    ->visit('/admin/investors/portfolio/585')

                    ->visit('/admin/investors/portfolio/603')
                    
                    ->visit('/admin/investors/portfolio/604')
                    
                    ->visit('/admin/investors/portfolio/605')
                    
                    ->visit('/admin/investors/portfolio/606')
                    
                    ->visit('/admin/investors/portfolio/607')
                    
                    ->visit('/admin/investors/portfolio/608')

                    ->visit('/admin/investors/portfolio/609')
                    
                    ->visit('/admin/investors/portfolio/610')
                    
                    ->visit('/admin/investors/portfolio/611')
                    
                    ->visit('/admin/investors/portfolio/612')
                    
                    ->visit('/admin/investors/portfolio/613')
                    
                    ->visit('/admin/investors/portfolio/614')
                    
                    ->visit('/admin/investors/portfolio/615')
                    
                    ->visit('/admin/investors/portfolio/617')
                                                                            
                    ->visit('/admin/investors/portfolio/618')
                    
                    ->visit('/admin/investors/portfolio/619')
                    
                    ->visit('/admin/investors/portfolio/75')
                    
                    ->visit('/admin/investors/portfolio/91')
                    
                    ->visit('/admin/investors/portfolio/124')

                    ->visit('/admin/investors/portfolio/125')

                    ->visit('/admin/investors/portfolio/631')
                    
                    ->visit('/admin/investors/portfolio/634')
                    
                    ->visit('/admin/investors/portfolio/639')
                    
                    ->visit('/admin/investors/portfolio/640')
                    
                    ->visit('/admin/investors/portfolio/644')

                    ->visit('/admin/investors/portfolio/647')

                    ->visit('/admin/investors/portfolio/648')

                    ->visit('/admin/investors/portfolio/673')

                    ->visit('/admin/investors/portfolio/675')

                    ->visit('/admin/investors/portfolio/676')

                    ->visit('/admin/investors/portfolio/677')

                    ->visit('/admin/investors/portfolio/680')

                    ->visit('/admin/investors/portfolio/687')

                    ->visit('/admin/investors/portfolio/689')

                    ->visit('/admin/investors/portfolio/696')

                    ->visit('/admin/investors/portfolio/697')

                    ->visit('/admin/investors/portfolio/698')

                    ->visit('/admin/investors/portfolio/701')

                    ->visit('/admin/investors/portfolio/702')

                    ->visit('/admin/investors/portfolio/703')

                    ->visit('/admin/investors/portfolio/704')

                    ->visit('/admin/investors/portfolio/705')

                    ->visit('/admin/investors/portfolio/706')

                    ->visit('/admin/investors/portfolio/707')

                    ->visit('/admin/investors/portfolio/708')

                    ->visit('/admin/investors/portfolio/709')

                    ->visit('/admin/investors/portfolio/713')

                    ->visit('/admin/investors/portfolio/715')

                    ->visit('/admin/investors/portfolio/718')

                    ->visit('/admin/investors/portfolio/721')

                    ->visit('/admin/investors/portfolio/722')

                    ->visit('/admin/investors/portfolio/738')

                    ->visit('/admin/investors/portfolio/740')

                    ->visit('/admin/investors/portfolio/742')

                    ->visit('/admin/investors/portfolio/743')

                    ->visit('/admin/investors/portfolio/744')

                    ->visit('/admin/investors/portfolio/745')

                    ->visit('/admin/investors/portfolio/746')

                    ->visit('/admin/investors/portfolio/748')

                    ->visit('/admin/investors/portfolio/749')

                    ->visit('/admin/investors/portfolio/753')

                    ->visit('/admin/investors/portfolio/757')

                    ->visit('/admin/investors/portfolio/758')
               
                    ->visit('/admin/investors/portfolio/785')

                    ->visit('/admin/investors/portfolio/786')

                    ->pause('500')
                   
                    ->press('Select Table And Copy To Clipboard');
    }
    public function Logout()
    {
        $this->browser->clicklink('Logout')

             ->visit('/login');
         }
}
