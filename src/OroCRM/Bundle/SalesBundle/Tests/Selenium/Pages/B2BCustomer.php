<?php

namespace OroCRM\Bundle\SalesBundle\Tests\Selenium\Pages;

use Oro\Bundle\TestFrameworkBundle\Pages\AbstractPageEntity;

/**
 * Class B2BCustomer
 * @package OroCRM\Bundle\SalesBundle\Tests\Selenium\Pages
 * @method B2BCustomer assertTitle($title, $message = '')
 */
class B2BCustomer extends AbstractPageEntity
{
    public function setName($name)
    {
        $element = $this->test->byXpath("//*[@data-ftid='orocrm_sales_b2bcustomer_form_name']");
        $element->clear();
        $element->value($name);
        return $this;
    }

    public function getName()
    {
        return $this->test->byXpath("//*[@data-ftid='orocrm_sales_b2bcustomer_form_name']")->value();
    }

    public function setOwner($owner)
    {
        $element = $this->test->byXpath("//div[starts-with(@id,'s2id_orocrm_sales_b2bcustomer_form_owner')]/a");
        $element->click();
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

    public function setAccount($account)
    {
        $element = $this->test->byXpath("//div[starts-with(@id,'s2id_orocrm_sales_b2bcustomer_form_account')]/a");
        $element->click();
        $this->waitForAjax();
        $this->test->byXpath("//div[@id='select2-drop']/div/input")->value($account);
        $this->waitForAjax();
        $this->assertElementPresent(
            "//div[@id='select2-drop']//div[contains(., '{$account}')]",
            "Owner autocomplete doesn't return search value"
        );
        $this->test->byXpath("//div[@id='select2-drop']//div[contains(., '{$account}')]")->click();

        return $this;
    }

    public function setChannel($channel)
    {
        $element = $this->test->byXpath("//div[starts-with(@id,'s2id_orocrm_sales_b2bcustomer_form_dataChannel')]/a");
        $element->click();
        $this->waitForAjax();
        if ($this->isElementPresent("//div[@id='select2-drop']/div/input")) {
            $this->test->byXpath("//div[@id='select2-drop']/div/input")->value($channel);
            $this->waitForAjax();
            $this->assertElementPresent(
                "//div[@id='select2-drop']//div[contains(., '{$channel}')]",
                "Channel autocomplete doesn't return search value"
            );
        }
        $this->test->byXpath("//div[@id='select2-drop']//div[contains(., '{$channel}')]")->click();

        return $this;
    }

    public function edit()
    {
        $this->test->byXpath("//div[@class='pull-left btn-group icons-holder']/a[@title = 'Edit Business Customer']")
            ->click();
        $this->waitPageToLoad();
        $this->waitForAjax();

        return $this;
    }

    public function delete()
    {
        $this->test->byXpath("//div[@class='pull-left btn-group icons-holder']/a[contains(., 'Delete')]")->click();
        $this->test->byXpath("//div[div[contains(., 'Delete Confirmation')]]//a[text()='Yes, Delete']")->click();
        $this->waitPageToLoad();
        $this->waitForAjax();
        return new B2BCustomers($this->test, false);
    }
}
