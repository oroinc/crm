<?php

namespace OroCRM\Bundle\ContactBundle\Tests\Selenium\Contacts;

use Oro\Bundle\TestFrameworkBundle\Test\Selenium2TestCase;
use OroCRM\Bundle\CallBundle\Tests\Selenium\Pages\Call;
use OroCRM\Bundle\ContactBundle\Tests\Selenium\Pages\Contacts;
use OroCRM\Bundle\TaskBundle\Tests\Selenium\Pages\Task;

class ContactActivityListTest extends Selenium2TestCase
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
    public function testLogCallActivity($contactName)
    {
        $callSubject = 'Call_'.mt_rand();
        $phoneNumber = mt_rand(100, 999).'-'.mt_rand(100, 999).'-'.mt_rand(1000, 9999);

        $login = $this->login();
        /** @var Contacts $login */
        $call = $login->openContacts('OroCRM\Bundle\ContactBundle')
            ->filterBy('Email', $contactName . '@mail.com')
            ->open([$contactName])
            ->assertTitle($contactName . '_first ' . $contactName . '_last' . ' - Contacts - Customers')
            ->runActionInGroup('Log call')
            ->openCall('OroCRM\Bundle\CallBundle');

        /** @var Call $call */
        $call
            ->setCallSubject($callSubject)
            ->setPhoneNumber($phoneNumber)
            ->logCall()
            ->assertMessage('Call saved')
            ->verifyActivity('Call', $callSubject);
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
