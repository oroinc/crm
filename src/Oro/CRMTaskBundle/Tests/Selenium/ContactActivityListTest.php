<?php

namespace Oro\CRMTaskBundle\Tests\Selenium;

use Oro\Bundle\TestFrameworkBundle\Test\Selenium2TestCase;
use OroCRM\Bundle\TaskBundle\Tests\Selenium\Pages\Task;
use OroCRM\Bundle\ContactBundle\Tests\Selenium\Pages\Contacts;

class ContactTaskActivityListTest extends Selenium2TestCase
{
    /**
     * @return string
     */
    public function testCreateContact()
    {
        $contactName = 'Contact_'.mt_rand();

        $login = $this->login();
        /** @var Contacts $login */
        $login->openContacts('OroCRM\Bundle\ContactBundle')
            ->assertTitle('All - Contacts - Customers')
            ->add()
            ->assertTitle('Create Contact - Contacts - Customers')
            ->setFirstName($contactName . '_first')
            ->setLastName($contactName . '_last')
            ->setOwner('admin')
            ->setEmail($contactName . '@mail.com')
            ->save();

        return $contactName;
    }

    /**
     * @depends testCreateContact
     * @param $contactName
     */
    public function testAddTaskActivity($contactName)
    {
        $subject = 'Tasks_' . mt_rand();

        $login = $this->login();
        /** @var Contacts $login */
        $task = $login->openContacts('OroCRM\Bundle\ContactBundle')
            ->filterBy('Email', $contactName . '@mail.com')
            ->open([$contactName])
            ->runActionInGroup('Add task')
            ->openTask('OroCRM\Bundle\TaskBundle');

        /** @var Task $task */
        $task
            ->setSubject($subject)
            ->setDescription($subject)
            ->createTask()
            ->assertMessage('Task created successfully')
            ->verifyActivity('Task', $subject);
    }

    public function testCloseWidgetWindow()
    {
        $login = $this->login();
        $login->closeWidgetWindow();
    }
}
