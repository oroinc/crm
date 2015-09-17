<?php

namespace OroCRM\Bundle\TaskBundle\Tests\Selenium;

use Oro\Bundle\TestFrameworkBundle\Test\Selenium2TestCase;
use Oro\Bundle\UserBundle\Tests\Selenium\Pages\Roles;
use Oro\Bundle\UserBundle\Tests\Selenium\Pages\Users;
use OroCRM\Bundle\TaskBundle\Tests\Selenium\Pages\Tasks;

class AclTasksTest extends Selenium2TestCase
{
    public function testCreateRole()
    {
        $randomPrefix = mt_rand();
        $login = $this->login();
        /** @var Roles $login */
        $login->openRoles('Oro\Bundle\UserBundle')
            ->add()
            ->setLabel('Label_' . $randomPrefix)
            ->setEntity('Task', array('Create', 'Edit', 'Delete', 'View', 'Assign'), 'System')
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
    public function testCreateTask()
    {
        $subject = 'Tasks_' . mt_rand();

        $login = $this->login();

        // set DueDate = now + 1 day to prevent "Due date must not be in the past" error
        $dueDate = new \DateTime('now');
        $dueDateValue = $dueDate
            ->add(new \DateInterval('P1D'))
            ->format('M j, Y g:i A');

        /** @var Tasks $login */
        $login->openTasks('OroCRM\Bundle\TaskBundle')
            ->add()
            ->setSubject($subject)
            ->setDescription($subject)
            ->setDueDate($dueDateValue)
            ->save()
            // ->assertMessage('Task saved') // comment component using ajax and message could disappear already
            ->assertTitle("{$subject} - Tasks - Activities")
            ->toGrid()
            ->assertTitle('All - Tasks - Activities');

        return $subject;
    }


    /**
     * @depends testCreateUser
     * @depends testCreateRole
     * @depends testCreateTask
     *
     * @param $aclCase
     * @param $username
     * @param $role
     * @param $taskSubject
     *
     * @dataProvider columnTitle
     */
    public function testTaskAcl($aclCase, $username, $role, $taskSubject)
    {
        $roleName = 'Label_' . $role;
        $login = $this->login();
        switch ($aclCase) {
            case 'delete':
                $this->deleteAcl($login, $roleName, $username, $taskSubject);
                break;
            case 'update':
                $this->updateAcl($login, $roleName, $username, $taskSubject);
                break;
            case 'create':
                $this->createAcl($login, $roleName, $username);
                break;
            case 'view':
                $this->viewAcl($login, $username, $roleName, $taskSubject);
                break;
        }
    }

    public function deleteAcl($login, $roleName, $username, $taskSubject)
    {
        /** @var Roles $login */
        $login->openRoles('Oro\Bundle\UserBundle')
            ->filterBy('Label', $roleName)
            ->open(array($roleName))
            ->setEntity('Task', array('Delete'), 'None')
            ->save()
            ->logout()
            ->setUsername($username)
            ->setPassword('123123q')
            ->submit();
        /** @var Tasks $login */
        $login->openTasks('OroCRM\Bundle\TaskBundle')
            ->filterBy('Subject', $taskSubject)
            ->assertNoActionMenu('Delete')
            ->open(array($taskSubject))
            ->assertElementNotPresent("//div[@class='pull-left btn-group icons-holder']/a[@title='Delete Task']");
    }

    public function updateAcl($login, $roleName, $username, $taskSubject)
    {
        /** @var Roles $login */
        $login->openRoles('Oro\Bundle\UserBundle')
            ->filterBy('Label', $roleName)
            ->open(array($roleName))
            ->setEntity('Task', array('Edit'), 'None')
            ->save()
            ->logout()
            ->setUsername($username)
            ->setPassword('123123q')
            ->submit();
        /** @var Tasks $login */
        $login->openTasks('OroCRM\Bundle\TaskBundle')
            ->filterBy('Subject', $taskSubject)
            ->assertNoActionMenu('Update')
            ->open(array($taskSubject))
            ->assertElementNotPresent("//div[@class='pull-left btn-group icons-holder']/a[@title='Edit Task']");
    }

    public function createAcl($login, $roleName, $username)
    {
        /** @var Roles $login */
        $login->openRoles('Oro\Bundle\UserBundle')
            ->filterBy('Label', $roleName)
            ->open(array($roleName))
            ->setEntity('Task', array('Create'), 'None')
            ->save()
            ->logout()
            ->setUsername($username)
            ->setPassword('123123q')
            ->submit();
        /** @var Tasks $login */
        $login->openTasks('OroCRM\Bundle\TaskBundle')
            ->assertElementNotPresent(
                "//div[@class='pull-right title-buttons-container']//a[contains(., 'Create Task')]"
            );
    }

    public function viewAcl($login, $username, $roleName)
    {
        /** @var Roles $login */
        $login->openRoles('Oro\Bundle\UserBundle')
            ->filterBy('Label', $roleName)
            ->open(array($roleName))
            ->setEntity('Task', array('View'), 'None')
            ->save()
            ->logout()
            ->setUsername($username)
            ->setPassword('123123q')
            ->submit();
        /** @var Tasks $login */
        $login->openTasks('OroCRM\Bundle\TaskBundle')
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
