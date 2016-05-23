<?php

namespace OroCRM\Bundle\TaskBundle\Tests\Selenium\Pages;

use Oro\Bundle\TestFrameworkBundle\Pages\AbstractPageEntity;
use Oro\Bundle\TestFrameworkBundle\Pages\Workflow;
use Symfony\Component\Config\Definition\Exception\Exception;

/**
 * Class Task
 *
 * @package OroCRM\Bundle\SalesBundle\Tests\Selenium\Pages
 * @method Task assertTitle($title, $message = '')
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
    protected $dueTime;
    /** @var  \PHPUnit_Extensions_Selenium2TestCase_Element */
    protected $prioty;
    /** @var  \PHPUnit_Extensions_Selenium2TestCase_Element */
    protected $assignedTo;
    /** @var  \PHPUnit_Extensions_Selenium2TestCase_Element */
    protected $reminders;

    /** @var  Workflow */
    protected $workflow;

    public function setSubject($subject)
    {
        $this->subject = $this->test->byXpath("//*[@data-ftid='orocrm_task_subject']");
        $this->subject->clear();
        $this->subject->value($subject);
        return $this;
    }

    public function getSubject()
    {
        $this->subject = $this->test->byXpath("//*[@data-ftid='orocrm_task_subject']");
        return $this->subject->value();
    }

    public function setDescription($description)
    {
        return $this->setContentToTinymceElement('orocrm_task_description', $description);
    }

    public function getDescription()
    {
        $this->description = $this->test->byXpath("//*[@data-ftid='orocrm_task_description']");
        return $this->description->value();
    }

    /**
     * @param $dueDate string Due date
     * @return $this
     * @throws \Exception
     */
    public function setDueDate($dueDate)
    {
        $this->dueDate = $this->test->byXpath("//*[@data-ftid='orocrm_task_dueDate']/..".
            "/following-sibling::*//input[contains(@class,'datepicker-input')]");
        $this->dueTime = $this->test->byXpath("//*[@data-ftid='orocrm_task_dueDate']/..".
            "/following-sibling::*//input[contains(@class,'timepicker-input')]");
        $this->dueDate->clear();
        $this->dueTime->clear();
        $dueDateParts = [];
        if (preg_match('/^(.+\d{4}),?\s(\d{1,2}\:\d{2}\s\w{2})$/', $dueDate, $dueDateParts)) {
            $this->dueDate->click(); // focus
            $this->dueDate->value($dueDateParts[1]);
            $this->dueTime->click(); // focus
            $this->dueTime->clear();
            $this->dueTime->value($dueDateParts[2]);
        } else {
            throw new Exception("Value {$dueDate} is not a valid date");
        }

        return $this;
    }

    public function getDueDate()
    {
        $this->dueDate = $this->test->byXpath("//*[@data-ftid='orocrm_task_dueDate']/..".
            "/following-sibling::*//input[contains(@class,'datepicker-input')]");
        $this->dueTime = $this->test->byXpath("//*[@data-ftid='orocrm_task_dueDate']/..".
            "/following-sibling::*//input[contains(@class,'timepicker-input')]");
        return $this->dueDate->value() . ' ' . $this->dueTime->value();
    }

    public function delete()
    {
        $this->test->byXpath("//a[@title = 'Delete Task']")->click();
        $this->test->byXpath("//div[div[contains(., 'Delete Confirmation')]]//a[text()='Yes, Delete']")->click();
        $this->waitPageToLoad();
        $this->waitForAjax();
        return new Tasks($this->test, false);
    }

    /**
     * @return Task
     */
    public function edit()
    {
        $this->test->byXpath("//div[@class='pull-left btn-group icons-holder']/a[@title = 'Edit Task']")->click();
        $this->waitPageToLoad();
        $this->waitForAjax();

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

    public function createTask()
    {
        $this->test->byXpath("//div[@class='widget-actions-section']//button[contains(., 'Create Task')]")->click();
        $this->waitForAjax();

        return $this;
    }
}
