<?php

namespace OroCRM\Bundle\CaseBundle\Tests\Selenium\Pages;

use Oro\Bundle\TestFrameworkBundle\Pages\AbstractPageEntity;

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
    /** @var  \PHPUnit_Extensions_Selenium2TestCase_Element_Select */
    protected $status;

    public function init()
    {
        $this->subject = $this->test->byXpath("//*[@data-ftid='orocrm_case_entity_form_subject']");
        $this->resolution = $this->test->byXpath("//*[@data-ftid='orocrm_case_entity_form_resolution']");
        $this->status = $this->test
            ->select($this->test->byXpath("//*[@data-ftid='orocrm_case_entity_form_status']"));

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
        return $this->setContentToTinymceElement('orocrm_case_entity_form_description', $description);
    }

    public function getDescription()
    {
        return $this->description->value();
    }

    public function setStatus($status)
    {
        $this->status->selectOptionByLabel($status);

        return $this;
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
}
