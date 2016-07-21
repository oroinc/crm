<?php

namespace OroCRM\Bundle\TestFrameworkBundle\Tests\Selenium\Acl;

use Oro\Bundle\DataAuditBundle\Tests\Selenium\Pages\DataAudit;
use Oro\Bundle\NavigationBundle\Tests\Selenium\Pages\Navigation;
use Oro\Bundle\SecurityBundle\Tests\Selenium\Pages\AclCheck;
use Oro\Bundle\TestFrameworkBundle\Test\Selenium2TestCase;
use Oro\Bundle\UserBundle\Tests\Selenium\Pages\Groups;
use Oro\Bundle\UserBundle\Tests\Selenium\Pages\Login;
use Oro\Bundle\UserBundle\Tests\Selenium\Pages\Roles;
use Oro\Bundle\UserBundle\Tests\Selenium\Pages\User;
use Oro\Bundle\UserBundle\Tests\Selenium\Pages\Users;
use OroCRM\Bundle\AccountBundle\Tests\Selenium\Pages\Accounts;
use OroCRM\Bundle\ContactBundle\Tests\Selenium\Pages\ContactGroups;
use OroCRM\Bundle\ContactBundle\Tests\Selenium\Pages\Contacts;

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
        /** @var Roles $login */
        $login->openRoles('Oro\Bundle\UserBundle')
            ->add()
            ->setLabel($this->newRole['LABEL'] . $randomPrefix)
            ->setEntity('Role', array('View'), 'System')
            ->setEntity('Contact Group', array('Create', 'Edit', 'Delete', 'View', 'Assign'), 'System')
            ->setEntity('Contact', array('Create', 'Edit', 'Delete', 'View', 'Assign'), 'System')
            ->setEntity('Account', array('Create', 'Edit', 'Delete', 'View', 'Assign'), 'System')
            ->setCapability(['Update User Profile'], 'System')
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
        /** @var Users $login */
        $login->openUsers('Oro\Bundle\UserBundle')
            ->add()
            ->assertTitle('Create User - Users - User Management - System')
            ->setUsername($username)
            ->setOwner('Main')
            ->enable()
            ->setFirstPassword('123123q')
            ->setSecondPassword('123123q')
            ->setFirstName('First_'.$username)
            ->setLastName('Last_'.$username)
            ->setEmail($username.'@mail.com')
            ->setRoles(array($roleName))
            ->setBusinessUnitOrganization(array('OroCRM'))
            ->setBusinessUnit()
            ->uncheckInviteUser()
            ->save()
            ->assertMessage('User saved')
            ->toGrid()
            ->close()
            ->assertTitle('All - Users - User Management - System');

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
        /** @var Navigation $login */
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
            ->submit();
        /** @var Users $login */
        $login->openUsers('Oro\Bundle\UserBundle')
            ->assertTitle('403 - Forbidden');
        /** @var Groups $login */
        $login->openGroups('Oro\Bundle\UserBundle')
            ->assertTitle('403 - Forbidden');
        /** @var DataAudit $login */
        $login->openDataAudit('Oro\Bundle\DataAuditBundle')
            ->assertTitle('403 - Forbidden');
    }

    /**
     * @param $roleName
     * @depends testCreateRole
     */
    public function testEditRole($roleName)
    {
        $login = $this->login();
        /** @var Roles $login */
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
            ->submit();
        /** @var Accounts $login */
        $login->openAccounts('OroCRM\Bundle\AccountBundle')
            ->assertTitle('Accounts - Customers')
            ->assertElementNotPresent("//div[@class='container-fluid']//a[@title='Create Account']");
        /** @var Contacts $login */
        $login->openContacts('OroCRM\Bundle\ContactBundle')
            ->assertTitle('Contacts - Customers')
            ->assertElementNotPresent("//div[@class='container-fluid']//a[@title='Create Contact']");
        /** @var ContactGroups $login */
        $login->openContactGroups('OroCRM\Bundle\ContactBundle')
            ->assertTitle('Contact Groups - System')
            ->assertElementNotPresent("//div[@class='container-fluid']//a[@title='Create Contact Group']");
        /** @var AclCheck $login */
        $login->openAclCheck('Oro\Bundle\SecurityBundle')
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
        /** @var Login $login */
        $login = $login->setUsername($username)
            ->setPassword('123123q')
            ->submit();

        /** @var User $login */
        $login->openUser('Oro\Bundle\UserBundle')
            ->viewInfo($username)
            ->checkRoleSelector();
    }
}
