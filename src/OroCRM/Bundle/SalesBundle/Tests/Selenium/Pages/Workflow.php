<?php

namespace OroCRM\Bundle\SalesBundle\Tests\Selenium\Pages;

use Oro\Bundle\TestFrameworkBundle\Pages\AbstractPageEntity;

/**
 * Class Workflow
 *
 * @package OroCRM\Bundle\SalesBundle\Tests\Selenium\Pages
 * @method Workflow openWorkflow openWorkflow(string)
 * {@inheritdoc}
 */
class Workflow extends AbstractPageEntity
{
    /** @var  \PHPUnit_Extensions_Selenium2TestCase_Element */
    protected $contact;
    /** @var  \PHPUnit_Extensions_Selenium2TestCase_Element */
    protected $account;
    /** @var  \PHPUnit_Extensions_Selenium2TestCase_Element */
    protected $budget;
    /** @var  \PHPUnit_Extensions_Selenium2TestCase_Element */
    protected $probability;
    /** @var  \PHPUnit_Extensions_Selenium2TestCase_Element */
    protected $solution;

    public function __construct($testCase, $redirect = true)
    {
        parent::__construct($testCase, $redirect);
    }

    public function setContact($contact)
    {
        $this->test->byXpath("//div[@id='s2id_oro_workflow_step_contact']/a")->click();
        $this->waitForAjax();
        $this->test->byXpath("//div[@id='select2-drop']/div/input")->value($contact);
        $this->waitForAjax();
        $this->assertElementPresent(
            "//div[@id='select2-drop']//div[contains(., '{$contact}')]",
            "Contact autocomplete doesn't return search value"
        );
        $this->test->byXpath("//div[@id='select2-drop']//div[contains(., '{$contact}')]")->click();

        return $this;
    }

    public function setAccount($account)
    {
        $this->test->byXpath("//div[@id='s2id_oro_workflow_transition_new_account']/a")->click();
        $this->waitForAjax();
        $this->test->byXpath("//div[@id='select2-drop']/div/input")->value($account);
        $this->waitForAjax();
        $this->assertElementPresent(
            "//div[@id='select2-drop']//div[contains(., '{$account}')]",
            "Account autocomplete doesn't return search value"
        );
        $this->test->byXpath("//div[@id='select2-drop']//div[contains(., '{$account}')]")->click();

        return $this;
    }

    public function setBudget($budget)
    {
        $this->budget = $this->test->byId('oro_workflow_transition_budget_amount');
        $this->budget->clear();
        $this->budget->value($budget);
        return $this;
    }

    public function getBudget()
    {
        return $this->test->byId('oro_workflow_transition_budget_amount')->value();
    }

    public function setProbability($probability)
    {
        $this->probability = $this->test->byId('oro_workflow_transition_probability');
        $this->probability->clear();
        $this->probability->value($probability);
        return $this;
    }

    public function getProbability()
    {
        return $this->test->byId('oro_workflow_transition_probability')->value();
    }

    public function setCustomerNeed($customerNeed)
    {
        $field = $this->test->byId('oro_workflow_transition_customer_need');
        $field->clear();
        $field->value($customerNeed);
        return $this;
    }

    public function getCustomerNeed()
    {
        return $this->test->byId('oro_workflow_transition_customer_need')->value();
    }

    public function setSolution($solution)
    {
        $this->solution = $this->test->byId('oro_workflow_transition_proposed_solution');
        $this->solution->clear();
        $this->solution->value($solution);
        return $this;
    }

    public function getSolution()
    {
        return $$this->test->byId('oro_workflow_transition_proposed_solution')->value();
    }

    public function setCloseRevenue($closeRevenue)
    {
        $field = $this->test->byId('oro_workflow_transition_close_revenue');
        $field->clear();
        $field->value($closeRevenue);
        return $this;
    }

    public function setCloseReason($closeReason)
    {
        $field = $this->test->select($this->test->byId('oro_workflow_transition_close_reason_name'));
        $field->selectOptionByLabel($closeReason);
        return $this;
    }

    public function setCloseDate($closeDate)
    {
        $field = $this->test->byId($this->test->byId('date_selector_oro_workflow_transition_close_date'));
        $field->clear();
        $field->value($closeDate);
        return $this;
    }

    public function setCompanyName($company)
    {
        $field = $this->test->byId('oro_workflow_transition_company_name');
        $field->clear();
        $field->value($company);
        return $this;
    }

    public function getCompanyName()
    {
        return $this->test->byId('oro_workflow_transition_company_name')->value();
    }

    public function qualify()
    {
        $this->test->byXpath(
            "//div[@class='btn-group']/a[@id='transition-b2b_flow_sales_funnel-qualify']"
        )->click();
        $this->waitPageToLoad();
        $this->waitForAjax();
        $this->assertElementPresent(
            "//div[@class='ui-dialog-content ui-widget-content']/preceding-sibling::div/span[text()='Qualify']"
        );
        return $this;
    }

    public function disqualify()
    {
        $this->test->byXpath(
            "//div[@class='btn-group']/a[@id='transition-b2b_flow_sales_funnel-disqualify']"
        )->click();
        $this->waitPageToLoad();
        $this->waitForAjax();
        return $this;
    }

    public function reactivate()
    {
        $this->test->byXpath(
            "//div[@class='btn-group']/a[@id='transition-b2b_flow_sales_funnel-reactivate']"
        )->click();
        $this->waitPageToLoad();
        $this->waitForAjax();
        return $this;
    }

    public function reopen()
    {
        $this->test->byXpath(
            "//div[@class='btn-group']/a[@id='transition-b2b_flow_sales_funnel-reopen']"
        )->click();
        $this->waitForAjax();
        $this->test->byXpath("//div[div[contains(., 'Reopen')]]//a[text()='OK']")->click();
        $this->waitPageToLoad();
        $this->waitForAjax();
        return $this;
    }

    public function develop()
    {
        $this->test->byXpath(
            "//div[@class='btn-group']/a[@id='transition-b2b_flow_sales_funnel-develop']"
        )->click();
        $this->waitPageToLoad();
        $this->waitForAjax();
        $this->assertElementPresent(
            "//div[@class='ui-dialog-content ui-widget-content']/preceding-sibling::div/span[text()='Develop']"
        );
        return $this;
    }

    public function closeAsWon()
    {
        $this->test->byXpath(
            "//div[@class='btn-group']/a[@id='transition-b2b_flow_sales_funnel-close_as_won']"
        )->click();
        $this->waitPageToLoad();
        $this->waitForAjax();
        return $this;
    }

    public function closeAsLost()
    {
        $this->test->byXpath(
            "//div[@class='btn-group']/a[@id='transition-b2b_flow_sales_funnel-close_as_lost']"
        )->click();
        $this->waitPageToLoad();
        $this->waitForAjax();
        return $this;
    }

    public function checkStep($step)
    {
        $this->assertElementPresent("//div[@class='widget-content']//li[contains (.,'{$step}')]");
        return $this;
    }

    public function submit()
    {
        $this->test->byXpath("//button[normalize-space(text()) = 'Submit']")->click();
        $this->waitPageToLoad();
        $this->waitForAjax();
        //processing redirect
        $this->waitPageToLoad();
        $this->waitForAjax();
        return $this;
    }
}
