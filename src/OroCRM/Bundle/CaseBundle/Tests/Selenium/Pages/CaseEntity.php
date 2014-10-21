<?php

namespace OroCRM\Bundle\CaseBundle\Tests\Selenium\Pages;

use Oro\Bundle\TestFrameworkBundle\Pages\AbstractPageEntity;
use Oro\Bundle\TestFrameworkBundle\Pages\Workflow;

/**
 * Class Case
 *
 * @package OroCRM\Bundle\CaseBundle\Tests\Selenium\Pages
 */
class CaseEntity extends AbstractPageEntity
{
    protected $owner = "//div[starts-with(@id,'s2id_orocrm_case_form_owner')]/a";

    /** @var  \PHPUnit_Extensions_Selenium2TestCase_Element */
    protected $subject;
    /** @var  \PHPUnit_Extensions_Selenium2TestCase_Element */
    protected $description;
    /** @var  \PHPUnit_Extensions_Selenium2TestCase_Element */
    protected $resolution;

    /** @var  Workflow */
    protected $workflow;

    public function __construct($testCase, $redirect = true)
    {
        parent::__construct($testCase, $redirect);

    }

    public function init()
    {
        $this->subject = $this->test->byId('orocrm_case_entity_form_subject');
        $this->description = $this->test->byId('orocrm_case_entity_form_description');
        $this->resolution = $this->test->byId('orocrm_case_entity_form_resolution');

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

    public function delete()
    {
        $this->test->byXpath("//a[@title = 'Delete Case']")->click();
        $this->test->byXpath("//div[div[contains(., 'Delete Confirmation')]]//a[text()='Yes, Delete']")->click();
        $this->waitPageToLoad();
        $this->waitForAjax();
        return new CaseEntity($this->test, false);
    }

    /**
     * @return CaseEntity
     */
    public function edit()
    {
        $this->test->byXpath("//div[@class='pull-left btn-group icons-holder']/a[@title = 'Edit Case']")->click();
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
