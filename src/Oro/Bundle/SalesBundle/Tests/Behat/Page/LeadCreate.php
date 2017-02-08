<?php

namespace Oro\Bundle\SalesBundle\Tests\Behat\Page;

use Oro\Bundle\TestFrameworkBundle\Behat\Element\Page;

class LeadCreate extends Page
{
    /**
     * {@inheritdoc}
     */
    public function open(array $parameters = [])
    {
        $this->getMainMenu()->openAndClick('Sales/Leads');
        $this->elementFactory->getPage()->getSession()->getDriver()->waitForAjax();
        $this->elementFactory->getPage()->clickLink('Create Lead');
    }
}
