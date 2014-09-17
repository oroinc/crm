<?php

namespace OroCRM\Bundle\AccountBundle\Tests\Selenium\Pages;

use Oro\Bundle\TestFrameworkBundle\Pages\AbstractPageEntity;

class Account extends AbstractPageEntity
{
    /** @var   \PHPUnit_Extensions_Selenium2TestCase_Element */
    protected $accountName;
    /** @var   \PHPUnit_Extensions_Selenium2TestCase_Element */
    protected $street;
    /** @var   \PHPUnit_Extensions_Selenium2TestCase_Element */
    protected $city;
    /** @var   \PHPUnit_Extensions_Selenium2TestCase_Element */
    protected $zipcode;
    /** @var   \PHPUnit_Extensions_Selenium2TestCase_Element */
    protected $country;
    /** @var   \PHPUnit_Extensions_Selenium2TestCase_Element */
    protected $state;
    /** @var   \PHPUnit_Extensions_Selenium2TestCase_Element */
    protected $owner;

    public function setAccountName($accountName)
    {
        $this->accountName = $this->test->byId('orocrm_account_form_name');
        $this->accountName->clear();
        $this->accountName->value($accountName);
        return $this;
    }

    public function setOwner($owner)
    {
        $this->owner = $this->test->byXpath("//div[starts-with(@id,'s2id_orocrm_account_form_owner')]/a");
        $this->owner->click();
        $this->waitForAjax();
        $this->test->byXpath("//div[@id='select2-drop']/div/input")->value($owner);
        $this->waitForAjax();
        $this->assertElementPresent(
            "//div[@id='select2-drop']//div[contains(., '{$owner}')]",
            "Owner autocomplete doesn't return search value"
        );
        $this->test->byXpath("//div[@id='select2-drop']//div[contains(., '{$owner}')]")->click();

        return $this;

    }

    public function getOwner()
    {
        return;
    }

    public function verifyTag($tag)
    {
        if ($this->isElementPresent("//div[starts-with(@id,'s2id_orocrm_account_form_tags_autocomplete')]")) {
            $tags = $this->test->byXpath("//div[starts-with(@id,'s2id_orocrm_account_form_tags_autocomplete')]//input");
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
            $tags = $this->test->byXpath("//div[starts-with(@id,'s2id_orocrm_account_form_tags_autocomplete')]//input");
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

    public function getAccountName()
    {
        return $this->accountName->value();
    }

    public function setStreet($street)
    {
        $this->street = $this->test->byId('orocrm_account_form_billingAddress_street');
        $this->street->clear();
        $this->street->value($street);
        return $this;
    }

    public function getStreet()
    {
        return $this->street->value();
    }

    public function setCity($city)
    {
        $this->city = $this->test->byId('orocrm_account_form_billingAddress_city');
        $this->city->clear();
        $this->city->value($city);
        return $this;
    }

    public function getCity()
    {
        return $this->city->value();
    }

    public function setCountry($country)
    {
        $this->country = $this->test
            ->byXpath("//div[starts-with(@id,'s2id_orocrm_account_form_billingAddress_country')]/a");
        $this->country->click();
        $this->waitForAjax();
        $this->test->byXpath("//div[@id='select2-drop']/div/input")->value($country);
        $this->waitForAjax();
        $this->assertElementPresent(
            "//div[@id='select2-drop']//div[contains(., '{$country}')]",
            "Country's autocomplete doesn't return search value"
        );
        $this->test->byXpath("//div[@id='select2-drop']//div[contains(., '{$country}')]")->click();
        $this->waitForAjax();

        return $this;
    }

    public function edit()
    {
        $this->test->byXpath("//div[@class='pull-left btn-group icons-holder']/a[@title = 'Edit Account']")->click();
        $this->waitPageToLoad();
        $this->waitForAjax();
        $this->init();
        return $this;
    }

    public function delete()
    {
        $this->test->byXpath("//div[@class='pull-left btn-group icons-holder']/a[contains(., 'Delete')]")->click();
        $this->test->byXpath("//div[div[contains(., 'Delete Confirmation')]]//a[text()='Yes, Delete']")->click();
        $this->waitPageToLoad();
        $this->waitForAjax();
        return new Accounts($this->test, false);
    }
}
