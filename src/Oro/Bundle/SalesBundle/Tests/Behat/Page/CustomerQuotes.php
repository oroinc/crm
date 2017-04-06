<?php

namespace Oro\Bundle\SalesBundle\Tests\Behat\Page;

use Oro\Bundle\TestFrameworkBundle\Behat\Element\Page;

class CustomerQuotes extends Page
{
    /**
     * Open page using parameters
     *
     * @param array $parameters
     */
    public function open(array $parameters = [])
    {
        $this->elementFactory->getPage()
            ->find('xpath', "//a[@href='/customer/quote/']")
            ->click();
    }
}
