<?php

namespace OroCRM\Bundle\TestFrameworkBundle\Tests\Selenium\Contacts;

use Oro\Bundle\TestFrameworkBundle\Pages\Objects\Login;

class CreateContactTest extends \PHPUnit_Extensions_Selenium2TestCase
{
    protected $coverageScriptUrl = PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_TESTS_URL_COVERAGE;

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

        $login = new Login($this);
        $login->setUsername(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_LOGIN)
            ->setPassword(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_PASS)
            ->submit()
            ->openContacts()
            ->add()
            ->setFirst_name($contactname . '_first')
            ->setLast_name($contactname . '_last')
            ->setEmail($contactname . '@mail.com')
            ->save()
            ->assertTitle('Contacts')
            ->assertMessage('Contact successfully saved');

        return $contactname;
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
            ->edit()
            ->assertTitle($contactname . '_last, ' . $contactname . '_first - Contacts')
            ->setFirst_name($newContactname . '_first')
            ->save()
            ->assertTitle('Contacts')
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
            ->filterBy('Email', $contactname . 'mail.com')
            ->open(array($contactname))
            ->delete()
            ->assertTitle('Contacts')
            ->assertMessage('Item was deleted');

        $login->openUsers()->filterBy('Email', $contactname . 'mail.com')->assertNoDataMessage('No Contacts were found to match your search');
    }
}
