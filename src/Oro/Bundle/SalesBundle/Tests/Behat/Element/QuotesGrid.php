<?php

namespace Oro\Bundle\SalesBundle\Tests\Behat\Element;

use Oro\Bundle\TestFrameworkBundle\Behat\Element\Table;
use Oro\Bundle\TestFrameworkBundle\Behat\Element\TableRow;

class QuotesGrid extends Table
{
    /**
     * @return TableRow[]
     */
    public function getItems()
    {
        return $this->getRows();
    }
}
