<?php

namespace OroCRM\Bundle\ContactBundle\Tests\Selenium\Pages;

use Oro\Bundle\TestFrameworkBundle\Pages\AbstractPageEntity;

/**
 * Class Contact
 *
 * @package OroCRM\Bundle\ContactBundle\Tests\Selenium\Pages
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class Contact extends AbstractPageEntity
{
    /** @var  \PHPUnit_Extensions_Selenium2TestCase_Element */
    protected $namePrefix;
    /** @var  \PHPUnit_Extensions_Selenium2TestCase_Element */
    protected $firstName;
    /** @var  \PHPUnit_Extensions_Selenium2TestCase_Element */
    protected $lastName;
    /** @var  \PHPUnit_Extensions_Selenium2TestCase_Element */
    protected $nameSuffix;
    /** @var  \PHPUnit_Extensions_Selenium2TestCase_Element */
    protected $email;
    /** @var  \PHPUnit_Extensions_Selenium2TestCase_Element */
    protected $assignedTo;
    /** @var  \PHPUnit_Extensions_Selenium2TestCase_Element */
    protected $reportsTo;
    /** @var  \PHPUnit_Extensions_Selenium2TestCase_Element */
    protected $addressCollection;

    public function __construct($testCase, $redirect = true)
    {
        parent::__construct($testCase, $redirect);
    }

    public function init()
    {
        $this->namePrefix = $this->test->byId('orocrm_contact_form_namePrefix');
        $this->firstName = $this->test->byId('orocrm_contact_form_firstName');
        $this->lastName = $this->test->byId('orocrm_contact_form_lastName');
        $this->nameSuffix = $this->test->byId('orocrm_contact_form_nameSuffix');
        $this->email = $this->test->byId('orocrm_contact_form_emails_0_email');
        $this->assignedTo = $this->test->byXpath("//div[@id='s2id_orocrm_contact_form_assignedTo']/a");
        $this->reportsTo = $this->test->byXpath("//div[@id='s2id_orocrm_contact_form_reportsTo']/a");
        $this->addressCollection = $this->test->byId('orocrm_contact_form_addresses_collection');
        $this->owner = $this->test->byXpath("//div[@id='s2id_orocrm_contact_form_owner']/a");

        return $this;
    }

    public function setFirstName($firstName)
    {
        $this->firstName->clear();
        $this->firstName->value($firstName);
        return $this;
    }

    public function getFirstName()
    {
        return $this->firstName->value();
    }

    public function setLastName($lastName)
    {
        $this->lastName->clear();
        $this->lastName->value($lastName);
        return $this;
    }

    public function getLastName()
    {
        return $this->lastName->value();
    }

    public function setOwner($owner)
    {
        $this->test->moveto($this->owner);
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

    public function setEmail($email)
    {
        $this->email->clear();
        $this->email->value($email);
        return $this;
    }

    public function getEmail()
    {
        return $this->email->value();
    }

    public function setAddressTypes($values, $addressId = 0)
    {
        $xpath = "//input[@name = 'orocrm_contact_form[addresses][{$addressId}][types][]'";
        if ($this->isElementPresent("//div[@role='dialog']")) {
            $xpath = "//input[@name='orocrm_contact_address_form[types][]'";
        }
        foreach ($values as $type) {
            $this->test->byXpath("{$xpath} and @value = '{$type}']")->click();
        }

        return $this;
    }

    public function setAddressPrimary($value, $addressId = 0)
    {
        $primary = "//input[@id='orocrm_contact_form_addresses_{$addressId}_primary']";
        if ($this->isElementPresent("//div[@role='dialog']")) {
            $primary = ("//input[@id='orocrm_contact_address_form_primary']");
        }
        if ($value) {
            $this->test->byXpath($primary)->click();
        }

        return $this;
    }

    public function getAddressTypes($addressId)
    {
        $values = array();
        $types = $this->test->elements(
            $this->test->using('xpath')
                ->value("//input[@name = 'orocrm_contact_form[addresses][{$addressId}][types][]']")
        );
        foreach ($types as $type) {
            /** @var \PHPUnit_Extensions_Selenium2TestCase_Element $type */
            if ($type->selected()) {
                $values[] = $type->attribute('value');
            }
        }

        return $values;
    }

    public function getAddressPrimary($addressId = 0)
    {
        return $this->test->byId("orocrm_contact_form_addresses_{$addressId}_primary")->selected();
    }

    public function setAddressFirstName($value, $addressId = 0)
    {
        $addressFirstName = "//input[@id='orocrm_contact_form_addresses_{$addressId}_firstName']";
        if ($this->isElementPresent("//div[@role='dialog']")) {
            $addressFirstName = "//input[@id='orocrm_contact_address_form_firstName']";
        }
        $addressFirstName = $this->test->byXpath($addressFirstName);
        $this->test->moveto($addressFirstName);

        $addressFirstName->clear();
        $addressFirstName->value($value);

        return $this;

    }

    public function getAddressFirstName($addressId = 0)
    {
        $addressFirstName = $this->test->byId("orocrm_contact_form_addresses_{$addressId}_firstName");
        return $addressFirstName->attribute('value');
    }

    public function setAddressLastName($value, $addressId = 0)
    {
        $addressLastName = "//input[@id='orocrm_contact_form_addresses_{$addressId}_lastName']";
        if ($this->isElementPresent("//div[@role='dialog']")) {
            $addressLastName = "//input[@id='orocrm_contact_address_form_lastName']";
        }
        $addressLastName = $this->test->byXpath($addressLastName);
        $this->test->moveto($addressLastName);

        $addressLastName->clear();
        $addressLastName->value($value);

        return $this;

    }

    public function getAddressLastName($addressId = 0)
    {
        $addressLastName = $this->test->byId("orocrm_contact_form_addresses_{$addressId}_lastName");
        return $addressLastName->attribute('value');
    }

    public function setAddressStreet($value, $addressId = 0)
    {
        $street = "//input[@id='orocrm_contact_form_addresses_{$addressId}_street']";
        if ($this->isElementPresent("//div[@role='dialog']")) {
            $street = "//input[@id='orocrm_contact_address_form_street']";
        }
        $street = $this->test->byXpath($street);
        $this->test->moveto($street);

        $street->clear();
        $street->value($value);

        return $this;
    }

    public function getAddressStreet($addressId = 0)
    {
        $street = $this->test->byId("orocrm_contact_form_addresses_{$addressId}_street");
        return $street->attribute('value');
    }

    public function setAddressCity($value, $addressId = 0)
    {
        $xpathCity = "//input[@id='orocrm_contact_form_addresses_{$addressId}_city']";
        if ($this->isElementPresent("//div[@role='dialog']")) {
            $xpathCity = "//input[@id='orocrm_contact_address_form_city']";
        }
        $city = $this->test->byXpath($xpathCity);
        $this->test->moveto($city);

        $city->clear();
        $city->value($value);
        return $this;
    }

    public function getAddressCity($addressId = 0)
    {
        $city = $this->test->byId("orocrm_contact_form_addresses_{$addressId}_city");
        return $city->attribute('value');
    }

    public function setAddressPostalCode($value, $addressId = 0)
    {
        $xpathZipcode = "//input[@id='orocrm_contact_form_addresses_{$addressId}_postalCode']";
        if ($this->isElementPresent("//div[@role='dialog']")) {
            $xpathZipcode = "//input[@id='orocrm_contact_address_form_postalCode']";
        }
        $zipcode = $this->test->byXpath($xpathZipcode);
        $this->test->moveto($zipcode);

        $zipcode->clear();
        $zipcode->value($value);
        return $this;
    }

    public function getAddressPostalCode($addressId = 0)
    {
        $zipcode = $this->test->byId("orocrm_contact_form_addresses_{$addressId}_postalCode");
        return $zipcode->attribute('value');
    }

    public function setAddressCountry($value, $addressId = 0)
    {
        $country = "//div[@id='s2id_orocrm_contact_form_addresses_{$addressId}_country']/a";
        if ($this->isElementPresent("//div[@role='dialog']")) {
            $country = "//div[@id='s2id_orocrm_contact_address_form_country']/a";
        }
        $country = $this->test->byXpath($country);
        $this->test->moveto($country);

        $country->click();
        $this->waitForAjax();
        $this->test->byXpath("//div[@id='select2-drop']/div/input")->value($value);
        $this->waitForAjax();
        $this->assertElementPresent(
            "//div[@id='select2-drop']//div[normalize-space(.) = '{$value}']",
            "Country's autocomplete doesn't return search value"
        );
        $this->test->byXpath("//div[@id='select2-drop']//div[normalize-space(.) = '{$value}']")->click();
        $this->waitForAjax();

        return $this;
    }

    public function typeAddressCountry($value, $addressId = 0)
    {
        $country = "//div[@id='s2id_orocrm_contact_form_addresses_{$addressId}_country']/a";
        if ($this->isElementPresent("//div[@role='dialog']")) {
            $country = "//div[@id='s2id_orocrm_contact_address_form_country']/a";
        }
        $country = $this->test->byXpath($country);
        $this->test->moveto($country);

        $country->click();
        $this->waitForAjax();
        $this->test->byXpath("//div[@id='select2-drop']/div/input")->value($value);
        $this->waitForAjax();
        $this->assertElementPresent(
            "//div[@id='select2-drop']//div[contains(., '{$value}')]",
            "Country's autocomplete doesn't return search value"
        );
        $this->test->byXpath("//div[@id='select2-drop']//div[contains(., '{$value}')]")->click();
        $this->waitForAjax();

        return $this;
    }

    public function getAddressCountry($addressId = 0)
    {
        return $this->test
            ->byXpath("//div[@id = 's2id_orocrm_contact_form_addresses_{$addressId}_country']/a/span")
            ->text();
    }

    public function setAddressRegion($region, $addressId = 0)
    {
        $xpath = "//div[@id='s2id_orocrm_contact_form_addresses_{$addressId}_region']/a";
        if ($this->isElementPresent("//div[@role='dialog']")) {
            $xpath = "//div[@id='s2id_orocrm_contact_address_form_region']/a";
        }
        $xpath = $this->test->byXpath($xpath);
        $this->test->moveto($xpath);

        $xpath->click();
        $this->waitForAjax();
        $this->test->byXpath("//div[@id='select2-drop']/div/input")->value($region);
        $this->waitForAjax();
        $this->assertElementPresent(
            "//div[@id='select2-drop']//div[normalize-space(.) = '{$region}']",
            "Country's autocopmlete doesn't return search value"
        );
        $this->test->byXpath("//div[@id='select2-drop']//div[normalize-space(.) = '{$region}']")->click();

        return $this;
    }

    public function typeAddressRegion($region, $addressId = 0)
    {
        $xpath = "//div[@id='s2id_orocrm_contact_form_addresses_{$addressId}_region']/a";
        if ($this->isElementPresent("//div[@role='dialog']")) {
            $xpath = "//div[@id='s2id_orocrm_contact_address_form_region']/a";
        }
        $xpath = $this->test->byXpath($xpath);
        $this->test->moveto($xpath);

        $xpath->click();
        $this->waitForAjax();
        $this->test->byXpath("//div[@id='select2-drop']/div/input")->value($region);
        $this->waitForAjax();
        $this->assertElementPresent(
            "//div[@id='select2-drop']//div[contains(., '{$region}')]",
            "Country's autocopmlete doesn't return search value"
        );
        $this->test->byXpath("//div[@id='select2-drop']//div[contains(., '{$region}')]")->click();

        return $this;
    }

    public function getAddressRegion($addressId = 0)
    {
        return $this->test
            ->byXpath("//div[@id = 's2id_orocrm_contact_form_addresses_{$addressId}_region']/a/span")
            ->text();
    }

    public function setAddress($data, $addressId = 0)
    {
        if ($this->isElementPresent("//button[@data-action-name='add_address']")) {
            // click Add address button
            $this->test->byXpath("//button[@data-action-name='add_address']")->click();
            $this->waitForAjax();
        } elseif (!$this->isElementPresent(
            "//div[@id='orocrm_contact_form_addresses_collection']/div[@data-content='{$addressId}' or " .
            "@data-content='orocrm_contact_form[addresses][{$addressId}]']"
        )
        ) {
            //click Add
            $addButton = $this->test->byXpath(
                "//div[@class='row-oro'][div[@id='orocrm_contact_form_addresses_collection']]" .
                "//a[@class='btn add-list-item']"
            );
            $this->test->moveto($addButton);
            $addButton->click();
            $this->waitForAjax();
        }

        foreach ($data as $key => $value) {
            $method = 'setAddress' . ucfirst($key);
            $this->$method($value, $addressId);
        }

        if ($this->isElementPresent("//div[@role='dialog']")) {
            $this->test->byXpath("//div[@class='form-actions widget-actions']//button[@type='submit']")->click();
            $this->waitForAjax();
        }

        return $this;
    }

    public function getAddress(&$values, $addressId = 0)
    {
        $values['types'] = $this->getAddressTypes($addressId);
        $values['primary'] = $this->getAddressPrimary($addressId);
        $values['firstName'] = $this->getAddressFirstName($addressId);
        $values['lastName'] = $this->getAddressLastName($addressId);
        $values['street'] = $this->getAddressStreet($addressId);
        $values['city'] = $this->getAddressCity($addressId);
        $values['postalCode'] = $this->getAddressPostalCode($addressId);
        $values['country'] = $this->getAddressCountry($addressId);
        $values['region'] = $this->getAddressRegion($addressId);

        return $this;
    }

    public function setAssignedTo($assignedTo)
    {
        $this->assignedTo->click();
        $this->waitForAjax();
        $this->test->byXpath("//div[@id='select2-drop']/div/input")->value($assignedTo);
        $this->waitForAjax();
        $this->assertElementPresent(
            "//div[@id='select2-drop']//div[contains(., '{$assignedTo}')]",
            "Assigned to autocomplete doesn't return search value"
        );
        $this->test->byXpath("//div[@id='select2-drop']//div[contains(., '{$assignedTo}')]")->click();

        return $this;
    }

    public function setReportsTo($reportsTo)
    {
        $this->reportsTo->click();
        $this->waitForAjax();
        $this->test->byXpath("//div[@id='select2-drop']/div/input")->value($reportsTo);
        $this->waitForAjax();
        $this->assertElementPresent(
            "//div[@id='select2-drop']//div[contains(., '{$reportsTo}')]",
            "Reports to autocomplete doesn't return search value"
        );
        $this->test->byXpath("//div[@id='select2-drop']//div[contains(., '{$reportsTo}')]")->click();

        return $this;
    }

    public function edit()
    {
        $this->test->byXpath("//div[@class='pull-left btn-group icons-holder']/a[@title = 'Edit Contact']")->click();
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
        return new Contacts($this->test, false);
    }
}
