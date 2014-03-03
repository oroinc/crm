<?php

namespace OroCRM\Bundle\SalesBundle\Tests\Selenium\Pages;

use Oro\Bundle\TestFrameworkBundle\Pages\AbstractPageEntity;

/**
 * Class SalesActivity
 *
 * @package OroCRM\Bundle\SalesBundle\Tests\Selenium\Pages
 */
class SalesActivity extends AbstractPageEntity
{
    /** @var  \PHPUnit_Extensions_Selenium2TestCase_Element */
    protected $activityname;
    /** @var  \PHPUnit_Extensions_Selenium2TestCase_Element */
    protected $startdate;
    /** @var  \PHPUnit_Extensions_Selenium2TestCase_Element */
    protected $owner;
    /** @var  \PHPUnit_Extensions_Selenium2TestCase_Element */
    protected $lead;
    /** @var  \PHPUnit_Extensions_Selenium2TestCase_Element */
    protected $opportunity;

    public function __construct($testCase, $redirect = true)
    {
        parent::__construct($testCase, $redirect);
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setActivityName($name)
    {
        $this->activityname = $this->test->byId('oro_workflow_transition_sales_funnel_name');
        $this->activityname->clear();
        $this->activityname->value($name);
        return $this;
    }

    public function setStartDate($date)
    {
        $this->startdate = $this->test->byId('oro_workflow_transition_sales_funnel_name');
        $this->startdate->clear();
        $this->startdate->value($date);
        return $this;
    }

    public function setOwner($owner)
    {
        $this->owner = $this->test->byXpath("//div[@id='s2id_oro_workflow_transition_sales_funnel_owner']/a");
        $this->owner->click();
        $this->waitForAjax();
        $this->test->byXpath("//div[@id='select2-drop']/div/input")->value($owner);
        $this->waitForAjax();
        $this->assertElementPresent(
            "//div[@id='select2-drop']//div[contains(., '{$owner}')]",
            "Owner autocomplete doesn't return search value"
        );
        $this->test->byXpath("//div[@id='select2-drop']//div[contains(., '{$owner}')]")->click();

        return $this;
    }

    public function setLead($lead)
    {
        $this->lead = $this->test->byXpath("//div[@id='s2id_oro_workflow_transition_lead']/a");
        $this->lead->click();
        $this->waitForAjax();
        $this->test->byXpath("//div[@id='select2-drop']/div/input")->value($lead);
        $this->waitForAjax();
        $this->assertElementPresent(
            "//div[@id='select2-drop']//div[contains(., '{$lead}')]",
            "Lead autocomplete doesn't return search value"
        );
        $this->test->byXpath("//div[@id='select2-drop']//div[contains(., '{$lead}')]")->click();

        return $this;
    }

    public function setOpportunity($opportunity)
    {
        $this->opportunity = $this->test->byXpath("//div[@id='s2id_oro_workflow_transition_opportunity']/a");
        $this->opportunity->click();
        $this->waitForAjax();
        $this->test->byXpath("//div[@id='select2-drop']/div/input")->value($opportunity);
        $this->waitForAjax();
        $this->assertElementPresent(
            "//div[@id='select2-drop']//div[contains(., '{$opportunity}')]",
            "Opportunity autocomplete doesn't return search value"
        );
        $this->test->byXpath("//div[@id='select2-drop']//div[contains(., '{$opportunity}')]")->click();

        return $this;
    }

    public function submit()
    {
        $this->test->byXpath("//button[@id='save-and-transit']")->click();
        $this->waitPageToLoad();
        $this->waitForAjax();
        return $this;
    }

    public function edit()
    {
        $this->test->byXpath(
            "//div[@class='pull-left btn-group icons-holder']/a[@title = 'Edit sales activity']"
        )->click();
        $this->waitPageToLoad();
        $this->waitForAjax();
        return $this;
    }

    public function delete()
    {
        $this->test->byXpath("//div[@class='pull-left btn-group icons-holder']/a[contains(., 'Delete')]")->click();
        $this->test->byXpath("//div[div[contains(., 'Delete Confirmation')]]//a[text()='Yes, Delete']")->click();
        $this->waitPageToLoad();
        $this->waitForAjax();
        return new SalesActivities($this->test, false);
    }
}
