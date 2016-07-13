<?php

namespace OroCRM\Bundle\SalesBundle\Tests\Selenium\Pages;

use Oro\Bundle\TestFrameworkBundle\Pages\AbstractPageEntity;
use OroCRM\Bundle\ContactBundle\Tests\Selenium\Pages\Contacts;

/**
 * Class Lead
 *
 * @package OroCRM\Bundle\SalesBundle\Tests\Selenium\Pages
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
    protected $address;
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
        $this->email = $this->test->byXpath("//*[@data-ftid='orocrm_sales_lead_form_email']");
        $this->companyName = $this->test->byXpath("//*[@data-ftid='orocrm_sales_lead_form_companyName']");
        $this->website = $this->test->byXpath("//*[@data-ftid='orocrm_sales_lead_form_website']");
        $this->employees = $this->test->byXpath("//*[@data-ftid='orocrm_sales_lead_form_numberOfEmployees']");
        $this->industry = $this->test->byXpath("//*[@data-ftid='orocrm_sales_lead_form_industry']");
        $this->campaign = $this->test->byXpath(
            "//div[starts-with(@id,'s2id_orocrm_sales_lead_form_campaign')]/a"
        );

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

    public function setAddressLabel($value)
    {
        $addressLabel = $this->test->byXpath("//*[@data-ftid='orocrm_sales_lead_form_address_label']");
        $addressLabel->clear();
        $addressLabel->value($value);

        return $this;
    }

    public function getAddressLabel()
    {
        $addressLabel = $this->test->byXpath("//*[@data-ftid='orocrm_sales_lead_form_address_label']");

        return $addressLabel->attribute('value');
    }

    public function setAddressStreet($value)
    {
        $addressStreet = $this->test->byXpath("//*[@data-ftid='orocrm_sales_lead_form_address_street']");
        $addressStreet->clear();
        $addressStreet->value($value);

        return $this;
    }

    public function getAddressStreet()
    {
        $addressStreet = $this->test->byXpath("//*[@data-ftid='orocrm_sales_lead_form_address_street']");

        return $addressStreet->attribute('value');
    }

    public function setAddressCity($value)
    {
        $addressCity = $this->test->byXpath("//*[@data-ftid='orocrm_sales_lead_form_address_city']");
        $addressCity->clear();
        $addressCity->value($value);

        return $this;
    }

    public function getAddressCity()
    {
        $addressCity = $this->test->byXpath("//*[@data-ftid='orocrm_sales_lead_form_address_city']");

        return $addressCity->attribute('value');
    }

    public function setAddressZipCode($value)
    {
        $addressCity = $this->test->byXpath("//*[@data-ftid='orocrm_sales_lead_form_address_postalCode']");
        $addressCity->clear();
        $addressCity->value($value);

        return $this;
    }

    public function getAddressZipCode()
    {
        $addressCity = $this->test->byXpath("//*[@data-ftid='orocrm_sales_lead_form_address_postalCode']");

        return $addressCity->attribute('value');
    }

    public function setAddressCountry($value)
    {
        $country = $this->test->byXpath("//div[starts-with(@id,'s2id_orocrm_sales_lead_form_address_country')]/a");
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

    public function getAddressCountry()
    {
        return $this->test->byXpath("//div[@id = 's2id_orocrm_sales_lead_form_address_country']/a/span")->text();
    }

    public function setAddressRegion($value)
    {
        if ($this->test->byXPath("//input[starts-with(@id, 'orocrm_sales_lead_form_address_region_text')]")
            ->displayed()) {
            $state = $this->test->byXPath("//input[starts-with(@id, 'orocrm_sales_lead_form_address_region_text')]");
            $state->clear();
            $state->value($value);
        } else {
            $state = $this->test->byXpath("//div[starts-with(@id,'s2id_orocrm_sales_lead_form_address_region')]/a");
            $state->click();
            $this->waitForAjax();
            $this->test->byXpath("//div[@id='select2-drop']/div/input")->value($value);
            $this->waitForAjax();
            $this->assertElementPresent(
                "//div[@id='select2-drop']//div[normalize-space(.) = '{$value}']",
                "Country's autocomplete doesn't return search value"
            );
            $this->test->byXpath("//div[@id='select2-drop']//div[normalize-space(.) = '{$value}']")->click();
        }

        return $this;
    }

    public function getAddressRegion()
    {
        return $this->test->byXpath("//div[@id = 's2id_orocrm_sales_lead_form_address_region']/a/span")->text();
    }

    public function setAddress($data)
    {
        foreach ($data as $key => $value) {
            $method = 'setAddress' . ucfirst($key);
            $this->$method($value);
        }

        return $this;
    }

    public function getAddress(&$values)
    {
        $values['label'] = $this->getAddressLabel();
        $values['street'] = $this->getAddressStreet();
        $values['city'] = $this->getAddressCity();
        $values['zipCode'] = $this->getAddressZipCode();
        $values['country'] = $this->getAddressCountry();
        $values['region'] = $this->getAddressRegion();

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
