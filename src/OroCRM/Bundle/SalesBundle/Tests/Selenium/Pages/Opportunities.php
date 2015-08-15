<?php

namespace OroCRM\Bundle\SalesBundle\Tests\Selenium\Pages;

use Oro\Bundle\TestFrameworkBundle\Pages\AbstractPageFilteredGrid;

/**
 * Class Opportunities
 *
 * @package OroCRM\Bundle\SalesBundle\Tests\Selenium\Pages
 * @method Opportunities openOpportunities(string $bundlePath)
 * @method Opportunity add()
 * @method Opportunity open(array $filter)
 * {@inheritdoc}
 */
class Opportunities extends AbstractPageFilteredGrid
{
    const NEW_ENTITY_BUTTON = "//a[@title='Create Opportunity']";
    const URL = 'opportunity';

    public function entityNew()
    {
        $opportunity = new Opportunity($this->test);
        return $opportunity->init();
    }

    public function entityView()
    {
        return new Opportunity($this->test);
    }
}
