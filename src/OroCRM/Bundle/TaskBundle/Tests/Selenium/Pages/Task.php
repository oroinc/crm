<?php

namespace OroCRM\Bundle\TaskBundle\Tests\Selenium\Pages;

use Oro\Bundle\TestFrameworkBundle\Pages\AbstractPageEntity;
use Oro\Bundle\TestFrameworkBundle\Pages\Workflow;
use Symfony\Component\Config\Definition\Exception\Exception;

/**
 * Class Task
 *
 * @package OroCRM\Bundle\SalesBundle\Tests\Selenium\Pages
 * @method Task assertTitle() assertTitle($title, $message = '')
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

    public function __construct($testCase, $redirect = true)
    {
        parent::__construct($testCase, $redirect);

    }

    public function init()
    {
        $this->subject = $this->test->byId('orocrm_task_subject');
        $this->description = $this->test->byId('orocrm_task_description');

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
        $this->dueDate = $this->test->byId('date_selector_orocrm_task_dueDate');
        $this->dueTime = $this->test->byId('time_selector_orocrm_task_dueDate');
        $this->dueDate->clear();
        $this->dueTime->clear();
        if (preg_match('/^(.+)\s(\d{2}\:\d{2}\s\w{2})$/', $dueDate, $dueDate)) {
            $this->test->execute(
                array(
                    'script' => "$('#date_selector_orocrm_task_dueDate').val('$dueDate[1]');$('#date_selector_orocrm_task_dueDate').trigger('change').trigger('blur')",
                    'args' => array()
                )
            );
            $this->test->execute(
                array(
                    'script' => "$('#time_selector_orocrm_task_dueDate').val('$dueDate[2]');$('#date_selector_orocrm_task_dueDate').trigger('change').trigger('blur')",
                    'args' => array()
                )
            );
        } else {
            throw new Exception('Value "' + $dueDate + '" is not a valid date');
        }

        return $this;
    }

    public function getDueDate()
    {
        $this->dueDate = $this->test->byId('date_selector_orocrm_task_dueDate');
        $this->dueTime = $this->test->byId('time_selector_orocrm_task_dueDate');
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
