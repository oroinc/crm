<?php

namespace OroCRM\Bundle\ContactBundle\Tests\Selenium\Contacts;

use Oro\Bundle\EmailBundle\Tests\Selenium\Pages\Email;
use Oro\Bundle\TestFrameworkBundle\Test\Selenium2TestCase;
use OroCRM\Bundle\CallBundle\Tests\Selenium\Pages\Call;
use OroCRM\Bundle\CallBundle\Tests\Selenium\Pages\Calls;
use OroCRM\Bundle\ContactBundle\Tests\Selenium\Pages\Contacts;

class ContactContactedCountTest extends Selenium2TestCase
{
    const EMAIL = 'mailbox3@example.com';

    /**
     * Test check that newly created Contact has no contact counter
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
        /** Log call to Contact */
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
        /** Check that counter increased */
        /** @var Contacts $login */
        $login->openContacts('OroCRM\Bundle\ContactBundle')
            ->filterBy('Email', self::EMAIL)
            ->open([$contactName])
            ->checkContactStatus(['Times Contacted: 1', 'Last Contacted']);
        /** Edit call */
        /** @var Calls $login */
        $login->openCalls('OroCRM\Bundle\CallBundle')
            ->filterBy('Subject', $callSubject)
            ->open(array($callSubject))
            ->assertTitle($callSubject . ' - Calls - Activities')
            ->edit()
            ->assertTitle($callSubject . ' - Edit - Calls - Activities')
            ->setCallSubject($callSubject)
            ->save()
            ->assertMessage('Call saved');
        /** Check that counter does not change */
        /** @var Contacts $login */
        $login->openContacts('OroCRM\Bundle\ContactBundle')
            ->filterBy('Email', self::EMAIL)
            ->open([$contactName])
            ->checkContactStatus(['Times Contacted: 1', 'Last Contacted']);
        /** Delete call */
        /** @var Calls $login */
        $login->openCalls('OroCRM\Bundle\CallBundle')
            ->filterBy('Subject', $callSubject)
            ->delete(array($callSubject))
            ->assertMessage('Item deleted');
        /** Check that counter decreased */
        /** @var Contacts $login */
        $login->openContacts('OroCRM\Bundle\ContactBundle')
            ->filterBy('Email', self::EMAIL)
            ->open([$contactName])
            ->checkContactStatus(['Not contacted yet']);

        return $contactName;
    }

    /**
     * @depends testCheckLogCallCount
     * @param $contactName
     */
    public function testCheckSendEmailCount($contactName)
    {
        $login = $this->login();
        /** Send email */
        /** @var Contacts $login */
        $login->openContacts('OroCRM\Bundle\ContactBundle')
            ->filterBy('Email', self::EMAIL)
            ->open([$contactName])
            ->runActionInGroup('Send email');
        /** @var Email $login */
        $login->openEmail('Oro\Bundle\EmailBundle')
            ->setSubject('Test contacted count')
            ->send();
        /** Check that count increased */
        /** @var Contacts $login */
        $login->openContacts('OroCRM\Bundle\ContactBundle')
            ->filterBy('Email', self::EMAIL)
            ->open([$contactName])
            ->checkContactStatus(['Times Contacted: 1']);
    }

    public function testCloseWidgetWindow()
    {
        $login = $this->login();
        $login->closeWidgetWindow();
    }
}
