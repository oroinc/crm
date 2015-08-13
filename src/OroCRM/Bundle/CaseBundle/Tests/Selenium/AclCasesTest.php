<?php

namespace OroCRM\Bundle\CaseBundle\Tests\Selenium;

use Oro\Bundle\TestFrameworkBundle\Test\Selenium2TestCase;
use Oro\Bundle\UserBundle\Tests\Selenium\Pages\Roles;
use Oro\Bundle\UserBundle\Tests\Selenium\Pages\Users;
use OroCRM\Bundle\CaseBundle\Tests\Selenium\Pages\Cases;

class AclCasesTest extends Selenium2TestCase
{
    public function testCreateRole()
    {
        $randomPrefix = mt_rand();
        $login = $this->login();
        /** @var Roles $login */
        $login->openRoles('Oro\Bundle\UserBundle')
            ->add()
            ->setLabel('Label_' . $randomPrefix)
            ->setEntity('Case', array('Create', 'Edit', 'Delete', 'View', 'Assign'), 'System')
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
    public function testCreateCase()
    {
        $subject = 'Case_' . mt_rand();

        $login = $this->login();
        /** @var Cases $login */
        $login->openCases('OroCRM\Bundle\CaseBundle')
            ->add()
            ->assertTitle('Create Case - Cases - Activities')
            ->setSubject($subject)
            ->setDescription($subject)
            ->save()
            ->assertMessage('Case saved')
            ->toGrid()
            ->assertTitle('All - Cases - Activities');

        return $subject;
    }

    /**
     * @depends testCreateUser
     * @depends testCreateRole
     * @depends testCreateCase
     *
     * @param $aclCase
     * @param $username
     * @param $role
     * @param $caseSubject
     *
     * @dataProvider columnTitle
     */
    public function testCaseAcl($aclCase, $username, $role, $caseSubject)
    {
        $roleName = 'Label_' . $role;
        $login = $this->login();
        switch ($aclCase) {
            case 'delete':
                $this->deleteAcl($login, $roleName, $username, $caseSubject);
                break;
            case 'update':
                $this->updateAcl($login, $roleName, $username, $caseSubject);
                break;
            case 'create':
                $this->createAcl($login, $roleName, $username);
                break;
            case 'view':
                $this->viewAcl($login, $username, $roleName, $caseSubject);
                break;
        }
    }

    public function deleteAcl($login, $roleName, $username, $caseSubject)
    {
        /** @var Roles $login */
        $login->openRoles('Oro\Bundle\UserBundle')
            ->filterBy('Label', $roleName)
            ->open(array($roleName))
            ->setEntity('Case', array('Delete'), 'None')
            ->save()
            ->logout()
            ->setUsername($username)
            ->setPassword('123123q')
            ->submit()
            ->openCases('OroCRM\Bundle\CaseBundle')
            ->filterBy('Subject', $caseSubject)
            ->assertNoActionMenu('Delete')
            ->open(array($caseSubject))
            ->assertElementNotPresent("//div[@class='pull-left btn-group icons-holder']/a[@title='Delete Case']");
    }

    public function updateAcl($login, $roleName, $username, $caseSubject)
    {
        /** @var Roles $login */
        $login->openRoles('Oro\Bundle\UserBundle')
            ->filterBy('Label', $roleName)
            ->open(array($roleName))
            ->setEntity('Case', array('Edit'), 'None')
            ->save()
            ->logout()
            ->setUsername($username)
            ->setPassword('123123q')
            ->submit()
            ->openCases('OroCRM\Bundle\CaseBundle')
            ->filterBy('Subject', $caseSubject)
            ->assertNoActionMenu('Update')
            ->open(array($caseSubject))
            ->assertElementNotPresent("//div[@class='pull-left btn-group icons-holder']/a[@title='Edit Case']");
    }

    public function createAcl($login, $roleName, $username)
    {
        /** @var Roles $login */
        $login->openRoles('Oro\Bundle\UserBundle')
            ->filterBy('Label', $roleName)
            ->open(array($roleName))
            ->setEntity('Case', array('Create'), 'None')
            ->save()
            ->logout()
            ->setUsername($username)
            ->setPassword('123123q')
            ->submit()
            ->openCases('OroCRM\Bundle\CaseBundle')
            ->assertElementNotPresent(
                "//div[@class='pull-right title-buttons-container']//a[contains(., 'Create Case')]"
            );
    }

    public function viewAcl($login, $username, $roleName)
    {
        /** @var Roles $login */
        $login->openRoles('Oro\Bundle\UserBundle')
            ->filterBy('Label', $roleName)
            ->open(array($roleName))
            ->setEntity('Case', array('View'), 'None')
            ->save()
            ->logout()
            ->setUsername($username)
            ->setPassword('123123q')
            ->submit()
            ->openCases('OroCRM\Bundle\CaseBundle')
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
