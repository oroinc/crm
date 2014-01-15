<?php

namespace OroCRM\Bundle\TestsBundle\Tests\Selenium;

use Oro\Bundle\TestFrameworkBundle\Test\Selenium2TestCase;

class Test404 extends Selenium2TestCase
{
    public function test404()
    {
        $login = $this->login();
        $login->openAclCheck('Oro\Bundle\SecurityBundle')
            ->assertAcl('404', '404 - Not Found')
            ->assertElementPresent(
                "//div[@class='pagination-centered popup-box-errors'][contains(., '404 Not Found')]"
            );
    }
}
