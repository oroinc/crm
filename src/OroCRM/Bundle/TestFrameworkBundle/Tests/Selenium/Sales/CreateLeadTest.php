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
        $topic = 'Lead_'.mt_rand();

        $login = new Login($this);
        $login->setUsername(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_LOGIN)
            ->setPassword(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_PASS)
            ->submit()
            ->openLeads()
            ->add()
            ->setTopic($topic)
            ->setFirstName($topic . '_first_name')
            ->setLastName($topic . '_last_name')
            ->setJobTitle('Manager')
            ->setPhone('712-566-3002')
            ->setEmail($topic . '@mail.com')
            ->setCompany('Some Company')
            ->setWebsite('http://www.orocrm.com')
            ->setEmployees('100')
            ->setAddress($this->address)
            ->save()
            ->assertMessage('Lead saved')
            ->toGrid()
            ->assertTitle('Leads - Sales');

        return $topic;
    }

    /**
     * @depends testCreateLead
     * @param $topic
     * @return string
     */
    public function testUpdateLead($topic)
    {
        $newTopic = 'Update_' . $topic;

        $login = new Login($this);
        $login->setUsername(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_LOGIN)
            ->setPassword(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_PASS)
            ->submit()
            ->openLeads()
            ->filterBy('Topic', $topic)
            ->open(array($topic))
            ->edit()
            ->assertTitle($topic . ' - Leads - Sales')
            ->setTopic($newTopic)
            ->save()
            ->assertMessage('Lead saved')
            ->toGrid()
            ->assertTitle('Leads - Sales')
            ->close();

        return $newTopic;
    }

    /**
     * @depends testUpdateLead
     * @param $topic
     */
    public function testDeleteAccount($topic)
    {
        $this->markTestSkipped('BAP-726');
        $login = new Login($this);
        $login->setUsername(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_LOGIN)
            ->setPassword(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_PASS)
            ->submit()
            ->openLeads()
            ->filterBy('Topic', $topic)
            ->open(array($topic))
            ->delete()
            ->assertTitle('Leads - Sales')
            ->assertMessage('Item deleted');

        $login->openUsers()->filterBy('Topic', $topic)->assertNoDataMessage('No Leads were found to match your search');
    }
}
