<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class Streamate extends DuskTestCase
{
    /**
     * A Dusk test example.
     *
     * @return void
     */
    public function testExample()
    {
        $this->browse(function (Browser $browser) {
            $password = "Ojeda1993";
            $user = "GDOP@YOPMAIL.COM";
            $url = "https://www.streamatemodels.com/smm/login.php";

            $browser->visit($url)->type('sausr', $user)->type('sapwd', $password)->press('login-form-submit');

            $browser->screenshot('logged.png');
        });
    }
}
