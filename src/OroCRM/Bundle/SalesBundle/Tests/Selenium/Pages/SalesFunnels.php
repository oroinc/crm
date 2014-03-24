<?php

namespace OroCRM\Bundle\SalesBundle\Tests\Selenium\Pages;

use Oro\Bundle\TestFrameworkBundle\Pages\AbstractPageFilteredGrid;

/**
 * Class SalesFunnels
 *
 * @package OroCRM\Bundle\SalesBundle\Tests\Selenium\Pages
 * @method SalesFunnels openSalesFunnels openSalesFunnels(string)
 * {@inheritdoc}
 */
class SalesFunnels extends AbstractPageFilteredGrid
{
    const URL = 'salesfunnel';

    public function __construct($testCase, $redirect = true)
    {
        $this->redirectUrl = self::URL;
        parent::__construct($testCase, $redirect);
    }

    public function startFromLead()
    {
        $this->test->byXPath("//a[@title='Start from Lead']")->click();
        $this->waitPageToLoad();
        $this->waitForAjax();

        return new SalesFunnel($this->test);
    }

    public function startFromOpportunity()
    {
        $this->test->byXPath("//a[@title='Start from Opportunity']")->click();
        $this->waitPageToLoad();
        $this->waitForAjax();
        return new SalesFunnel($this->test);
    }

    public function open($entityData = array())
    {
        $workflow = $this->getEntity($entityData);
        $workflow->click();
        sleep(1);
        $this->waitPageToLoad();
        $this->waitForAjax();

        return new Workflow($this->test);
    }
}
