<?php

namespace Oro\Bundle\SalesBundle\Tests\Behat\Page;

use Behat\Behat\Tester\Exception\PendingException;
use Oro\Bundle\TestFrameworkBundle\Behat\Element\Page;

class OpportunityView extends Page
{
    /**
     * {@inheritdoc}
     */
    public function open(array $parameters = [])
    {
        throw new PendingException('Open method is not implemented yet in Lead View page');
    }
}
