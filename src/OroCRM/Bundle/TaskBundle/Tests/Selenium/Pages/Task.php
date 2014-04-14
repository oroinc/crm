<?php

namespace OroCRM\Bundle\TaskBundle\Tests\Selenium\Pages;

use Oro\Bundle\TestFrameworkBundle\Pages\AbstractPageEntity;
use Oro\Bundle\TestFrameworkBundle\Pages\Workflow;

/**
 * Class Task
 *
 * @package OroCRM\Bundle\SalesBundle\Tests\Selenium\Pages
 */
class Task extends AbstractPageEntity
{
    /** @var  \PHPUnit_Extensions_Selenium2TestCase_Element */
    protected $subject;
    /** @var  \PHPUnit_Extensions_Selenium2TestCase_Element */
    protected $description;
    /** @var  \PHPUnit_Extensions_Selenium2TestCase_Element */
    protected $dueDate;
    /** @var  \PHPUnit_Extensions_Selenium2TestCase_Element */
    protected $prioty;
    /** @var  \PHPUnit_Extensions_Selenium2TestCase_Element */
    protected $account;
    /** @var  \PHPUnit_Extensions_Selenium2TestCase_Element */
    protected $contact;
    /** @var  \PHPUnit_Extensions_Selenium2TestCase_Element */
    protected $assignedTo;
    /** @var  \PHPUnit_Extensions_Selenium2TestCase_Element */
    protected $reporter;
    /** @var  \PHPUnit_Extensions_Selenium2TestCase_Element */
    protected $reminders;

    /** @var  Workflow */
    protected $workflow;

    public function __construct($testCase, $redirect = true)
    {
        parent::__construct($testCase, $redirect);

    }

    public function init()
    {
        $this->subject = $this->test->byId('orocrm_task_subject');
        $this->description = $this->test->byId('orocrm_task_description');
        $this->dueDate = $this->test->byId('datetime_selector_orocrm_task_dueDate');

        return $this;
    }

    public function setSubject($subject)
    {
        $this->subject->clear();
        $this->subject->value($subject);
        return $this;
    }

    public function getSubject()
    {
        return $this->subject->value();
    }

    public function setDescription($description)
    {
        $this->description->clear();
        $this->description->value($description);
        return $this;
    }

    public function getDescription()
    {
        return $this->description->value();
    }

    public function setDueDate($dueDate)
    {
        $this->dueDate->clear();
        $this->dueDate->value($dueDate);
        return $this;
    }

    public function getDueDate()
    {
        return $this->dueDate->value();
    }

    public function delete()
    {
        $this->test->byXpath("//a[@title = 'Delete Task']")->click();
        $this->test->byXpath("//div[div[contains(., 'Delete Confirmation')]]//a[text()='Yes, Delete']")->click();
        $this->waitPageToLoad();
        $this->waitForAjax();
        return new Tasks($this->test, false);
    }

    public function edit()
    {
        $this->test->byXpath("//div[@class='pull-left btn-group icons-holder']/a[@title = 'Edit Task']")->click();
        $this->waitPageToLoad();
        $this->waitForAjax();
        $this->init();
        return $this;
    }

    public function process(array $steps)
    {
        if (!isset($this->workflow)) {
            $this->workflow = new Workflow();
        }
        $this->workflow->process($this, $steps);

        return $this;
    }
}
