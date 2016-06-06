<?php

namespace OroCRM\Bundle\SalesBundle\Tests\Selenium\Sales;

use Oro\Bundle\TestFrameworkBundle\Test\Selenium2TestCase;
use Oro\Bundle\UserBundle\Tests\Selenium\Pages\Login;
use Oro\Bundle\UserBundle\Tests\Selenium\Pages\Roles;
use Oro\Bundle\UserBundle\Tests\Selenium\Pages\Users;
use OroCRM\Bundle\AccountBundle\Tests\Selenium\Pages\Accounts;
use OroCRM\Bundle\ChannelBundle\Tests\Selenium\Pages\Channels;
use OroCRM\Bundle\SalesBundle\Tests\Selenium\Pages\B2BCustomers;
use OroCRM\Bundle\SalesBundle\Tests\Selenium\Pages\Opportunities;

class AclOpportunityTest extends Selenium2TestCase
{
    /**
     * @return int
     */
    public function testCreateRole()
    {
        $randomPrefix = mt_rand();
        $login = $this->login();
        /** @var Roles $login */
        $login->openRoles('Oro\Bundle\UserBundle')
            ->add()
            ->setLabel('Label_' . $randomPrefix)
            ->setEntity('Opportunity', array('Create', 'Edit', 'Delete', 'View', 'Assign'), 'System')
            ->assertTitle('Create Role - Roles - User Management - System')
            ->save()
            ->assertMessage('Role saved')
            ->assertTitle('All - Roles - User Management - System')
            ->close();

        return ($randomPrefix);
    }

    /**
     * @depends testCreateRole
     * @param $role
     * @return string
     */
    public function testCreateUser($role)
    {
        $username = 'User_'.mt_rand();

        $login = $this->login();
        /** @var Users $login */
        $login->openUsers('Oro\Bundle\UserBundle')
            ->add()
            ->assertTitle('Create User - Users - User Management - System')
            ->setUsername($username)
            ->enable()
            ->setOwner('Main')
            ->setFirstPassword('123123q')
            ->setSecondPassword('123123q')
            ->setFirstName('First_'.$username)
            ->setLastName('Last_'.$username)
            ->setEmail($username.'@mail.com')
            ->setRoles(array('Label_' . $role))
            ->setBusinessUnit()
            ->setOrganization('OroCRM')
            ->uncheckInviteUser()
            ->save()
            ->assertMessage('User saved')
            ->toGrid()
            ->close()
            ->assertTitle('All - Users - User Management - System');

        return $username;
    }

    /**
     * @return string
     */
    public function testCreateOpportunity()
    {
        $login = $this->login();

        $opportunityName = 'Opportunity_'.mt_rand();
        $accountName = $this->createAccount($login);
        $channelName = $this->createChannel($login);
        $customer = $this->createB2BCustomer($login, $accountName);
        /** @var Opportunities $login */
        $login->openOpportunities('OroCRM\Bundle\SalesBundle')
            ->add()
            ->setName($opportunityName)
            ->setChannel($channelName)
            ->setB2BCustomer($customer)
            ->setProbability('50')
            ->seBudget('100')
            ->setCustomerNeed('50')
            ->setProposedSolution('150')
            ->setCloseRevenue('200')
            ->setCloseDate('Sep 26, 2013')
            ->setOwner('admin')
            ->save()
            ->assertMessage('Opportunity saved')
            ->toGrid()
            ->assertTitle('All - Opportunities - Sales');

        return $opportunityName;
    }

    /**
     * @param  Login  $login
     * @return string
     */
    protected function createAccount(Login $login)
    {
        $accountName = 'Account_'.mt_rand();
        /** @var Accounts $login */
        $login->openAccounts('OroCRM\Bundle\AccountBundle')
            ->add()
            ->setName($accountName)
            ->setOwner('admin')
            ->save();

        return $accountName;
    }

    /**
     * @param Login  $login
     * @param string $account
     * @return string
     */
    protected function createB2BCustomer(Login $login, $account)
    {
        $name = 'B2BCustomer_'.mt_rand();
        /** @var B2BCustomers $login */
        $login->openB2BCustomers('OroCRM\Bundle\SalesBundle')
            ->add()
            ->setName($name)
            ->setOwner('admin')
            ->setAccount($account)
            ->save();

        return $name;
    }

