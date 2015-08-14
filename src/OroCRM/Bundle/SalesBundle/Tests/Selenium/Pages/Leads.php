<?php

namespace OroCRM\Bundle\SalesBundle\Tests\Selenium\Pages;

use Oro\Bundle\TestFrameworkBundle\Pages\AbstractPageFilteredGrid;

/**
 * Class Leads
 *
 * @package OroCRM\Bundle\SalesBundle\Tests\Selenium\Pages
 * @method Leads openLeads(string $bundlePath)
 * @method Lead add()
 * @method Lead open(array $filter)
 * {@inheritdoc}
 */
class Leads extends AbstractPageFilteredGrid
{
    const NEW_ENTITY_BUTTON = "//a[@title='Create Lead']";
    const URL = 'lead';

    public function entityNew()
    {
        $lead = new Lead($this->test);
        return $lead->init();
    }

    public function entityView()
    {
        return new Lead($this->test);
    }
}
