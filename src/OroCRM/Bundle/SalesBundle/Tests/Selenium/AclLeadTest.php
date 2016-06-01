<?php

namespace OroCRM\Bundle\SalesBundle\Tests\Selenium\Sales;

use Oro\Bundle\TestFrameworkBundle\Test\Selenium2TestCase;
use Oro\Bundle\UserBundle\Tests\Selenium\Pages\Login;
use Oro\Bundle\UserBundle\Tests\Selenium\Pages\Roles;
use Oro\Bundle\UserBundle\Tests\Selenium\Pages\Users;
use OroCRM\Bundle\ChannelBundle\Tests\Selenium\Pages\Channels;
use OroCRM\Bundle\SalesBundle\Tests\Selenium\Pages\Leads;

class AclLeadTest extends Selenium2TestCase
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
            ->setEntity('Lead', array('Create', 'Edit', 'Delete', 'View', 'Assign'), 'System')
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
    public function testCreateLead()
    {
        $name = 'Lead_'.mt_rand();

        $login = $this->login();
        $channelName = $this->createChannel($login);
        /** @var Leads $login */
        $login->openLeads('OroCRM\Bundle\SalesBundle')
            ->add()
            ->setName($name)
            ->setChannel($channelName)
            ->setFirstName($name . '_first_name')
            ->setLastName($name . '_last_name')
            ->assertTitle('Create Lead - Leads - Sales')
            ->save()
            ->assertMessage('Lead saved')
            ->toGrid()
            ->assertTitle('All - Leads - Sales');

        return $name;
    }

    /**
     * @param Login $login
     * @return string
     */
    public function createChannel(Login $login)
    {
        $channelName = 'Channel_' . mt_rand();
        /** @var Channels $login */
        $login->openChannels('OroCRM\Bundle\ChannelBundle')
            ->assertTitle('All - Channels - System')
            ->add()
            ->assertTitle('Create Channel - Channels - System')
            ->setType('Sales')
            ->setName($channelName)
            ->setStatus('Active')
            ->save()
            ->assertMessage('Channel saved');

        return $channelName;
    }

    /**
     * @depends testCreateUser
     * @depends testCreateRole
     * @depends testCreateLead
     *
     * @param $aclCase
     * @param $username
     * @param $role
     * @param $leadName
     *
     * @dataProvider columnTitle
     */
    public function testLeadAcl($aclCase, $username, $role, $leadName)
    {
        $roleName = 'Label_' . $role;
        $login = $this->login();
        switch ($aclCase) {
            case 'delete':
                $this->deleteAcl($login, $roleName, $username, $leadName);
                break;
            case 'update':
                $this->updateAcl($login, $roleName, $username, $leadName);
                break;
            case 'create':
                $this->createAcl($login, $roleName, $username);
                break;
            case 'view':
                $this->viewAcl($login, $username, $roleName, $leadName);
                break;
        }
    }

    public function deleteAcl($login, $roleName, $username, $leadName)
    {
        /** @var Roles $login */
        $login->openRoles('Oro\Bundle\UserBundle')
            ->filterBy('Label', $roleName)
            ->open(array($roleName))
            ->setEntity('Lead', array('Delete'), 'None')
            ->save()
            ->logout()
            ->setUsername($username)
            ->setPassword('123123q')
            ->submit();
        /** @var Leads $login */
        $login->openLeads('OroCRM\Bundle\SalesBundle')
            ->filterBy('Lead name', $leadName)
            ->assertNoActionMenu('Delete')
            ->open(array($leadName))
            ->assertElementNotPresent("//div[@class='pull-left btn-group icons-holder']/a[@title='Delete Lead']");
    }

    public function updateAcl($login, $roleName, $username, $leadName)
    {
        /** @var Roles $login */
        $login->openRoles('Oro\Bundle\UserBundle')
            ->filterBy('Label', $roleName)
            ->open(array($roleName))
            ->setEntity('Lead', array('Edit'), 'None')
            ->save()
            ->logout()
            ->setUsername($username)
            ->setPassword('123123q')
            ->submit();
        /** @var Leads $login */
        $login->openLeads('OroCRM\Bundle\SalesBundle')
            ->filterBy('Lead name', $leadName)
            ->assertNoActionMenu('Update')
            ->open(array($leadName))
            ->assertElementNotPresent("//div[@class='pull-left btn-group icons-holder']/a[@title='Edit Lead']");
    }

    public function createAcl($login, $roleName, $username)
    {
        /** @var Roles $login */
        $login->openRoles('Oro\Bundle\UserBundle')
            ->filterBy('Label', $roleName)
            ->open(array($roleName))
            ->setEntity('Lead', array('Create'), 'None')
            ->save()
            ->logout()
            ->setUsername($username)
            ->setPassword('123123q')
            ->submit();
        /** @var Leads $login */
        $login->openLeads('OroCRM\Bundle\SalesBundle')
            ->assertElementNotPresent(
                "//div[@class='pull-right title-buttons-container']//a[contains(., 'Create Lead')]"
            );
    }

    public function viewAcl($login, $username, $roleName)
    {
        /** @var Roles $login */
        $login->openRoles('Oro\Bundle\UserBundle')
            ->filterBy('Label', $roleName)
            ->open(array($roleName))
            ->setEntity('Lead', array('View'), 'None')
            ->save()
            ->logout()
            ->setUsername($username)
            ->setPassword('123123q')
            ->submit();
        /** @var Leads $login */
        $login->openLeads('OroCRM\Bundle\SalesBundle')
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
