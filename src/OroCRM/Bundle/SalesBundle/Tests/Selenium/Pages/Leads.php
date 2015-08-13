<?php

namespace OroCRM\Bundle\SalesBundle\Tests\Selenium\Pages;

use Oro\Bundle\TestFrameworkBundle\Pages\AbstractPageFilteredGrid;

/**
 * Class Leads
 *
 * @package OroCRM\Bundle\SalesBundle\Tests\Selenium\Pages
 * @method Leads openLeads openLeads(string)
 * @method Lead add add()
 * @method Lead open open()
 * {@inheritdoc}
 */
class Leads extends AbstractPageFilteredGrid
{
    const NEW_ENTITY_BUTTON = "//a[@title='Create Lead']";
    const URL = 'lead';

    public function entityNew()
    {
        return new Lead($this->test);
    }

    public function entityView()
    {
        return new Lead($this->test);
    }
}
