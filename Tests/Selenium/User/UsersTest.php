<?php

namespace Oro\Bundle\TestsBundle\Tests\Selenium;

use Oro\Bundle\TestsBundle\Pages\BAP\Login;

class UsersTest extends \PHPUnit_Extensions_Selenium2TestCase
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
    public function testCreateUser()
    {
        $username = 'User_'.mt_rand();

        $login = new Login($this);
        $login->setUsername(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_LOGIN)
            ->setPassword(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_PASS)
            ->submit()
            ->openUsers()
            ->add()
            ->assertTitle('New profile - User Management')
            ->setUsername($username)
            ->setFirstpassword('123123q')
            ->setSecondpassword('123123q')
            ->setFirstname('First_'.$username)
            ->setLastname('Last_'.$username)
            ->setEmail($username.'@mail.com')
            ->setRoles(array('Manager'))
            ->save()
            ->assertMessage('User successfully saved')
            ->close()
            ->assertTitle('Users overview - User Management');

        return $username;
    }

    /**
     * @depends testCreateUser
     * @param $username
     * @return string
     */
    public function testUpdateUser($username)
    {
        $newUsername = 'Update_' . $username;

        $login = new Login($this);
        $login->setUsername(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_LOGIN)
            ->setPassword(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_PASS)
            ->submit()
            ->openUsers()
            ->filterBy('Username', $username)
            ->open(array($username))
            ->edit()
            ->assertTitle('Last_' . $username . ', First_' . $username . ' - Edit profile - User Management')
            ->setUsername($newUsername)
            ->setFirstname('First_' . $newUsername)
            ->setLastname('Last_' . $newUsername)
            ->save()
            ->assertTitle('Last_' . $newUsername . ', First_' . $newUsername. ' - View profile - User Management')
            ->assertMessage('User successfully saved')
            ->close();

        return $newUsername;
    }

    /**
     * @depends testUpdateUser
     * @param $username
     */
    public function testDeleteUser($username)
    {
        $this->markTestSkipped('Bug BAP-726');
        $login = new Login($this);
        $login->setUsername(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_LOGIN)
            ->setPassword(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_PASS)
            ->submit()
            ->openUsers()
            ->filterBy('Username', $username)
            ->open(array($username))
            ->delete()
            ->assertTitle('Users overview - User Management')
            ->assertMessage('User successfully removed');

        $login->openUsers()->filterBy('Username', $username)->assertNoDataMessage('No users were found to match your search');
    }
}
