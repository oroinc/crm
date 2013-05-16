<?php

namespace Oro\Bundle\TestsBundle\Tests\Selenium;

use Oro\Bundle\TestsBundle\Test\ToolsAPI;
use Oro\Bundle\TestsBundle\Pages\BAP\Login;

class RolesTest extends \PHPUnit_Extensions_Selenium2TestCase
{
    protected $coverageScriptUrl = PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_TESTS_URL_COVERAGE;

    protected $newRole = array('LABEL' => 'NEW_LABEL_', 'ROLE_NAME' => 'NEW_ROLE_');

    protected $defaultRoles = array(
        'header' => array('ID' => 'ID', 'ROLE' => 'ROLE', 'LABEL' => 'LABEL', '' => 'ACTION'),
        '1' => array('1' => '1', 'ROLE_MANAGER' => 'ROLE_MANAGER', 'Manager' => 'Manager', '...' => 'ACTION'),
        '2' => array('2' => '2', 'ROLE_ADMIN' => 'ROLE_ADMIN', 'Administrator' => 'Administrator', '...' => 'ACTION'),
        '3' => array('3' => '3', 'IS_AUTHENTICATED_ANONYMOUSLY' => 'IS_AUTHENTICATED_ANONYMOUSLY', 'Anonymous' => 'Anonymous', '...' => 'ACTION'),
        '4' => array('4' => '4', 'ROLE_USER' => 'ROLE_USER', 'User' => 'User', '...' => 'ACTION'),
        '5' => array('5' => '5', 'ROLE_SUPER_ADMIN' => 'ROLE_SUPER_ADMIN', 'Super admin' => 'Super admin', '...' => 'ACTION')
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

    public function testRolesGrid()
    {
        $login = new Login($this);
        $login->setUsername(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_LOGIN)
            ->setPassword(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_PASS)
            ->submit()
            ->openRoles()
            ->assertTitle('Roles overview - User management');
    }

    public function testRolesGridDefaultContent()
    {
        $login = new Login($this);
        $groups = $login->setUsername(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_LOGIN)
            ->setPassword(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_PASS)
            ->submit()
            ->openRoles();
        //get grid content
        $records = $groups->getRows();
        $headers = $groups->getHeaders();

        foreach ($headers as $header) {
            $content = $header->text();
            $this->assertArrayHasKey($content, $this->defaultRoles['header']);
        }

        foreach ($records as $row) {
            $columns = $row->elements($this->using('xpath')->value("td"));
            $id = null;
            foreach ($columns as $column) {
                $content = $column->text();
                if (is_null($id)) {
                    $id = $content;
                }
                $this->assertArrayHasKey($content, $this->defaultRoles[$id]);
            }
        }

    }

    public function testRolesAdd()
    {

        $randomPrefix = ToolsAPI::randomGen(5);

        $login = new Login($this);
        $roles = $login->setUsername(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_LOGIN)
            ->setPassword(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_PASS)
            ->submit()
            ->openRoles()
            ->add()
            ->setName($this->newRole['ROLE_NAME'] . $randomPrefix)
            ->setLabel($this->newRole['LABEL'])
            ->save()
            ->assertMessage('Role successfully saved')
            ->close();

        //verify new GROUP
        $roles->refresh();

        $this->assertTrue($roles->entityExists(array('name' => 'ROLE_' . $this->newRole['ROLE_NAME'] . strtoupper($randomPrefix))));

        return $randomPrefix;
    }

    /**
     * @depends testRolesAdd
     * @param $randomPrefix
     */
    public function testRoleDelete($randomPrefix)
    {
        $login = new Login($this);
        $roles = $login->setUsername(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_LOGIN)
            ->setPassword(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_PASS)
            ->submit()
            ->openRoles();
        $roles->deleteEntity(array('name' => 'ROLE_' . $this->newRole['ROLE_NAME'] . strtoupper($randomPrefix)));
        $this->assertFalse($roles->entityExists(array('name' => 'ROLE_' . $this->newRole['ROLE_NAME'] . strtoupper($randomPrefix))));
    }
}
