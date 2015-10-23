<?php

namespace OroCRM\Bundle\ContactBundle\Tests\Selenium\Contacts;

use Oro\Bundle\EmailBundle\Tests\Selenium\Pages\Email;
use Oro\Bundle\TestFrameworkBundle\Test\Selenium2TestCase;
use OroCRM\Bundle\CallBundle\Tests\Selenium\Pages\Call;
use OroCRM\Bundle\ContactBundle\Tests\Selenium\Pages\Contacts;

class ContactContactedCountTest extends Selenium2TestCase
{
    const EMAIL = 'mailbox3@example.com';

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
            ->setEmail(self::EMAIL)
            ->save()
            ->checkContactStatus(['Not contacted yet']);

        return $contactName;
    }

    /**
     * @depends testCreateContact
     * @param $contactName
     */
    public function testCheckLogCallCount($contactName)
    {
        $callSubject = 'Call_'.mt_rand();
        $phoneNumber = mt_rand(100, 999).'-'.mt_rand(100, 999).'-'.mt_rand(1000, 9999);

        $login = $this->login();
        /** @var Contacts $login */
        $login->openContacts('OroCRM\Bundle\ContactBundle')
            ->filterBy('Email', self::EMAIL)
            ->open([$contactName])
            ->assertTitle($contactName . '_first ' . $contactName . '_last' . ' - Contacts - Customers')
            ->runActionInGroup('Log call')
            /** @var Call $login */
            ->openCall('OroCRM\Bundle\CallBundle')
            ->setCallSubject($callSubject)
            ->setPhoneNumber($phoneNumber)
            ->logCall()
            ->assertMessage('Call saved')
            ->verifyActivity('Call', $callSubject);
        /** @var Contacts $login */
        $login->openContacts('OroCRM\Bundle\ContactBundle')
            ->filterBy('Email', self::EMAIL)
            ->open([$contactName])
            ->checkContactStatus(['Times Contacted: 1', 'Last Contacted']);

        return $contactName;
    }

    /**
     * @depends testCheckLogCallActivityCount
     * @param $contactName
     */
    public function testCheckSendEmailCount($contactName)
    {
        $login = $this->login();
        /** @var Contacts $login */
        $login->openContacts('OroCRM\Bundle\ContactBundle')
            ->filterBy('Email', self::EMAIL)
            ->open([$contactName])
            ->runActionInGroup('Send email');
        /** @var Email $login */
        $login->openEmail('Oro\Bundle\EmailBundle')
            ->setSubject('Test contacted count')
            ->send();
        /** @var Contacts $login */
        $login->openContacts('OroCRM\Bundle\ContactBundle')
            ->filterBy('Email', self::EMAIL)
            ->open([$contactName])
            ->checkContactStatus(['Times Contacted: 2']);
    }

    public function testCloseWidgetWindow()
    {
        $login = $this->login();
        $login->closeWidgetWindow();
    }
}
