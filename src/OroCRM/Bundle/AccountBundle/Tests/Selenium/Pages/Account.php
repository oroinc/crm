<?php

namespace OroCRM\Bundle\AccountBundle\Tests\Selenium\Pages;

use Oro\Bundle\TestFrameworkBundle\Pages\AbstractPageEntity;

class Account extends AbstractPageEntity
{
    protected $filtersPath = '';
    protected $owner = "//div[starts-with(@id,'s2id_orocrm_account_form_owner')]/a";

    public function setName($name)
    {
        $element = $this->test->byXPath("//*[@data-ftid='orocrm_account_form_name']");
        $element->clear();
        $element->value($name);

        return $this;
    }

    public function verifyTag($tag)
    {
        if ($this->isElementPresent("//div[starts-with(@id,'s2id_orocrm_account_form_tags_autocomplete')]")) {
            $tags = $this->test->byXPath("//div[starts-with(@id,'s2id_orocrm_account_form_tags_autocomplete')]//input");
            $tags->click();
            $tags->value(substr($tag, 0, (strlen($tag)-1)));
            $this->waitForAjax();
            $this->assertElementPresent(
                "//div[@id='select2-drop']//div[contains(., '{$tag}')]",
                "Tag's autocomplete doesn't return entity"
            );
            $tags->clear();
        } else {
            if ($this->isElementPresent("//div[contains(@class, 'tags-holder')]")) {
                $this->assertElementPresent(
                    "//div[contains(@class, 'tags-holder')]//li[contains(., '{$tag}')]",
                    'Tag is not assigned to entity'
                );
            } else {
                throw new \Exception("Tag field can't be found");
            }
        }
        return $this;
    }

    /**
     * @param $tag
     * @return $this
     * @throws \Exception
     */
    public function setTag($tag)
    {
        if ($this->isElementPresent("//div[starts-with(@id,'s2id_orocrm_account_form_tags_autocomplete')]")) {
            $tags = $this->test->byXPath("//div[starts-with(@id,'s2id_orocrm_account_form_tags_autocomplete')]//input");
            $tags->click();
            $tags->value($tag);
            $this->waitForAjax();
            $this->assertElementPresent(
                "//div[@id='select2-drop']//div[contains(., '{$tag}')]",
                "Tag's autocomplete doesn't return entity"
            );
            $this->test->byXpath("//div[@id='select2-drop']//div[contains(., '{$tag}')]")->click();

            return $this;
        } else {
            throw new \Exception("Tag field can't be found");
        }
    }

    public function getName()
    {
        return $this->test->byXPath("//*[@data-ftid='orocrm_account_form_name']")->value();
    }

    /**
     * @param $contactName
     * @return $this
     */
    public function addContact($contactName)
    {
        $this->test->byXPath("//button[@class='btn btn-medium add-btn'][text()='Add']")->click();
        $this->waitForAjax();
        $this->assertElementPresent(
            "//div[@class='ui-dialog-titlebar ui-widget-header ui-corner-all ui-helper-clearfix ui-draggable-handle']".
            "/span[text()='Select contacts']",
            "Select contact widget-window is not opened"
        );
        $this->filterBy('Email', $contactName);
        $this->assignEntityFromEmbeddedGrid('Email', $contactName);
        $this->test->byXPath("//button[@class='btn btn-primary'][text()='Select']")->click();
        $this->waitForAjax();
        $this->assertElementPresent(
            "//div[@class='entities clearfix row-fluid list-group pull-left']//a[contains(., '{$contactName}')]",
            "Selected Contact is not added on create new Account page"
        );
        return $this;
    }

    /**
     * @param $contactName
     * @return $this
     */
    public function assertContactAdded($contactName)
    {
        $this->assertElementPresent(
            "//div[@class='contact-widget-wrapper']//div[@class='contact-box']//a[contains(., '{$contactName}')]",
            "Contact is not added or not visible on Account view page"
        );
        return $this;
    }

    public function edit()
    {
        $this->test->byXPath("//div[@class='pull-left btn-group icons-holder']/a[@title = 'Edit Account']")->click();
        $this->waitPageToLoad();
        $this->waitForAjax();

        return $this;
    }

    public function delete()
    {
        $this->test->byXPath("//div[@class='pull-left btn-group icons-holder']/a[contains(., 'Delete')]")->click();
        $this->test->byXPath("//div[div[contains(., 'Delete Confirmation')]]//a[text()='Yes, Delete']")->click();
        $this->waitPageToLoad();
        $this->waitForAjax();
        return new Accounts($this->test, false);
    }
}
