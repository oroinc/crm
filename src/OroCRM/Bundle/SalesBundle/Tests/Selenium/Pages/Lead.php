<?php

namespace OroCRM\Bundle\SalesBundle\Tests\Selenium\Pages;

use Oro\Bundle\TestFrameworkBundle\Pages\AbstractPageEntity;
use OroCRM\Bundle\ContactBundle\Tests\Selenium\Pages\Contacts;

/**
 * Class Lead
 *
 * @package OroCRM\Bundle\SalesBundle\Tests\Selenium\Pages
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class Lead extends AbstractPageEntity
{
    /** @var  \PHPUnit_Extensions_Selenium2TestCase_Element */
    protected $name;
    /** @var  \PHPUnit_Extensions_Selenium2TestCase_Element */
    protected $firstName;
    /** @var  \PHPUnit_Extensions_Selenium2TestCase_Element */
    protected $lastName;
    /** @var  \PHPUnit_Extensions_Selenium2TestCase_Element */
    protected $contact;
    /** @var  \PHPUnit_Extensions_Selenium2TestCase_Element */
    protected $jobTitle;
    /** @var  \PHPUnit_Extensions_Selenium2TestCase_Element */
    protected $phone;
    /** @var  \PHPUnit_Extensions_Selenium2TestCase_Element */
    protected $email;
    /** @var  \PHPUnit_Extensions_Selenium2TestCase_Element */
    protected $companyName;
    /** @var  \PHPUnit_Extensions_Selenium2TestCase_Element */
    protected $website;
    /** @var  \PHPUnit_Extensions_Selenium2TestCase_Element */
    protected $employees;
    /** @var  \PHPUnit_Extensions_Selenium2TestCase_Element */
    protected $industry;
    /** @var  \PHPUnit_Extensions_Selenium2TestCase_Element */
    protected $addressCollection;
    /** @var  \PHPUnit_Extensions_Selenium2TestCase_Element */
    protected $campaign;

    protected $owner = "//div[starts-with(@id,'s2id_orocrm_sales_lead_form_owner')]/a";

    public function init()
    {
        $this->name = $this->test->byXpath("//*[@data-ftid='orocrm_sales_lead_form_name']");
        $this->firstName = $this->test->byXpath("//*[@data-ftid='orocrm_sales_lead_form_firstName']");
        $this->lastName = $this->test->byXpath("//*[@data-ftid='orocrm_sales_lead_form_lastName']");
        $this->contact = $this->test->byXpath("//div[starts-with(@id,'s2id_orocrm_sales_lead_form_contact')]/a");
        $this->jobTitle = $this->test->byXpath("//*[@data-ftid='orocrm_sales_lead_form_jobTitle']");
        $this->phone = $this->test->byXpath("//*[@data-ftid='orocrm_sales_lead_form_phones_0_phone']");
        $this->email = $this->test->byXpath("//*[@data-ftid='orocrm_sales_lead_form_emails_0_email']");
        $this->companyName = $this->test->byXpath("//*[@data-ftid='orocrm_sales_lead_form_companyName']");
        $this->website = $this->test->byXpath("//*[@data-ftid='orocrm_sales_lead_form_website']");
        $this->employees = $this->test->byXpath("//*[@data-ftid='orocrm_sales_lead_form_numberOfEmployees']");
        $this->industry = $this->test->byXpath("//*[@data-ftid='orocrm_sales_lead_form_industry']");
        $this->campaign = $this->test->byXpath(
            "//div[starts-with(@id,'s2id_orocrm_sales_lead_form_campaign')]/a"
        );
        $this->addressCollection = $this->test->byXpath("//div[@data-ftid='orocrm_sales_lead_form_addresses']");

        return $this;
    }

    public function setName($name)
    {
        $this->name->clear();
        $this->name->value($name);

        return $this;
    }

    public function getName()
    {
        return $this->name->value();
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

    public function setContact($contact)
    {
        $this->contact->click();
        $this->waitForAjax();
        $this->test->byXpath("//div[@id='select2-drop']/div/input")->value($contact);
        $this->waitForAjax();
        $this->assertElementPresent(
            "//div[@id='select2-drop']//div[contains(., '{$contact}')]",
            "Assigned to autocomplete doesn't return search value"
        );
        $this->test->byXpath("//div[@id='select2-drop']//div[contains(., '{$contact}')]")->click();
    }

    public function getContact()
    {
        return $this->test->byXpath("//div[starts-with(@id,'s2id_orocrm_sales_lead_form_contact')]/a/span")->text();
    }

    public function setJobTitle($jobTitle)
    {
        $this->jobTitle->clear();
        $this->jobTitle->value($jobTitle);

        return $this;
    }

    public function getJobTitle()
    {
        return $this->jobTitle->value();
    }

    public function setPhone($phone)
    {
        $this->phone->clear();
        $this->phone->value($phone);

        return $this;
    }

    public function getPhone()
    {
        return $this->phone->value();
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

    public function setCompany($companyName)
    {
        $this->companyName->clear();
        $this->companyName->value($companyName);

        return $this;
    }

    public function getCompany()
    {
        return $this->companyName->value();
    }

    public function setWebsite($website)
    {
        $this->website->clear();
        $this->website->value($website);

        return $this;
    }

    public function getWebsite()
    {
        return $this->website->value();
    }

    public function setEmployees($employees)
    {
        $this->employees->clear();
        $this->employees->value($employees);

        return $this;
    }

    public function getEmployees()
    {
        return $this->employees->value();
    }

    public function setCampaign($campaign)
    {
        $this->test->moveto($this->campaign);
        $this->campaign->click();
        $this->waitForAjax();
        $this->test->byXpath("//div[@id='select2-drop']/div/input")->value($campaign);
        $this->waitForAjax();
        $this->assertElementPresent(
            "//div[@id='select2-drop']//div[contains(., '{$campaign}')]",
            "Campaign autocomplete doesn't return search value"
        );
        $this->test->byXpath("//div[@id='select2-drop']//div[contains(., '{$campaign}')]")->click();

        return $this;
    }

    public function setAddressPrimary($value, $addressId = 0)
    {
        $primary = "//input[@data-ftid='orocrm_sales_lead_form_addresses_{$addressId}_primary']";
        if ($this->isElementPresent("//div[@role='dialog']")) {
            $primary = ("//input[@data-ftid='orocrm_sales_lead_address_form_primary']");
        }
        if ($value) {
            $this->test->byXpath($primary)->click();
        }

        return $this;
    }

    public function getAddressPrimary($addressId = 0)
    {
        return $this->test
            ->byXpath("//*[@data-ftid='orocrm_sales_lead_form_addresses_{$addressId}_primary']")->selected();
    }

    public function setAddressFirstName($value, $addressId = 0)
    {
        $addressFirstName = "//input[@data-ftid='orocrm_sales_lead_form_addresses_{$addressId}_firstName']";
        if ($this->isElementPresent("//div[@role='dialog']")) {
            $addressFirstName = "//input[@data-ftid='orocrm_sales_lead_address_form_firstName']";
        }
        $addressFirstName = $this->test->byXpath($addressFirstName);
        $this->test->moveto($addressFirstName);

        $addressFirstName->clear();
        $addressFirstName->value($value);

        return $this;
    }

    public function getAddressFirstName($addressId = 0)
    {
        $addressFirstName = $this->test
            ->byXpath("//*[@data-ftid='orocrm_sales_lead_form_addresses_{$addressId}_firstName']");

        return $addressFirstName->attribute('value');
    }

    public function setAddressLastName($value, $addressId = 0)
    {
        $addressLastName = "//input[@data-ftid='orocrm_sales_lead_form_addresses_{$addressId}_lastName']";
        if ($this->isElementPresent("//div[@role='dialog']")) {
            $addressLastName = "//input[@data-ftid='orocrm_sales_lead_address_form_lastName']";
        }
        $addressLastName = $this->test->byXpath($addressLastName);
        $this->test->moveto($addressLastName);

        $addressLastName->clear();
        $addressLastName->value($value);

        return $this;
    }

    public function getAddressLastName($addressId = 0)
    {
        $addressLastName = $this->test
            ->byXpath("//*[@data-ftid='orocrm_sales_lead_form_addresses_{$addressId}_lastName']");
        return $addressLastName->attribute('value');
    }

    public function setAddressStreet($value, $addressId = 0)
    {
        $street = "//input[@data-ftid='orocrm_sales_lead_form_addresses_{$addressId}_street']";
        if ($this->isElementPresent("//div[@role='dialog']")) {
            $street = "//input[@data-ftid='orocrm_sales_lead_address_form_street']";
        }
        $street = $this->test->byXpath($street);
        $this->test->moveto($street);

        $street->clear();
        $street->value($value);

        return $this;
    }

    public function getAddressStreet($addressId = 0)
    {
        $street = $this->test->byXpath("//*[@data-ftid='orocrm_sales_lead_form_addresses_{$addressId}_street']");
        return $street->attribute('value');
    }

    public function setAddressCity($value, $addressId = 0)
    {
        $xpathCity = "//input[@data-ftid='orocrm_sales_lead_form_addresses_{$addressId}_city']";
        if ($this->isElementPresent("//div[@role='dialog']")) {
            $xpathCity = "//input[@data-ftid='orocrm_sales_lead_address_form_city']";
        }
        $city = $this->test->byXpath($xpathCity);
        $this->test->moveto($city);

        $city->clear();
        $city->value($value);

        return $this;
    }

    public function getAddressCity($addressId = 0)
    {
        $city = $this->test->byXpath("//*[@data-ftid='orocrm_sales_lead_form_addresses_{$addressId}_city']");

        return $city->attribute('value');
    }

    public function setAddressPostalCode($value, $addressId = 0)
    {
        $xpathZipcode = "//input[@data-ftid='orocrm_sales_lead_form_addresses_{$addressId}_postalCode']";
        if ($this->isElementPresent("//div[@role='dialog']")) {
            $xpathZipcode = "//input[@data-ftid='orocrm_sales_lead_address_form_postalCode']";
        }
        $zipcode = $this->test->byXpath($xpathZipcode);
        $this->test->moveto($zipcode);

        $zipcode->clear();
        $zipcode->value($value);

        return $this;
    }

    public function getAddressPostalCode($addressId = 0)
    {
        $zipcode = $this->test
            ->byXpath("//*[@data-ftid='orocrm_sales_lead_form_addresses_{$addressId}_postalCode']");

        return $zipcode->attribute('value');
    }

    public function setAddressCountry($value, $addressId = 0)
    {
        $country = "//div[starts-with(@id,'s2id_orocrm_sales_lead_form_addresses_{$addressId}_country')]/a";
        if ($this->isElementPresent("//div[@role='dialog']")) {
            $country = "//div[starts-with(@id,'s2id_orocrm_sales_lead_address_form_country')]/a";
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

    public function getAddressCountry($addressId = 0)
    {
        return $this->test
            ->byXpath("//div[starts-with(@id,'s2id_orocrm_sales_lead_form_addresses_{$addressId}_country')]/a/span")
            ->text();
    }

    public function setAddressRegion($region, $addressId = 0)
    {
        $xpath = "//div[starts-with(@id,'s2id_orocrm_sales_lead_form_addresses_{$addressId}_region')]/a";
        if ($this->isElementPresent("//div[@role='dialog']")) {
            $xpath = "//div[starts-with(@id,'s2id_orocrm_sales_lead_address_form_region')]/a";
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

    public function getAddressRegion($addressId = 0)
    {
        return $this->test
            ->byXpath("//div[starts-with(@id,'s2id_orocrm_sales_lead_form_addresses_{$addressId}_region')]/a/span")
            ->text();
    }

    public function setAddress($data, $addressId = 0)
    {
        if ($this->isElementPresent("//button[@data-action-name='add_address']")) {
            // click Add address button
            $this->test->byXpath("//button[@data-action-name='add_address']")->click();
            $this->waitForAjax();
        } elseif (!$this->isElementPresent(
            "//div[@data-ftid='orocrm_sales_lead_form_addresses']/div[@data-content='{$addressId}' or " .
            "@data-content='orocrm_sales_lead_form[addresses][{$addressId}]']"
        )
        ) {
            //click Add
            $addButton = $this->test->byXpath(
                "//div[@class='row-oro'][div[@data-ftid='orocrm_sales_lead_form_addresses']]" .
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

    public function checkStatus($status)
    {
        $this->assertElementPresent("//div[@class='status-enabled pull-left'][contains(., '{$status}')]");

        return $this;
    }

    public function edit()
    {
        $this->test->byXpath("//div[@class='pull-left btn-group icons-holder']/a[@title = 'Edit Lead']")->click();
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

    public function setChannel($channel)
    {
        $element = $this->test->byXpath("//div[starts-with(@id,'s2id_orocrm_sales_lead_form_dataChannel')]/a");
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
}
