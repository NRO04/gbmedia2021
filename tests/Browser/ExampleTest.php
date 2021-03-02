<?php

namespace Tests\Browser;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Laravel\Dusk\Chrome\ChromeProcess;
use Laravel\Dusk\ElementResolver;
use Tests\DuskTestCase;

class ExampleTest extends DuskTestCase
{
    /**
     * A basic browser test example.
     *
     * @return void
     */
    public function testBasicExample()
    {

        $this->browse(function ($browser) {
            $browser->visit('https://www.streamatemodels.com/smm/login.php')
                ->type('sausr', 'LFMM1@YOPMAIL.COM')
                ->type('sapwd', 'Molina89')
                ->press('#login-form-submit')
                ->screenshot('logged')
                ->visit('https://www.streamatemodels.com/smm/reports/earnings/EarningsReportPivot.php')
                ->text('//*[@id="report"]/div/div[1]/p[1]');
        });
    }
}
