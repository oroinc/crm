<?php

namespace OroCRM\Bundle\CallBundle\Tests\Selenium\Pages;

use Oro\Bundle\TestFrameworkBundle\Pages\AbstractPageEntity;

/**
 * Class Call
 * @package OroCRM\Bundle\CallBundle\Tests\Selenium\Pages
 * {@inheritdoc}
 */
class Call extends AbstractPageEntity
{
    public function edit()
    {
        $this->test->byXpath("//div[@class='pull-left btn-group icons-holder']/a[@title = 'Edit Call']")->click();
        $this->waitPageToLoad();
        $this->waitForAjax();

        return $this;
    }

    /**
     * @param string $call
     * @return $this
     */
    public function setCallSubject($call)
    {
        $this->$call = $this->test->byXpath("//*[@data-ftid='orocrm_call_form_subject']");
        $this->$call->clear();
        $this->$call->value($call);

        return $this;
    }

    /**
     * @param string $phone
     * @return $this
     */
    public function setPhoneNumber($phone)
    {
        $this->test->byXpath("//div[starts-with(@id,'s2id_orocrm_call_form_phoneNumber')]/a")->click();
        $this->waitForAjax();
        $this->test->byXpath("//div[@id='select2-drop']/div/input")->value($phone);
        $this->waitForAjax();
        $this->test->byXpath("//div[@id='select2-drop']//div[contains(., '{$phone}')]")->click();

        return $this;
    }

    /**
     * @param $contact
     * @return $this
     */
    public function setContact($contact)
    {
        $this->test->byXpath("//div[starts-with(@id,'s2id_orocrm_call_form_relatedContact')]/a")->click();
        $this->waitForAjax();
        $this->test->byXpath("//div[@id='select2-drop']/div/input")->value($contact);
        $this->waitForAjax();
        $this->assertElementPresent(
            "//div[@id='select2-drop']//div[contains(., '{$contact}')]",
            "Assigned to autocomplete doesn't return search value"
        );
        $this->test->byXpath("//div[@id='select2-drop']//div[contains(., '{$contact}')]")->click();
        return $this;
    }

    public function logCall()
    {
        $this->test->byXpath("//div[@class='widget-actions-section']//button[contains(., 'Log call')]")->click();
        $this->waitForAjax();

        return $this;
    }
}
