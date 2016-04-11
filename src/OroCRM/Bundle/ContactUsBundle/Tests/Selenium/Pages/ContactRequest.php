<?php

namespace OroCRM\Bundle\ContactUsBundle\Tests\Selenium\Pages;

use Oro\Bundle\TestFrameworkBundle\Pages\AbstractPageEntity;

/**
 * Class ContactRequest
 * @package OroCRM\Bundle\ContactUsBundle\Tests\Selenium\Pages
 * {@inheritdoc}
 */
class ContactRequest extends AbstractPageEntity
{
    /**
     * @param string $name
     * @return $this
     */
    public function setFirstName($name)
    {
        $field = $this->test->byXpath("//*[@data-ftid='orocrm_magento_contactus_contact_request_firstName']");
        $field->clear();
        $field->value($name);

        return $this;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setLastName($name)
    {
        $field = $this->test->byXpath("//*[@data-ftid='orocrm_magento_contactus_contact_request_lastName']");
        $field->clear();
        $field->value($name);

        return $this;
    }

    /**
     * @param string $email
     * @return $this
     */
    public function setEmail($email)
    {
        $field = $this->test->byXpath("//*[@data-ftid='orocrm_magento_contactus_contact_request_emailAddress']");
        $field->clear();
        $field->value($email);

        return $this;
    }

    /**
     * @param string $comment
     * @return $this
     */
    public function setComment($comment)
    {
        $field = $this->test->byXpath("//*[@data-ftid='orocrm_magento_contactus_contact_request_comment']");
        $field->clear();
        $field->value($comment);

        return $this;
    }

    /**
     * @param string $phone
     * @return $this
     */
    public function setPhone($phone)
    {
        $field = $this->test->byXpath("//*[@data-ftid='orocrm_magento_contactus_contact_request_phone']");
        $field->clear();
        $field->value($phone);

        return $this;
    }

    /**
     * @return $this
     */
    public function edit()
    {
        $this->test->byXpath(
            "//div[@class='pull-left btn-group icons-holder']/a[@title = 'Edit Contact Request']"
        )->click();
        $this->waitPageToLoad();
        $this->waitForAjax();

        return $this;
    }

    /**
     * @return ContactRequest
     */
    public function delete()
    {
        $this->test->byXpath("//div[@class='pull-left btn-group icons-holder']/a[contains(., 'Delete')]")->click();
        $this->test->byXpath("//div[div[contains(., 'Delete Confirmation')]]//a[text()='Yes, Delete']")->click();
        $this->waitPageToLoad();
        $this->waitForAjax();
        return new ContactRequest($this->test, false);
    }

    /**
     * @param string $step
     * @return $this
     */
    public function checkStep($step)
    {
        $this->assertElementPresent("//ul[contains(@class, 'workflow-step-list')]//li[contains (.,'{$step}')]");
        return $this;
    }

    /**
     * @return $this
     */
    public function resolve()
    {
        $this->test->byXPath("//div[@class='btn-group']/a[@title='Resolve']")->click();
        $this->waitPageToLoad();
        $this->waitForAjax();
        $this->assertElementPresent(
            "//div[starts-with(@class, 'ui-dialog-titlebar')]/span[normalize-space(.)='Resolve']",
            'Resolve widget window is not opened'
        );

        return $this;
    }

    /**
     * @param string $feedback
     * @return $this
     */
    public function setFeedback($feedback)
    {
        $field = $this->test->byXpath("//*[@data-ftid='oro_workflow_transition_feedback']");
        $field->clear();
        $field->value($feedback);

        return $this;
    }

    /**
     * @return $this
     */
    public function submit()
    {
        $this->test->byXPath("//div[@class='widget-actions-section']//button[normalize-space()='Submit']")->click();
        $this->waitPageToLoad();
        $this->waitForAjax();
        $this->assertElementNotPresent(
            "//div[starts-with(@class, 'ui-dialog-titlebar')]",
            'Resolve widget window is still opened'
        );
        return $this;
    }

    /**
     * @return $this
     */
    public function logCall()
    {
        $this->test->byXPath("//div[@class='btn-group']/a[@title='Log call']")->click();
        $this->waitPageToLoad();
        $this->waitForAjax();
        $this->assertElementPresent(
            "//div[starts-with(@class, 'ui-dialog-titlebar')]/span[normalize-space(.)='Log call']",
            'Log Call widget window is not opened'
        );

        return $this;
    }

    /**
     * @param string $callSubject
     * @return $this
     */
    public function setCallSubject($callSubject)
    {
        $field = $this->test->byXpath("//*[@data-ftid='oro_workflow_transition_call_subject']");
        $field->clear();
        $field->value($callSubject);

        return $this;
    }

    /**
     * @param string $type
     * @param string $value
     * @return object $this
     */
    public function checkCommunication($type, $value)
    {
        $this->assertElementPresent(
            "//div[@class='title'][span[normalize-space(.)='{$type}']]" .
            "/following-sibling::div//td/a[@title[normalize-space(.)='{$value}']]"
        );

        return $this;
    }

    /**
     * @param string $feedback
     * @return $this
     */
    public function checkFeedback($feedback)
    {
        $this->assertElementPresent(
            "//div[@class='responsive-block']//label[normalize-space()='Feedback']" .
            "/following-sibling::div[normalize-space()='{$feedback}']"
        );

        return $this;
    }
}
