<?php

namespace OroCRM\Bundle\SalesBundle\Tests\Selenium\Pages;

use Oro\Bundle\TestFrameworkBundle\Pages\AbstractPageEntity;

/**
 * Class Workflow
 *
 * @package OroCRM\Bundle\SalesBundle\Tests\Selenium\Pages
 * @method Workflow openWorkflow openWorkflow(string $bundlePath)
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

    public function setContact($contact)
    {
        $this->test->byXpath("//div[starts-with(@id,'s2id_oro_workflow_step_contact')]/a")->click();
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

    public function setB2BCustomer($customer)
    {
        $this->test->byXpath("//div[starts-with(@id,'oro_workflow_transition_new_b2bcustomer')]//a")->click();
        $this->waitForAjax();
        $this->test->byXpath("//div[@id='select2-drop']/div/input")->value($customer);
        $this->waitForAjax();
        $this->assertElementPresent(
            "//div[@id='select2-drop']//div[contains(., '{$customer}')]",
            "Business customer autocomplete doesn't return search value"
        );
        $this->test->byXpath("//div[@id='select2-drop']//div[contains(., '{$customer}')]")->click();

        return $this;
    }

    public function setBudget($budget)
    {
        $this->budget = $this->test->byXpath("//*[@data-ftid='oro_workflow_transition_budget_amount']");
        $this->budget->clear();
        $this->budget->value($budget);

        return $this;
    }

    public function getBudget()
    {
        return $this->test->byXpath("//*[@data-ftid='oro_workflow_transition_budget_amount']")->value();
    }

    public function setProbability($probability)
    {
        $this->probability = $this->test->byXpath("//*[@data-ftid='oro_workflow_transition_probability']");
        $this->probability->clear();
        $this->probability->value($probability);

        return $this;
    }

    public function getProbability()
    {
        return $this->test->byXpath("//*[@data-ftid='oro_workflow_transition_probability']")->value();
    }

    public function setCustomerNeed($customerNeed)
    {
        $field = $this->test->byXpath("//*[@data-ftid='oro_workflow_transition_customer_need']");
        $field->clear();
        $field->value($customerNeed);

        return $this;
    }

    public function getCustomerNeed()
    {
        return $this->test->byXpath("//*[@data-ftid='oro_workflow_transition_customer_need']")->value();
    }

    public function setSolution($solution)
    {
        $this->solution = $this->test->byXpath("//*[@data-ftid='oro_workflow_transition_proposed_solution']");
        $this->solution->clear();
        $this->solution->value($solution);

        return $this;
    }

    public function getSolution()
    {
        return $$this->test->byXpath("//*[@data-ftid='oro_workflow_transition_proposed_solution']")->value();
    }

    public function setCloseRevenue($closeRevenue)
    {
        $field = $this->test->byXpath("//*[@data-ftid='oro_workflow_transition_close_revenue']");
        $field->clear();
        $field->value($closeRevenue);

        return $this;
    }

    public function setCloseReason($closeReason)
    {
        $field = $this->test
            ->select($this->test->byXpath("//*[@data-ftid='oro_workflow_transition_close_reason_name']"));
        $field->selectOptionByLabel($closeReason);

        return $this;
    }

    public function setCloseDate($closeDate)
    {
        $field = $this->test->byXpath("//*[@data-ftid='oro_workflow_transition_close_date']/..".
            "/following-sibling::input[contains(@class,'datepicker-input')]");
        $field->clear();
        $field->value($closeDate);

        return $this;
    }

    public function setCompanyName($company)
    {
        $field = $this->test->byXpath("//*[@data-ftid='oro_workflow_transition_new_company_name']");
        $field->clear();
        $field->value($company);

        return $this;
    }

    public function qualify()
    {
        $this->test->byId('transition-b2b_flow_sales_funnel-qualify')->click();
        sleep(1);
        $this->waitPageToLoad();
        $this->waitForAjax();
        $this->assertElementPresent(
            "//div[@class='ui-dialog-content ui-widget-content']/preceding-sibling::div/span[text()='Qualify']"
        );

        return $this;
    }

    public function disqualify()
    {
        $this->test->byId('transition-b2b_flow_sales_funnel-disqualify')->click();
        sleep(1);
        $this->waitPageToLoad();
        $this->waitForAjax();

        return $this;
    }

    public function reactivate()
    {
        $this->test->byId('transition-b2b_flow_sales_funnel-reactivate')->click();
        sleep(1);
        $this->waitPageToLoad();
        $this->waitForAjax();

        return $this;
    }

    public function reopen()
    {
        $this->test->byId('transition-b2b_flow_sales_funnel-reopen')->click();
        sleep(1);
        $this->waitForAjax();
        $this->test->byXpath("//div[div[contains(., 'Reopen')]]//a[text()='OK']")->click();
        $this->waitPageToLoad();
        $this->waitForAjax();

        return $this;
    }

    public function develop()
    {
        $this->test->byId('transition-b2b_flow_sales_funnel-develop')->click();
        sleep(1);
        $this->waitPageToLoad();
        $this->waitForAjax();
        $this->assertElementPresent(
            "//div[@class='ui-dialog-content ui-widget-content']/preceding-sibling::div/span[text()='Develop']"
        );

        return $this;
    }

    public function closeAsWon()
    {
        $this->test->byId('transition-b2b_flow_sales_funnel-close_as_won')->click();
        sleep(1);
        $this->waitPageToLoad();
        $this->waitForAjax();

        return $this;
    }

    public function closeAsLost()
    {
        $this->test->byId('transition-b2b_flow_sales_funnel-close_as_lost')->click();
        sleep(1);
        $this->waitPageToLoad();
        $this->waitForAjax();

        return $this;
    }

    public function checkStep($step)
    {
        $this->assertElementPresent("//ul[contains(@class, 'workflow-step-list')]//li[contains (.,'{$step}')]");

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
