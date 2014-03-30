<?php

namespace OroCRM\Bundle\SalesBundle\Tests\Selenium\Pages;

use Oro\Bundle\TestFrameworkBundle\Pages\AbstractPageFilteredGrid;

/**
 * Class Opportunities
 *
 * @package OroCRM\Bundle\SalesBundle\Tests\Selenium\Pages
 * @method Opportunities openOpportunities openOpportunities(string)
 * {@inheritdoc}
 */
class Opportunities extends AbstractPageFilteredGrid
{
    const URL = 'opportunity';

    public function __construct($testCase, $redirect = true)
    {
        $this->redirectUrl = self::URL;
        parent::__construct($testCase, $redirect);
    }

    /**
     * @return Opportunity
     */
    public function add()
    {
        $this->test->byXPath("//a[@title='Create Opportunity']")->click();
        $this->waitPageToLoad();
        $this->waitForAjax();
        $lead = new Opportunity($this->test);
        return $lead->init();
    }

    public function open($entityData = array())
    {
        $contact = $this->getEntity($entityData);
        $contact->click();
        $this->waitPageToLoad();
        $this->waitForAjax();

        return new Opportunity($this->test);
    }
}
