<?php

namespace Tests\Browser\Components;

use Laravel\Dusk\Browser;
use Laravel\Dusk\Component as BaseComponent;

class DatePicker extends BaseComponent
{
    /**
     * Get the root selector for the component.
     *
     * @return string
     */
    public function selector()
    {
        return '.valid_dates';
    }

    /**
     * Assert that the browser page contains the component.
     *
     * @param Browser $browser
     *
     * @return void
     */
    public function assert(Browser $browser)
    {
        $browser->assertVisible($this->selector());
    }

    /**
     * Get the element shortcuts for the component.
     *
     * @return array
     */
    public function elements()
    {
        return [
            '@date-field' => 'input.valid_dates-input',
            '@month-list' => 'div > div.valid_dates-months',

        ];
    }

    /**
     * Select the given date.
     *
     * @param \Laravel\Dusk\Browser $browser
     * @param int                   $month
     * @param int                   $year
     *
     * @return void
     */
    public function selectDate($browser, $month)
    {
        $browser->click('@date-field')
                ->within('@month-list', function ($browser) {
                    $browser->click(5);
                });
    }
}
