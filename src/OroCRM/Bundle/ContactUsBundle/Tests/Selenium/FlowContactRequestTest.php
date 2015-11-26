<?php

namespace OroCRM\Bundle\ContactUsBundle\Tests\Selenium;

use Oro\Bundle\TestFrameworkBundle\Test\Selenium2TestCase;
use OroCRM\Bundle\ContactUsBundle\Tests\Selenium\Pages\ContactRequests;

/**
 * Class FlowContactRequestTest
 *
 * @package OroCRM\Bundle\ContactUsBundle\Tests\Selenium
 */
class FlowContactRequestTest extends Selenium2TestCase
{
    /**
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
            ->add()
            ->setFirstName($firstName)
            ->setLastName($lastName)
            ->setEmail($email)
            ->setPhone('123456789')
            ->setComment('Test comment message')
            ->save()
            ->assertMessage('Contact request has been saved successfully')
            ->assertTitle($firstName . ' ' . $lastName . ' - Contact Requests - Activities')
            ->checkStep('Open')
            ->close();

        return $email;
    }

    /**
     * @depends testCreateContactRequest
     * @param $email
     * @return string
     */
    public function testFlowContactRequest($email)
    {
        $feedback    = 'Test feedback_' . mt_rand(10, 99);

        $login = $this->login();
        /** @var ContactRequests $login */
        $login->openContactRequests('OroCRM\Bundle\ContactUsBundle')
            ->filterBy('Email', $email)
            ->open([$email])
            ->resolve()
            ->setFeedback($feedback)
            ->submit()
            ->checkStep('Resolved')
            ->checkFeedback($feedback);
    }
}
