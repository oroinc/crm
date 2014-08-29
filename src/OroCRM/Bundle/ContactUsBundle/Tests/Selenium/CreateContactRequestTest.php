<?php

namespace OroCRM\Bundle\ContactUsBundle\Tests\Selenium;

use Oro\Bundle\TestFrameworkBundle\Test\Selenium2TestCase;
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
    public function testCreateContactRequest()
    {
        $firstName = 'First name_' . mt_rand(10, 99);
        $lastName = 'Last name_' . mt_rand(10, 99);
        $email = 'Email_' . mt_rand(10, 99) . '@mail.com';

        $login = $this->login();
        /** @var ContactRequests $login */
        $login->openContactRequests('OroCRM\Bundle\ContactUsBundle')
            ->assertTitle('Contact Requests - Activities')
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
            ->open(array($email))
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
            ->open(array($email))
            ->delete()
            ->assertTitle('Contact Requests - Activities')
            ->assertMessage('Contact Request deleted');
        /** @var ContactRequests $login */
        $login->openContactRequests('OroCRM\Bundle\ContactUsBundle');
        if ($login->getRowsCount() > 0) {
            $login->filterBy('Email', $email)
                ->assertNoDataMessage('No contact request was found to match your search.');
        }
    }
}
