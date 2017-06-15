<?php

namespace Oro\Bundle\SalesBundle\Tests\Behat\Element;

use Oro\Bundle\DataGridBundle\Tests\Behat\Element\GridMappedChildInterface;
use Oro\Bundle\TestFrameworkBundle\Behat\Element\Table;
use Oro\Bundle\TestFrameworkBundle\Behat\Element\TableRow;

class QuotesGrid extends Table implements GridMappedChildInterface
{
    /**
     * @return TableRow[]
     */
    public function getItems()
    {
        return $this->getRows();
    }

    /**
     * {@inheritdoc}
     */
    public function getMappedChildElementName($name)
    {
        return $name;
    }
}
