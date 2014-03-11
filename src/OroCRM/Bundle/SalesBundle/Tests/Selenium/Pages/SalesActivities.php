<?php

namespace OroCRM\Bundle\SalesBundle\Tests\Selenium\Pages;

use Oro\Bundle\TestFrameworkBundle\Pages\AbstractPageFilteredGrid;

/**
 * Class SalesActivities
 *
 * @package OroCRM\Bundle\SalesBundle\Tests\Selenium\Pages
 * @method SalesActivities openSalesActivities openSalesActivities(string)
 * {@inheritdoc}
 */
class SalesActivities extends AbstractPageFilteredGrid
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

        return new SalesActivity($this->test);
    }

    public function startFromOpportunity()
    {
        $this->test->byXPath("//a[@title='Start from Opportunity']")->click();
        $this->waitPageToLoad();
        $this->waitForAjax();

        return new SalesActivity($this->test);
    }

    public function open($entityData = array())
    {
        $workflow = $this->getEntity($entityData);
        $workflow->click();
        $this->waitForAjax();
        $this->waitPageToLoad();
        $this->waitForAjax();

        return new Workflow($this->test);
    }
}
