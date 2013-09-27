<?php

namespace OroCRM\Bundle\TestFrameworkBundle\Tests\Selenium\Sales;

use Oro\Bundle\TestFrameworkBundle\Pages\Objects\Accounts;
use Oro\Bundle\TestFrameworkBundle\Pages\Objects\Login;

class CreateLeadTest extends \PHPUnit_Extensions_Selenium2TestCase
{
    protected $coverageScriptUrl = PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_TESTS_URL_COVERAGE;

    protected $address = array(
        'label' => 'Address Label',
        'street' => 'Address Street',
        'city' => 'Address City',
        'zipCode' => '10001',
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
    public function testCreateLead()
    {
        $name = 'Lead_'.mt_rand();

        $login = new Login($this);
        $login->setUsername(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_LOGIN)
            ->setPassword(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_PASS)
            ->submit()
            ->openLeads()
            ->add()
            ->setName($name)
            ->setFirstName($name . '_first_name')
            ->setLastName($name . '_last_name')
            ->setJobTitle('Manager')
            ->setPhone('712-566-3002')
            ->setEmail($name . '@mail.com')
            ->setCompany('Some Company')
            ->setWebsite('http://www.orocrm.com')
            ->setEmployees('100')
            ->setOwner('admin')
            ->setAddress($this->address)
            ->save()
            ->assertMessage('Lead saved')
            ->toGrid()
            ->assertTitle('Leads - Sales');

        return $name;
    }

    /**
     * @depends testCreateLead
     * @param $name
     * @return string
     */
    public function testUpdateLead($name)
    {
        $newName = 'Update_' . $name;

        $login = new Login($this);
        $login->setUsername(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_LOGIN)
            ->setPassword(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_PASS)
            ->submit()
            ->openLeads()
            ->filterBy('Name', $name)
            ->open(array($name))
            ->edit()
            ->assertTitle($name . ' - Edit - Leads - Sales')
            ->setName($newName)
            ->save()
            ->assertMessage('Lead saved')
            ->toGrid()
            ->assertTitle('Leads - Sales')
            ->close();

        return $newName;
    }

    /**
     * @depends testUpdateLead
     * @param $name
     */
    public function testDeleteAccount($name)
    {
        $login = new Login($this);
        $login->setUsername(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_LOGIN)
            ->setPassword(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_PASS)
            ->submit()
            ->openLeads()
            ->filterBy('Name', $name)
            ->open(array($name))
            ->delete()
            ->assertTitle('Leads - Sales')
            ->assertMessage('Item deleted')
            ->assertNoDataMessage('No leads exists');
    }
}
