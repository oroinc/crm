<?php

namespace Oro\Bundle\TestsBundle\Tests\Selenium;

use Oro\Bundle\TestsBundle\Test\ToolsAPI;
use Oro\Bundle\TestsBundle\Pages\BAP\Login;

class GroupsTest extends \PHPUnit_Extensions_Selenium2TestCase
{
    protected $coverageScriptUrl = PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_TESTS_URL_COVERAGE;
    
    protected $newGroup = array('NAME' => 'NEW_GROUP_', 'ROLE' => 'Administrator');

    protected $defaultGroups = array(
        'header' => array('ID' => 'ID', 'NAME' => 'NAME', 'ROLES' => 'ROLES', '' => 'ACTION'),
        '2' => array('2' => '2', 'Administrators' => 'Administrators', '' => 'ROLES', '...' => 'ACTION'),
        '1' => array('1' => '1', 'Managers' => 'Managers', '' => 'ROLES', '...' => 'ACTION')
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

    public function testGroupsGrid()
    {
        $login = new Login($this);
        $login->setUsername(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_LOGIN)
            ->setPassword(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_PASS)
            ->submit()
            ->openGroups()
            ->assertTitle('Groups overview - User Management');
    }

    public function testRolesGridDefaultContent()
    {
        $login = new Login($this);
        $groups = $login->setUsername(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_LOGIN)
            ->setPassword(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_PASS)
            ->submit()
            ->openGroups();
        //get grid content
        $records = $groups->getRows();
        $headers = $groups->getHeaders();

        foreach ($headers as $header) {
            $content = $header->text();
            $this->assertArrayHasKey($content, $this->defaultGroups['header']);
        }

        foreach ($records as $row) {
            $columns = $row->elements($this->using('xpath')->value("td"));
            $id = null;
            foreach ($columns as $column) {
                $content = $column->text();
                if (is_null($id)) {
                    $id = $content;
                }
                $this->assertArrayHasKey($content, $this->defaultGroups[$id]);
            }
        }
    }

    public function testGroupAdd()
    {
        $randomPrefix = ToolsAPI::randomGen(5);

        $login = new Login($this);
        $groups = $login->setUsername(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_LOGIN)
            ->setPassword(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_PASS)
            ->submit()
            ->openGroups()
            ->add()
            ->setName($this->newGroup['NAME'] . $randomPrefix)
            ->setRoles(array($this->newGroup['ROLE']))
            ->save()
            ->assertMessage('Group successfully saved')
            ->close();

        //verify new GROUP
        $groups->refresh();

        $this->assertTrue($groups->entityExists(array('name' => $this->newGroup['NAME'] . $randomPrefix)));

        return $randomPrefix;
    }

    /**
     * @depends testGroupAdd
     * @param $randomPrefix
     */
    public function testGroupDelete($randomPrefix)
    {
        $login = new Login($this);
        $groups = $login->setUsername(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_LOGIN)
            ->setPassword(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_PASS)
            ->submit()
            ->openGroups();
        $groups->deleteEntity(array('name' => $this->newGroup['NAME'] . $randomPrefix));
        $this->assertFalse($groups->entityExists(array('name' => $this->newGroup['NAME'] . $randomPrefix)));
    }
}
