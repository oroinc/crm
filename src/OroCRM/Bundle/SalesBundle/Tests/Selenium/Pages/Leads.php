<?php

namespace OroCRM\Bundle\SalesBundle\Tests\Selenium\Pages;

use Oro\Bundle\TestFrameworkBundle\Pages\AbstractPageFilteredGrid;

/**
 * Class Leads
 *
 * @package OroCRM\Bundle\SalesBundle\Tests\Selenium\Pages
 * @method Leads openLeads openLeads(string)
 * {@inheritdoc}
 */
class Leads extends AbstractPageFilteredGrid
{
    const URL = 'lead';

    public function __construct($testCase, $redirect = true)
    {
        $this->redirectUrl = self::URL;
        parent::__construct($testCase, $redirect);
    }

    /**
     * @return Lead
     */
    public function add()
    {
        $this->test->byXPath("//a[@title='Create Lead']")->click();
        $this->waitPageToLoad();
        $this->waitForAjax();
        $lead = new Lead($this->test);
        return $lead->init();
    }

    public function open($entityData = array())
    {
        $contact = $this->getEntity($entityData);
        $contact->click();
        $this->waitPageToLoad();
        $this->waitForAjax();

        return new Lead($this->test);
    }
}
