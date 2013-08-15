<?php

namespace OroCRM\Bundle\TestFrameworkBundle\Tests\Selenium\Contacts;

use Oro\Bundle\TestFrameworkBundle\Pages\Objects\Login;

class CreateContactTest extends \PHPUnit_Extensions_Selenium2TestCase
{
    protected $coverageScriptUrl = PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_TESTS_URL_COVERAGE;

    protected $addressPrimary = array(
                    'types' => array('billing'),
                    'primary' => true,
                    'firstName' => 'Address First Name',
                    'lastName' => 'Address Last Name',
                    'street' => 'Address Street',
                    'city' => 'Address City',
                    'postalCode' => '10001',
                    'country' => 'United States',
                    'state' => 'New York'
    );

    protected $addressSecondary = array(
            'types' => array('shipping'),
            'primary' => false,
            'firstName' => 'Address1 First Name',
            'lastName' => 'Address1 Last Name',
            'street' => 'Address1 Street',
            'city' => 'Address1 City',
            'postalCode' => '10001',
            'country' => 'United States',
            'state' => 'New York'
    );

    protected function setUp()
    {
        $this->setHost(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_HOST);
        $this->setPort(intval(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_PORT));
        $this->setBrowser(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM2_BROWSER);
        $this->setBrowserUrl(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_TESTS_URL);
    }

    protected function tearDown()
    {
        $this->cookie()->clear();
    }

    /**
     * @return string
     */
    public function testCreateContact()
    {
        $contactname = 'Contact_'.mt_rand();
        $addressPrimary = array();
        $addressSecondary = array();

        $login = new Login($this);
        $login->setUsername(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_LOGIN)
            ->setPassword(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_PASS)
            ->submit()
            ->openContacts()
            ->add()
            ->setFirstName($contactname . '_first')
            ->setLastName($contactname . '_last')
            ->setEmail($contactname . '@mail.com')
            ->setAddress($this->addressPrimary)
            ->setAddress($this->addressSecondary, 1)
            ->save()
            ->assertTitle('Contacts - Customers')
            ->assertMessage('Contact successfully saved')
            ->close()
            ->filterBy('Email', $contactname . '@mail.com')
            ->open(array($contactname))
            ->assertTitle($contactname . '_first ' . $contactname . '_last' . ' - Contacts - Customers')
            ->edit()
            ->getAddress($addressPrimary)
            ->getAddress($addressSecondary, 1);

        $this->assertEquals($this->addressPrimary, $addressPrimary);
        $this->assertEquals($this->addressSecondary, $addressSecondary);

        return $contactname;
    }

    /**
     * @depends testCreateContact
     * @param $contactname
     */
    public function testContactAutocmplete($contactname)
    {
        $login = new Login($this);
        $login->setUsername(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_LOGIN)
            ->setPassword(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_PASS)
            ->submit()
            ->openContacts()
            ->add()
            ->setFirstName($contactname . '_first_autocomplete')
            ->setLastName($contactname . '_last_autocomplete')
            ->setAssignedTo('admin')
            ->setReportsTo($contactname)
            ->setAddressStreet('Street')
            ->setAddressCity('City')
            ->setAddressPostalCode('Zip Code 000')
            ->setAddressCountry('Kazak')
            ->setAddressState('Aqm')
            ->save()
            ->assertTitle('Contacts - Customers')
            ->assertMessage('Contact successfully saved')
            ->close();
    }

    /**
     * @depends testCreateContact
     * @param $contactname
     * @return string
     */
    public function testUpdateContact($contactname)
    {
        $newContactname = 'Update_' . $contactname;

        $login = new Login($this);
        $login->setUsername(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_LOGIN)
            ->setPassword(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_PASS)
            ->submit()
            ->openContacts()
            ->filterBy('Email', $contactname . '@mail.com')
            ->open(array($contactname))
            ->assertTitle($contactname . '_first ' . $contactname . '_last' . ' - Contacts - Customers')
            ->edit()
            ->setFirstName($newContactname . '_first')
            ->save()
            ->assertTitle('Contacts - Customers')
            ->assertMessage('Contact successfully saved')
            ->close();

        return $newContactname;
    }

    /**
     * @depends testUpdateContact
     * @param $contactname
     */
    public function testDeleteContact($contactname)
    {
        $this->markTestSkipped('BAP-726');
        $login = new Login($this);
        $login->setUsername(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_LOGIN)
            ->setPassword(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_PASS)
            ->submit()
            ->openContacts()
            ->filterBy('Email', $contactname . '@mail.com')
            ->open(array($contactname))
            ->delete()
            ->assertTitle('Contacts - Customers')
            ->assertMessage('Item was deleted');

        $login->openUsers()->filterBy('Email', $contactname . '@mail.com')->assertNoDataMessage('No Contacts were found to match your search');
    }
}
