<?php

namespace OroCRM\Bundle\CallBundle\Tests\Selenium;

use Oro\Bundle\TestFrameworkBundle\Test\Selenium2TestCase;
use Oro\Bundle\UserBundle\Tests\Selenium\Pages\Roles;
use Oro\Bundle\UserBundle\Tests\Selenium\Pages\Users;
use OroCRM\Bundle\CallBundle\Tests\Selenium\Pages\Calls;

class AclCallTest extends Selenium2TestCase
{
    public function testCreateRole()
    {
        $randomPrefix = mt_rand();
        $login = $this->login();
        /** @var Roles $login */
        $login->openRoles('Oro\Bundle\UserBundle')
            ->add()
            ->setLabel('Label_' . $randomPrefix)
            ->setEntity('Call', array('Create', 'Edit', 'Delete', 'View', 'Assign'), 'System')
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
    public function testCreateCall()
    {
        $callSubject = 'Call_'.mt_rand(500, 999);
        $phoneNumber = mt_rand(100, 999).'-'.mt_rand(100, 999).'-'.mt_rand(1000, 9999);

        $login = $this->login();
        /** @var Calls $login */
        $login->openCalls('OroCRM\Bundle\CallBundle')
            ->add()
            ->assertTitle('Log Call - Calls - Activities')
            ->setCallSubject($callSubject)
            ->setPhoneNumber($phoneNumber)
            ->save()
            ->assertMessage('Call saved')
            ->assertTitle($callSubject.' - Calls - Activities')
            ->close();

        return $callSubject;
    }

    /**
     * @depends testCreateUser
     * @depends testCreateRole
     * @depends testCreateCall
     *
     * @param $aclCase
     * @param $username
     * @param $role
     * @param $callSubject
     *
     * @dataProvider columnTitle
     */
    public function testCallAcl($aclCase, $username, $role, $callSubject)
    {
        $roleName = 'Label_' . $role;
        $login = $this->login();
        switch ($aclCase) {
            case 'delete':
                $this->deleteAcl($login, $roleName, $username, $callSubject);
                break;
            case 'update':
                $this->updateAcl($login, $roleName, $username, $callSubject);
                break;
            case 'create':
                $this->createAcl($login, $roleName, $username);
                break;
            case 'view':
                $this->viewAcl($login, $username, $roleName);
                break;
        }
    }

    public function deleteAcl($login, $roleName, $username, $callSubject)
    {
        /** @var Roles $login */
        $login = $login->openRoles('Oro\Bundle\UserBundle')
            ->filterBy('Label', $roleName)
            ->open(array($roleName))
            ->setEntity('Call', array('Delete'), 'None')
            ->save()
            ->logout()
            ->setUsername($username)
            ->setPassword('123123q')
            ->submit();
        /** @var Calls $login */
        $login->openCalls('OroCRM\Bundle\CallBundle')
            ->filterBy('Subject', $callSubject)
            ->assertNoActionMenu('Delete');
    }

    public function updateAcl($login, $roleName, $username, $callSubject)
    {
        /** @var Roles $login */
        $login->openRoles('Oro\Bundle\UserBundle')
            ->filterBy('Label', $roleName)
            ->open(array($roleName))
            ->setEntity('Call', array('Edit'), 'None')
            ->save()
            ->logout()
            ->setUsername($username)
            ->setPassword('123123q')
            ->submit();
        /** @var Calls $login */
        $login->openCalls('OroCRM\Bundle\CallBundle')
            ->filterBy('Subject', $callSubject)
            ->assertNoActionMenu('Update');
    }

    public function createAcl($login, $roleName, $username)
    {
        /** @var Roles $login */
        $login->openRoles('Oro\Bundle\UserBundle')
            ->filterBy('Label', $roleName)
            ->open(array($roleName))
            ->setEntity('Call', array('Create'), 'None')
            ->save()
            ->logout()
            ->setUsername($username)
            ->setPassword('123123q')
            ->submit();
        /** @var Calls $login */
        $login->openCalls('OroCRM\Bundle\CallBundle')
            ->assertElementNotPresent(
                "//div[@class='pull-right title-buttons-container']//a[contains(., 'Log call')]"
            );
    }

    public function viewAcl($login, $username, $roleName)
    {
        /** @var Roles $login */
        $login->openRoles('Oro\Bundle\UserBundle')
            ->filterBy('Label', $roleName)
            ->open(array($roleName))
            ->setEntity('Call', array('View'), 'None')
            ->save()
            ->logout()
            ->setUsername($username)
            ->setPassword('123123q')
            ->submit();
        /** @var Calls $login */
        $login->openCalls('OroCRM\Bundle\CallBundle')
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
