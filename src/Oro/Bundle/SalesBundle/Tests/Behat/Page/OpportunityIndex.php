<?php

namespace Oro\Bundle\SalesBundle\Tests\Behat\Page;

use Oro\Bundle\TestFrameworkBundle\Behat\Element\Page;

class OpportunityIndex extends Page
{
    #[\Override]
    public function open(array $parameters = [])
    {
        $this->getMainMenu()->openAndClick('Sales/Opportunities');
    }
}
