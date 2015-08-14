<?php

namespace OroCRM\Bundle\SalesBundle\Tests\Selenium\Pages;

use Oro\Bundle\TestFrameworkBundle\Pages\AbstractPageFilteredGrid;

/**
 * Class SalesFunnels
 *
 * @package OroCRM\Bundle\SalesBundle\Tests\Selenium\Pages
 * @method SalesFunnels openSalesFunnels(string $bundlePath)
 * @method Workflow open(array $filter)
 * {@inheritdoc}
 */
class SalesFunnels extends AbstractPageFilteredGrid
{
    const URL = 'salesfunnel';

    protected $gridPath = "//div[contains(@class,'grid-container')]";

    public function entityNew()
    {
        return $this;
    }

    public function entityView()
    {
        return new Workflow($this->test);
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
}
