<?php

namespace Oro\Bundle\SalesBundle\Tests\Behat\Element;

use Oro\Bundle\DataGridBundle\Tests\Behat\Element\Grid;
use Oro\Bundle\DataGridBundle\Tests\Behat\Element\GridMappedChildInterface;
use Oro\Bundle\TestFrameworkBundle\Behat\Element\TableRow;

class QuotesGrid extends Grid implements GridMappedChildInterface
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