    /**
     * @param Login $login
     * @return string
     */
    public function createChannel(Login $login)
    {
        $name = 'Channel_' . mt_rand();
        /** @var Channels $login */
        $login->openChannels('OroCRM\Bundle\ChannelBundle')
            ->assertTitle('All - Channels - System')
            ->add()
            ->assertTitle('Create Channel - Channels - System')
            ->setType('Sales')
            ->setName($name)
            ->setStatus('Active')
            ->save()
            ->assertMessage('Channel saved');

        return $name;
    }

    /**
     * @depends testCreateUser
     * @depends testCreateRole
     * @depends testCreateOpportunity
     *
     * @param $aclCase
     * @param $username
     * @param $role
     * @param $opportunityName
     *
     * @dataProvider columnTitle
     */
    public function testOpportunityAcl($aclCase, $username, $role, $opportunityName)
    {
        $roleName = 'Label_' . $role;
        $login = $this->login();
        switch ($aclCase) {
            case 'delete':
                $this->deleteAcl($login, $roleName, $username, $opportunityName);
                break;
            case 'update':
                $this->updateAcl($login, $roleName, $username, $opportunityName);
                break;
            case 'create':
                $this->createAcl($login, $roleName, $username);
                break;
            case 'view':
                $this->viewAcl($login, $username, $roleName, $opportunityName);
                break;
        }
    }

    public function deleteAcl($login, $roleName, $username, $opportunityName)
    {
        /** @var Roles $login */
        $login = $login->openRoles('Oro\Bundle\UserBundle')
            ->filterBy('Label', $roleName)
            ->open(array($roleName))
            ->setEntity('Opportunity', array('Delete'), 'None')
            ->save()
            ->logout()
            ->setUsername($username)
            ->setPassword('123123q')
            ->submit();
        /** @var Opportunities $login */
        $login->openOpportunities('OroCRM\Bundle\SalesBundle')
            ->filterBy('Opportunity name', $opportunityName)
            ->assertNoActionMenu('Delete')
            ->open(array($opportunityName))
            ->assertElementNotPresent(
                "//div[@class='pull-left btn-group icons-holder']/a[@title='Delete Opportunity']"
            );
    }

    public function updateAcl($login, $roleName, $username, $opportunityName)
    {
        /** @var Roles $login */
        $login = $login->openRoles('Oro\Bundle\UserBundle')
            ->filterBy('Label', $roleName)
            ->open(array($roleName))
            ->setEntity('Opportunity', array('Edit'), 'None')
            ->save()
            ->logout()
            ->setUsername($username)
            ->setPassword('123123q')
            ->submit();
        /** @var Opportunities $login */
        $login->openOpportunities('OroCRM\Bundle\SalesBundle')
            ->filterBy('Opportunity name', $opportunityName)
            ->assertNoActionMenu('Update')
            ->open(array($opportunityName))
            ->assertElementNotPresent("//div[@class='pull-left btn-group icons-holder']/a[@title='Edit Opportunity']");
    }

    public function createAcl($login, $roleName, $username)
    {
        /** @var Roles $login */
        $login = $login->openRoles('Oro\Bundle\UserBundle')
            ->filterBy('Label', $roleName)
            ->open(array($roleName))
            ->setEntity('Opportunity', array('Create'), 'None')
            ->save()
            ->logout()
            ->setUsername($username)
            ->setPassword('123123q')
            ->submit();
        /** @var Opportunities $login */
        $login->openOpportunities('OroCRM\Bundle\SalesBundle')
            ->assertElementNotPresent(
                "//div[@class='pull-right title-buttons-container']//a[contains(., 'Create Opportunity')]"
            );
    }

    public function viewAcl($login, $username, $roleName)
    {
        /** @var Roles $login */
        $login = $login->openRoles('Oro\Bundle\UserBundle')
            ->filterBy('Label', $roleName)
            ->open(array($roleName))
            ->setEntity('Opportunity', array('View'), 'None')
            ->save()
            ->logout()
            ->setUsername($username)
            ->setPassword('123123q')
            ->submit();
        /** @var Opportunities $login */
        $login->openOpportunities('OroCRM\Bundle\SalesBundle')
            ->assertTitle('403 - Forbidden');
    }

    /**
     * Data provider for Tags ACL test
     *
     * @return array
     */
    public function columnTitle()
    {
        return array(
            'delete' => array('delete'),
            'update' => array('update'),
            'create' => array('create'),
            'view' => array('view')
        );
    }
}
