<?php

namespace OroCRM\Bundle\TestsBundle\Tests\Selenium;

use Oro\Bundle\TestFrameworkBundle\Test\Selenium2TestCase;
use Oro\Bundle\UserBundle\Tests\Selenium\Pages\Login;

/**
 * Class AclTest
 *
 * @package OroCRM\Bundle\TestsBundle\Tests\Selenium
 */
class AclTest extends Selenium2TestCase
{
    protected $newRole = array('ROLE_NAME' => 'NEW_ROLE_', 'LABEL' => 'Role_label_');

    public function testCreateRole()
    {
        $randomPrefix = mt_rand();
        $login = $this->login();
        $login->openRoles('Oro\Bundle\UserBundle')
            ->add()
            ->setLabel($this->newRole['LABEL'] . $randomPrefix)
            ->setOwner('Main')
            ->setEntity('Contact Group', array('Create', 'Edit', 'Delete', 'View', 'Assign'), 'System')
            ->setEntity('Contact', array('Create', 'Edit', 'Delete', 'View', 'Assign'), 'System')
            ->setEntity('Account', array('Create', 'Edit', 'Delete', 'View', 'Assign'), 'System')
            ->save()
            ->assertMessage('Role saved');

        return ($this->newRole['LABEL'] . $randomPrefix);
    }

    /**
     * @param $roleName
     * @depends testCreateRole
     * @return string
     */
    public function testCreateUser($roleName)
    {
        $username = 'User_'.mt_rand();

        $login = $this->login();
        $login->openUsers('Oro\Bundle\UserBundle')
            ->add()
            ->assertTitle('Create User - Users - Users Management - System')
            ->setUsername($username)
            ->setOwner('Main')
            ->enable()
            ->setFirstpassword('123123q')
            ->setSecondpassword('123123q')
            ->setFirstName('First_'.$username)
            ->setLastName('Last_'.$username)
            ->setEmail($username.'@mail.com')
            ->setRoles(array($roleName))
            ->uncheckInviteUser()
            ->save()
            ->assertMessage('User saved')
            ->toGrid()
            ->close()
            ->assertTitle('Users - Users Management - System');

        return $username;
    }

    /**
     * @param $username
     * @depends testCreateUser
     */
    public function testUserAccess($username)
    {
        $login = new Login($this);
        $login->setUsername($username)
            ->setPassword('123123q')
            ->submit();
        $login->assertElementNotPresent(
            "//div[@id='main-menu']//span[normalize-space(.) = 'Configuration']",
            'Element present so ACL for Users do not work'
        );
        $login->assertElementNotPresent("//div[@id='search-div']", 'Element present so ACL for Search do not work');
        $login->openNavigation('Oro\Bundle\NavigationBundle')->openMyMenu();
    }

    /**
     * @param $username
     * @depends testCreateUser
     */
    public function testUserAccessDirectUrl($username)
    {
        $login = new Login($this);
        $login->setUsername($username)
            ->setPassword('123123q')
            ->submit()
            ->openUsers('Oro\Bundle\UserBundle')
            ->assertTitle('403 - Forbidden')
            ->openRoles('Oro\Bundle\UserBundle')
            ->assertTitle('403 - Forbidden')
            ->openGroups('Oro\Bundle\UserBundle')
            ->assertTitle('403 - Forbidden')
            ->openDataAudit('Oro\Bundle\DataAuditBundle')
            ->assertTitle('403 - Forbidden');
    }

    /**
     * @param $roleName
     * @depends testCreateRole
     */
    public function testEditRole($roleName)
    {
        $login = $this->login();
        $login->openRoles('Oro\Bundle\UserBundle')
            ->filterBy('Label', $roleName)
            ->open(array($roleName))
            ->setEntity('Account', array('Create', 'Edit', 'Delete', 'Assign'), 'None')
            ->setEntity('Contact Group', array('Create', 'Edit', 'Delete', 'Assign'), 'None')
            ->setEntity('Contact', array('Create', 'Edit', 'Delete', 'Assign'), 'None')
            //->setEntity('User', array('View', 'Edit'))
            ->save()
            ->assertMessage('Role saved');
    }

    /**
     * @param $username
     * @depends testCreateUser
     * @depends testEditRole
     */
    public function testViewAccountsContacts($username)
    {
        $login = new Login($this);
        $login->setUsername($username)
            ->setPassword('123123q')
            ->submit()
            ->openAccounts('OroCRM\Bundle\AccountBundle')
            ->assertTitle('Accounts - Customers')
            ->assertElementNotPresent("//div[@class='container-fluid']//a[@title='Create account']")
            ->openContacts('OroCRM\Bundle\ContactBundle')
            ->assertTitle('Contacts - Customers')
            ->assertElementNotPresent("//div[@class='container-fluid']//a[@title='Create contact']")
            ->openContactGroups('OroCRM\Bundle\ContactBundle')
            ->assertTitle('Contact Groups - System')
            ->assertElementNotPresent("//div[@class='container-fluid']//a[@title='Create contact group']")
            ->openAclCheck('Oro\Bundle\SecurityBundle')
            ->assertAcl('account/create')
            ->assertAcl('contact/create')
            ->assertAcl('contact/group/create')
            ->assertAcl('contact/group/create');
    }

    /**
     * @param $username
     * @depends testCreateUser
     * @depends testEditRole
     */
    public function testEditUserProfile($username)
    {
        $login = new Login($this);
        $login->setUsername($username)
            ->setPassword('123123q')
            ->submit()
            ->openUser('Oro\Bundle\UserBundle')
            ->viewInfo($username)
            ->checkRoleSelector();
    }
}
