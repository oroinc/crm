<?php

namespace OroCRM\Bundle\ContactUsBundle\Tests\Selenium;

use Oro\Bundle\TestFrameworkBundle\Test\Selenium2TestCase;
use OroCRM\Bundle\ChannelBundle\Tests\Selenium\Pages\Channels;
use OroCRM\Bundle\ContactUsBundle\Tests\Selenium\Pages\ContactRequests;

/**
 * Class CreateContactRequestTest
 *
 * @package OroCRM\Bundle\ContactUsBundle\Tests\Selenium
 */
class CreateContactRequestTest extends Selenium2TestCase
{
    /**
     * @return string
     */
    public function testCreateChannel()
    {
        $login = $this->login();
        /** @var Channels $login */
        $login->openChannels('OroCRM\Bundle\ChannelBundle')
            ->assertTitle('All - Channels - System')
            ->add()
            ->assertTitle('Create Channel - Channels - System')
            ->setType('Custom')
            ->setName('Channel_' . mt_rand())
            ->setStatus('Active')
            ->addEntity('Contact Request')
            ->save()
            ->assertMessage('Channel saved');
    }

    /**
     * @depends testCreateChannel
     * @return string
     */
    public function testCreateContactRequest()
    {
        $firstName = 'First name_' . mt_rand(10, 99);
        $lastName  = 'Last name_' . mt_rand(10, 99);
        $email     = 'Email_' . mt_rand(10, 99) . '@mail.com';

        $login = $this->login();
        /** @var ContactRequests $login */
        $login->openContactRequests('OroCRM\Bundle\ContactUsBundle')
            ->assertTitle('All - Contact Requests - Activities')
            ->add()
            ->assertTitle('Create contact request - Contact Requests - Activities')
            ->setFirstName($firstName)
            ->setLastName($lastName)
            ->setEmail($email)
            ->setComment('Test comment message')
            ->save()
            ->assertMessage('Contact request has been saved successfully')
            ->assertTitle($firstName . ' ' . $lastName . ' - Contact Requests - Activities')
            ->checkStep('Open');

        return $email;
    }

    /**
     * @depends testCreateContactRequest
     * @param $email
     * @return string
     */
    public function testUpdateContactRequest($email)
    {
        $newEmail = 'Update_' . $email;

        $login = $this->login();
        /** @var ContactRequests $login */
        $login->openContactRequests('OroCRM\Bundle\ContactUsBundle')
            ->filterBy('Email', $email)
            ->open([$email])
            ->edit()
            ->setEmail($newEmail)
            ->save()
            ->assertMessage('Contact request has been saved successfully');
        $login->openContactRequests('OroCRM\Bundle\ContactUsBundle')
            ->filterBy('Email', $email)
            ->assertNoDataMessage('No contact request was found to match your search.');

        return $newEmail;
    }

    /**
     * @depends testUpdateContactRequest
     * @param $email
     */
    public function testDeleteContactRequest($email)
    {
        $login = $this->login();
        /** @var ContactRequests $login */
        $login->openContactRequests('OroCRM\Bundle\ContactUsBundle')
            ->filterBy('Email', $email)
            ->open([$email])
            ->delete()
            ->assertMessage('Contact Request deleted')
            ->assertTitle('All - Contact Requests - Activities');
        /** @var ContactRequests $login */
        $login->openContactRequests('OroCRM\Bundle\ContactUsBundle');
        if ($login->getRowsCount() > 0) {
            $login->filterBy('Email', $email)
                ->assertNoDataMessage('No contact request was found to match your search.');
        }
    }
}
